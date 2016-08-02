<?php
llxHeader('', $tpl_langs->trans('OTPAdminTitle'));
print_fiche_titre($tpl_langs->trans('OTPAdminTitle'),
	'<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$tpl_langs->trans("BackToModuleList").'</a>');
?>

	<br>

<?php if ($tpl_notcompatible): info_admin($tpl_langs->trans('OTPAdminNotCompatible')) ?>
<?php else: ?>

	<div class="titre"><?php echo $tpl_langs->trans('OTPAdminUsage') ?></div>

	<p><?php echo sprintf(
			$tpl_langs->trans('OTPAdminInfoDevices'),
			'<a href="http://en.wikipedia.org/wiki/HMAC-based_One-time_Password_Algorithm">', '</a>',
			'<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">', '</a>',
			'<a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=es">',
			'</a>',
			'<a href="https://support.google.com/accounts/answer/1066447?hl=es">', '</a>'
		) ?></p>

	<p><?php echo $tpl_langs->trans('OTPAdminInfoRegenerate') ?></p>

	<p><?php echo $tpl_langs->trans('OTPAdminInfoConfig') ?></p>

	<p style="text-align:center"><?php echo sprintf(
			$tpl_langs->trans('OTPAdminInfoLine'),
			'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i>',
			'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i>'
		) ?></p>

	<p><?php echo $tpl_langs->trans('OTPAdminInfoLogin') ?></p>

	<p style="text-align: center;border: 1px solid red;line-height:20px"><?php echo sprintf(
			$tpl_langs->trans('OTPAdminInfoImportant'),
			'<br>'.sprintf(
				$tpl_langs->trans('OTPAdminInfoLine'),
				'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i>',
				'<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i>'
			).'<br />') ?><br>

		<strong><?php echo $tpl_langs->trans('OTPAdminInfoOverride') ?></strong></p>

	<div class="titre"><?php echo $tpl_langs->trans('OTPAdminAuthor') ?></div>

	<p><?php echo sprintf($tpl_langs->trans('OTPAdminAuthorBody'),
			'<a href="http://www.marcosgdf.com">www.marcosgdf.com</a>') ?></p>
	<ul>
		<li>Endroid QR Code: <a href="https://github.com/endroid/QrCode">https://github.com/endroid/QrCode</a></li>
		<li>Rych-OTP: <a href="https://github.com/rchouinard/rych-otp">https://github.com/rchouinard/rych-otp</a></li>
		<li>Rych Random Data Library: <a href="https://github.com/rchouinard/rych-random">https://github.com/rchouinard/rych-random</a>
		</li>
	</ul>
	<p><?php echo sprintf($tpl_langs->trans('OTPAdminSupport'),
			'<a href="mailto:hola@marcosgdf.com">hola@marcosgdf.com</a>') ?></p>

	<p><?php echo sprintf($tpl_langs->trans('OTPGift'),
			'<a href="mailto:hola@marcosgdf.com">hola@marcosgdf.com</a>') ?></p>
	<p style="text-align:center"><a href="https://www.amazon.es/gp/product/B005Z3AHTQ/gcrnsts"><img
				src="../img/buy.gif"></a></p>
<?php endif;
llxFooter();