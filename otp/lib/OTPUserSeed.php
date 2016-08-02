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

class OTPUserSeed
{

	const METHOD_OLD = 'old';
	const METHOD_NEW = 'new';

	/**
	 * Returns the full path to the file that contains the crypto key
	 * @return string
	 */
	protected function getKeyFile()
	{
		return __DIR__.'/../otpkey.txt';
	}

	/**
	 * Generates a new crypto key and returns it in binary format
	 *
	 * @return string Key in binary format
	 * @throws ErrorException If there was an error saving the key to the file that stores the key
	 */
	private function generateCryptKey()
	{
		$cd = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');

		$seed = Seed::generate(mcrypt_enc_get_key_size($cd));
		$cryptokey = $seed->getValue(Seed::FORMAT_RAW);

		if (!file_put_contents($this->getKeyFile(), base64_encode($cryptokey))) {
			throw new ErrorException();
		}

		return $cryptokey;
	}

	/**
	 * Retrieves the crypto key. In case of not being created it generates a new one.
	 *
	 * @param string $method New or Old.
	 * @return string Key in binary format
	 * @throws ErrorException If there was an error reading the file that stores the key
	 */
	private function getCryptKey($method = self::METHOD_NEW)
	{
		if ($method == self::METHOD_OLD) {
			global $dolibarr_main_cookie_cryptkey;
			return @pack('H*', $dolibarr_main_cookie_cryptkey);
		}

		if (!file_exists($this->getKeyFile())) {
			return $this->generateCryptKey();
		}

		if ($content = file_get_contents($this->getKeyFile())) {
			return base64_decode($content);
		}

		throw new ErrorException();
	}

	/**
	 * Regenerates an user seed
	 *
	 * @param DoliDB $db Database handler
	 * @param User $user User holding the seed
	 * @return bool|string
	 */
	public function generate(DoliDB $db, User $user)
	{
		// Generates a 20-byte (160-bit) secret key
		$otpSeed = Seed::generate();
		$base32Seed = $otpSeed->getValue(Seed::FORMAT_BASE32);

		if (!$this->store($db, $user->id, $base32Seed)) {
			return false;
		}

		return $base32Seed;
	}

	/**
	 * Encrypts a given seed
	 * Examples from http://es.php.net/mcrypt_encrypt
	 *
	 * @param string $seed Raw seed
	 * @return string Ciphered seed
	 * @throws \ErrorException If there was an error retrieving the crypt key
	 */
	private function encrypt($seed)
	{
		//Crear una aleatoria IV para utilizarla con codificación CBC
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		$ciphertext = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256,
			$this->getCryptKey(),
			$seed,
			MCRYPT_MODE_CBC,
			$iv
		);

		//Anteponer la IV para que esté disponible para el descifrado
		return $iv.$ciphertext;
	}

	/**
	 * Decrypts seed in the database
	 * Examples from http://es.php.net/mcrypt_encrypt
	 *
	 * @param string $seed Given seed
	 * @param string $method Cryptkey
	 * @return string
	 * @throws \ErrorException If there was an error retrieving the crypt key
	 */
	public function decrypt($seed, $method = self::METHOD_NEW)
	{
		$ciphertext_dec = base64_decode($seed);

		//Recupera la IV, iv_size debería crearse usando mcrypt_get_iv_size()
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv_dec = substr($ciphertext_dec, 0, $iv_size);

		//Recupera el texto cifrado (todos excepto el $iv_size en el frente)
		$ciphertext_dec = substr($ciphertext_dec, $iv_size);

		//Podrían eliminarse los caracteres con valor 00h del final del texto puro
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->getCryptKey($method),
			$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
	}

	/**
	 * Stores a user seed
	 *
	 * @param DoliDB $db Database handler
	 * @param int $rowid User id
	 * @param string $seed Seed to store
	 * @return bool
	 */
	public function store(DoliDB $db, $rowid, $seed)
	{
		//Encrypt and encode the text
		$ciphertext_base64 = base64_encode($this->encrypt($seed));

		$sql = "UPDATE ".MAIN_DB_PREFIX."user SET otp_seed = '".$db->escape(
				$ciphertext_base64
			)."', otp_counter = 0 WHERE rowid = ".$rowid;

		if (!$db->query($sql)) {
			return false;
		}

		return true;
	}

}