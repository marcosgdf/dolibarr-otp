<?php

/*
 * Copyright (C) 2014 Marcos García de La Fuente <hola@marcosgdf.com>
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

require __DIR__.'/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Rych\OTP\Seed;

class ActionsOtp
{

	public function getLoginPageOptions($parameters, &$object, &$action, HookManager $hookManager)
	{
		global $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		$langs->load('otp@otp');

		if (versioncompare(versiondolibarrarray(), array('3','8','0')) >= 0) {
			$this->results = array(
				'options' => array(
					'table' => '<tr>
<td class="nowrap center valignmiddle"><input type="text" name="otp" class="flat" size="20" id="otp" tabindex="3" placeholder="'.$langs->trans('OTPCode').'"></td></tr>'
				)
			);
		} else {
			$this->results = array(
				'options' => array(
					'table' => '<tr>
<td>
<label for="otp"><strong>'.$langs->trans('OTPCode').'</strong></label></td><td><input type="text" name="otp" class="flat" size="15" id="otp" tabindex="3"></td></tr>'
				)
			);
		}
	}

	public function formObjectOptions($parameters, &$object, &$action, HookManager $hookManager)
	{
		global $db, $user, $langs, $mysoc, $dolibarr_main_cookie_cryptkey;

		$langs->load('otp@otp');

		$regenerate_button = '<form method="post">
			<input type="submit" value="'.$langs->trans('OTPRegenerate').'" class="button" name="regenerate_otp">
		</form>';

		if ($action == '') {

			print '<tr><td>'.$langs->trans('OTPLogin').'</td><td colspan="2">';

			if (GETPOST('regenerate_otp')) {

				if ($user->admin || ($user->id == GETPOST('id', 'int'))) {

					/**
					 * Examples from http://es.php.net/mcrypt_encrypt
					 */

					// Generates a 20-byte (160-bit) secret key
					$otpSeed = Seed::generate();
					$base32Seed = $otpSeed->getValue(Seed::FORMAT_BASE32);

					$key = pack('H*', $dolibarr_main_cookie_cryptkey);

					# crear una aleatoria IV para utilizarla co condificación CBC
					$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
					$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

					$ciphertext = mcrypt_encrypt(
						MCRYPT_RIJNDAEL_256,
						$key,
						$base32Seed,
						MCRYPT_MODE_CBC,
						$iv
					);

					# anteponer la IV para que esté disponible para el descifrado
					$ciphertext = $iv.$ciphertext;

					# codificar el texto cifrado resultante para que pueda ser representado por un string
					$ciphertext_base64 = base64_encode($ciphertext);

					$sql = "UPDATE ".MAIN_DB_PREFIX."user SET otp_seed = '".$db->escape(
							$ciphertext_base64
						)."', otp_counter = 0 WHERE rowid = ".$user->id;
					$db->query($sql);

					$qrCode = new QrCode();
					$qrCode->setText(
						"otpauth://hotp/".$mysoc->name.":".$user->login."?secret=".$base32Seed."&issuer=".$mysoc->name
					);
					$qrCode->setSize(96);
					$qrCode->setPadding(5);

					$img_path = __DIR__.'/../tmp/'.$user->id.'.png';

					$qrCode->save($img_path);

					//Qrcode library doesn't warn on image creation error
					if (file_exists($img_path)) {
						print '<img src="'.dol_buildpath('/otp/showdoc.php', 1).'?img='.$user->id.'"><br>'.$langs->trans('OTPTroubleHash').'<br />
				<span style="font-family:monospace;font-size:20px">'.$base32Seed.'</span><br>'.$langs->trans('OTPKeyType');
					} else {
						print $regenerate_button;
						setEventMessage('ErrorCreatingImage', 'errors');
					}
				}

			} else {

				if ($user->admin || ($user->id == GETPOST('id', 'int'))) {
					print $regenerate_button;
				}
			}

			print '</td></tr>';
		}

	}

}