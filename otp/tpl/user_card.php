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

?>
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