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

if (!file_exists('../../main.inc.php')) {
	require_once '../../../main.inc.php';
} else {
	require_once '../../main.inc.php';
}

if (!file_exists('../../core/lib/admin.lib.php')) {
	require_once '../../../core/lib/admin.lib.php';
} else {
	require_once '../../core/lib/admin.lib.php';
}

require_once __DIR__.'/../class/OtpTranslate.class.php';

$otplangs = new OtpTranslate('', $conf);
$otplangs->setDefaultLang($langs->getDefaultLang());

$otplangs->load('otp@otp');

$title = $otplangs->trans('OTPAdminTitle');

llxHeader('', $title);

$otplangs->load('admin');

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$otplangs->trans("BackToModuleList").'</a>';
print_fiche_titre($title,$linkback,'setup');

print '<br />';

if (!function_exists('mcrypt_encrypt')) {
	echo info_admin($otplangs->trans('OTPAdminNotCompatible'));
	llxFooter();
	die;
}

print '<div class="titre">'.$otplangs->trans('OTPAdminUsage').'</div>';

print '

<p>'.sprintf(
		$otplangs->trans('OTPAdminInfoDevices'),
		'<a href="http://en.wikipedia.org/wiki/HMAC-based_One-time_Password_Algorithm">', '</a>',
		'<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">', '</a>',
		'<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=es">', '</a>',
		'<a href="https://support.google.com/accounts/answer/1066447?hl=es">', '</a>'
	).'</p>

<p>'.$otplangs->trans('OTPAdminInfoRegenerate').'</p>

<p>'.$otplangs->trans('OTPAdminInfoConfig').'</p>

<p style="text-align:center">'.sprintf(
		$otplangs->trans('OTPAdminInfoLine'),
		'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i>',
		'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i>'
	).'</p>

<p>'.$otplangs->trans('OTPAdminInfoLogin').'</p>

<p style="text-align: center;border: 1px solid red;line-height:20px">'.sprintf(
		$otplangs->trans('OTPAdminInfoImportant'),
		'<br>'.sprintf(
			$otplangs->trans('OTPAdminInfoLine'),
			'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i>',
			'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i>'
		).'<br />').'<br>

<strong>'.$otplangs->trans('OTPAdminInfoOverride').'</strong></p>';

print '<div class="titre">'.$otplangs->trans('OTPAdminAuthor').'</div>';

print '
<p>'.sprintf($otplangs->trans('OTPAdminAuthorBody'), '<a href="http://www.marcosgdf.com">www.marcosgdf.com</a>').'</p><ul>
<li>Endroid QR Code: <a href="https://github.com/endroid/QrCode">https://github.com/endroid/QrCode</a></li>
<li>Rych-OTP: <a href="https://github.com/rchouinard/rych-otp">https://github.com/rchouinard/rych-otp</a></li>
<li>Rych Random Data Library: <a href="https://github.com/rchouinard/rych-random">https://github.com/rchouinard/rych-random</a></li>
</ul>
<p>'.sprintf($otplangs->trans('OTPAdminSupport'), '<a href="mailto:hola@marcosgdf.com">hola@marcosgdf.com</a>').'.</p>

<p>'.sprintf($otplangs->trans('OTPGift'), 'hola@marcosgdf.com').'</p>
<p style="text-align:center"><a href="https://www.amazon.es/gp/product/B005Z3AHTQ/gcrnsts"><img src="../img/buy.gif"></a></p>
';

