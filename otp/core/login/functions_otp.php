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
function check_user_password_otp($usertotest, $passwordtotest, $entitytotest)
{
	global $db, $conf, $user;

	dol_include_once('/core/login/functions_dolibarr.php');

	//We first check if user & password are OK
	if (check_user_password_dolibarr($usertotest, $passwordtotest, $entitytotest) == '') {
		return '';
	}

	// Force master entity in transversal mode
	$entity = $entitytotest;
	if (!empty($conf->multicompany->enabled) && !empty($conf->multicompany->transverse_mode)) {
		$entity = 1;
	}

	$sql = 'SELECT rowid, otp_seed, otp_counter';
	$sql .= ' FROM '.MAIN_DB_PREFIX."user";
	$sql .= " WHERE login = '".$db->escape($usertotest)."'";
	$sql .= ' AND entity IN (0,'.($entity ?: 1).")";

	dol_syslog("functions_dolibarr::check_user_password_otp", LOG_DEBUG);
	$resql = $db->query($sql);

	if (!$resql) {
		return '';
	}

	$obj = $db->fetch_object($resql);

	if (!$obj) {
		return '';
	}

	//The user has not configured an OTP key
	if (!$obj->otp_seed) {
		return $usertotest;
	}

	//Now we validate OTP
	$providedOTP = GETPOST('otp');

	if (empty($providedOTP)) {
		return '';
	}

	require_once __DIR__.'/../../lib/OTPUserSeed.php';

	$otpuserseed = new OTPUserSeed();

	$methods = array(
		OTPUserSeed::METHOD_OLD,
		OTPUserSeed::METHOD_NEW
	);

	foreach ($methods as $cryptmethod) {

		$decryptedSeed = $otpuserseed->decrypt($obj->otp_seed, $cryptmethod);

		$otplib = new \Rych\OTP\HOTP($decryptedSeed);

		if ($otplib->validate($providedOTP, $obj->otp_counter)) {

			if ($cryptmethod == OTPUserSeed::METHOD_OLD) {
				$otpuserseed->store($db, $obj->rowid, $decryptedSeed);
			}

			$obj->otp_counter++;

			$sql = "UPDATE ".MAIN_DB_PREFIX."user SET otp_counter = '".$obj->otp_counter."' WHERE rowid = ".$obj->rowid;
			$db->query($sql);

			// Now the user is authenticated
			return $usertotest;
		}
	}

	return '';
}