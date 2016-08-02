<tr>
	<td><?php echo $tpl_langs->trans('OTPLogin') ?></td>
	<td colspan="2">
		<?php if ($tpl_allowed): ?>
			<?php if ($tpl_qr): ?>
				<div style="text-align: center"><img height="138" width="138" src="<?php echo $tpl_qr ?>"></div>
				<br><?php echo $tpl_langs->trans('OTPTroubleHash') ?><br/>
				<span
					style="font-family:monospace;font-size:20px"><?php echo $tpl_seed ?></span>
				<br><?php echo $tpl_langs->trans('OTPKeyType') ?>
				<br><br>
			<?php endif; ?>
			<form method="post">
				<div style="text-align: center"><input type="submit" value="<?php echo $tpl_langs->trans('OTPRegenerate') ?>"
				                                       class="button"
				                                       name="regenerate_otp"></div>
			</form>
		<?php endif ?>
	</td>
</tr>