<?php

/*
 * Copyright (C) 2014-2016 Marcos García de La Fuente <hola@marcosgdf.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require __DIR__.'/../../vendor/autoload.php';

/**
 * Check validity of user/password/entity
 * If test is ko, reason must be filled into $_SESSION["dol_loginmesg"]
 *
 * @param	string	$usertotest		Login
 * @param	string	$passwordtotest	Password
 * @param   int		$entitytotest   Number of instance (always 1 if module multicompany not enabled)
 * @return	string					Login if OK, '' if KO
 */
function check_user_password_otp($usertotest,$passwordtotest,$entitytotest)
{
	global $db, $conf, $dolibarr_main_cookie_cryptkey;

	dol_include_once('/core/login/functions_dolibarr.php');

	//We first check if user & password are OK
	if (check_user_password_dolibarr($usertotest,$passwordtotest,$entitytotest) == '') {
		return '';
	}

	// Force master entity in transversal mode
	$entity=$entitytotest;
	if (! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)) $entity=1;

	$sql ='SELECT rowid, otp_seed, otp_counter';
	$sql.=' FROM '.MAIN_DB_PREFIX."user";
	$sql.=' WHERE login = "'.$db->escape($usertotest).'"';
	$sql.=' AND entity IN (0,' . ($entity ? $entity : 1) . ")";

	dol_syslog("functions_dolibarr::check_user_password_dolibarr sql=".$sql);
	$resql=$db->query($sql);

	if ($resql) {

		$obj = $db->fetch_object($resql);
		if ($obj) {

			//The user has not configured an OTP key
			if (!$obj->otp_seed) {
				return $usertotest;
			}

			//Now we validate OTP
			$providedOTP = GETPOST('otp');

			if (empty($providedOTP)) {
				return '';
			}

			/**
			 * Examples from http://es.php.net/mcrypt_encrypt
			 */

			$ciphertext_dec = base64_decode($obj->otp_seed);
			$key = pack('H*', $dolibarr_main_cookie_cryptkey);

			# recupera la IV, iv_size debería crearse usando mcrypt_get_iv_size()
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
			$iv_dec = substr($ciphertext_dec, 0, $iv_size);

			# recupera el texto cifrado (todo excepto el $iv_size en el frente)
			$ciphertext_dec = substr($ciphertext_dec, $iv_size);

			# podrían eliminarse los caracteres con valor 00h del final del texto puro
			$otpSeed = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,
				$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

			$otplib = new \Rych\OTP\HOTP($otpSeed);

			if ($otplib->validate($providedOTP, $obj->otp_counter)) {

				$obj->otp_counter++;

				$sql = "UPDATE ".MAIN_DB_PREFIX."user SET otp_counter = '".$obj->otp_counter."' WHERE rowid = ".$obj->rowid;
				$db->query($sql);

				// Now the user is authenticated
				return $usertotest;
			}
		}
	}

	return '';
}