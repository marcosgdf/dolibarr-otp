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
 *
 * With derived work of Dolibarr project (www.dolibarr.org) under the following license:
 *
 * Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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

use Rych\OTP\Seed;

/**
 * Regenerates an user seed
 * Examples from http://es.php.net/mcrypt_encrypt
 *
 * @param DoliDB $db Database handler
 * @param User $user User holding the seed
 * @return bool|string
 */
function OTPregenerateSeed(DoliDB $db, User $user)
{
	global $dolibarr_main_cookie_cryptkey;

	// Generates a 20-byte (160-bit) secret key
	$otpSeed = Seed::generate();
	$base32Seed = $otpSeed->getValue(Seed::FORMAT_BASE32);

	$key = pack('H*', $dolibarr_main_cookie_cryptkey);

	//Crear una aleatoria IV para utilizarla con codificación CBC
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	$ciphertext = mcrypt_encrypt(
		MCRYPT_RIJNDAEL_256,
		$key,
		$base32Seed,
		MCRYPT_MODE_CBC,
		$iv
	);

	//Anteponer la IV para que esté disponible para el descifrado
	$ciphertext = $iv.$ciphertext;

	//Codificar el texto cifrado resultante para que pueda ser representado por un string
	$ciphertext_base64 = base64_encode($ciphertext);

	$sql = "UPDATE ".MAIN_DB_PREFIX."user SET otp_seed = '".$db->escape(
			$ciphertext_base64
		)."', otp_counter = 0 WHERE rowid = ".$user->id;

	if (!$db->query($sql)) {
		return false;
	}

	return $base32Seed;
}

/**
 * Decrypts seed in the database
 * Examples from http://es.php.net/mcrypt_encrypt
 * 
 * @param string $seed Given seed
 * @param string $cryptkey Cryptkey
 * @return string
 */
function OTPDecryptSeed($seed, $cryptkey)
{
	$ciphertext_dec = base64_decode($seed);
	$key = pack('H*', $cryptkey);

	//Recupera la IV, iv_size debería crearse usando mcrypt_get_iv_size()
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
	$iv_dec = substr($ciphertext_dec, 0, $iv_size);

	//Recupera el texto cifrado (todo excepto el $iv_size en el frente)
	$ciphertext_dec = substr($ciphertext_dec, $iv_size);

	//Podrían eliminarse los caracteres con valor 00h del final del texto puro
	return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,
		$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
}