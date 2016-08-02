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

require __DIR__.'/../vendor/autoload.php';

use Endroid\QrCode\QrCode;

class ActionsOtp
{
	/**
	 * Data returned by the hook
	 * @var string
	 */
	public $results;

	/**
	 * Edits the login form to allow entering OTP
	 */
	public function getLoginPageOptions()
	{
		global $langs;

		require_once __DIR__.'/../lib/otp.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		$langs->load('otp@otp');

		$vars = array(
			'label' => $langs->trans('OTPCode')
		);

		if (versioncompare(versiondolibarrarray(), array('3','8','0')) >= 0) {
			$tpl = OTPrenderTemplate('login_3.8.0', $vars);
		} else {
			$tpl = OTPrenderTemplate('login_older', $vars);
		}

		$this->results = array(
			'options' => array(
				'table' => $tpl
			)
		);
	}

	/**
	 * Edits the user page to allow regenerating an OTP
	 *
	 * @param array $parameters Parameters given to the hook
	 * @param User $object User of the page
	 * @param string $action Action of the page
	 */
	public function formObjectOptions($parameters, $object, &$action)
	{
		global $db, $user, $langs, $mysoc;

		require __DIR__.'/../lib/otp.lib.php';

		$langs->load('otp@otp');

		$allow_regenerate = $user->admin || ($user->id == $object->id);

		$vars = array(
			'allowed' => $allow_regenerate,
			'langs' => $langs
		);

		if ($action == '' && $allow_regenerate && GETPOST('regenerate_otp')) {

			require_once __DIR__.'/../lib/OTPUserSeed.php';

			$otpuserseed = new OTPUserSeed();

			$otp_seed = $otpuserseed->generate($db, $object);

			//iPhone's Google Authenticator app has problems with spaces
			$strip_company_name = str_replace(' ', '', $mysoc->name);

			$qrCode = new QrCode();
			$qrCode->setText(
				"otpauth://hotp/".$strip_company_name.":".$object->login."?secret=".$otp_seed."&issuer=".$strip_company_name
			);
			$qrCode->setSize(128);
			$qrCode->setPadding(5);

			$img_path = __DIR__.'/../tmp/'.$object->id.'.png';

			try {
				$qrCode->save($img_path);
				$vars['qr'] = dol_buildpath('/otp/showdoc.php', 1).'?img='.$object->id;
				$vars['seed'] = $otp_seed;
			} catch (\Endroid\QrCode\Exceptions\ImageFunctionUnknownException $e) {
				setEventMessage('ErrorCreatingImage', 'errors');
			} catch (\Endroid\QrCode\Exceptions\ImageFunctionFailedException $e) {
				setEventMessage('ErrorCreatingImage', 'errors');
			}
		}

		print OTPrenderTemplate('user_card', $vars);
	}

}