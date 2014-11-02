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

$title = 'Configuración del módulo OTP';

llxHeader('', $title);

$langs->load('admin');

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($title,$linkback,'setup');

print '<br />';

if (!function_exists('mcrypt_encrypt')) {
	echo info_admin('Su sistema no es compatible con este módulo. Debe instalar la extensión Mcrypt para poder proceder.');
	llxFooter();
	die;
}

print '<div class="titre">Utilización y configuración del módulo</div>';

print '

<p>Todos los usuarios de Dolibarr deberán disponer de un dispositivo Android, iPhone o Blackberry capaz de generar
códigos HOTP a partir de una clave proporcionada por Dolibarr. Puede descargar la aplicación Google Authenticator para <a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">iPhone</a>®, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=es">Android™</a> o <a href="https://support.google.com/accounts/answer/1066447?hl=es">Blackberry®</a>.</p>

<p>Una vez preparado para configurar su clave, todos los usuarios de Dolibarr deberán acceder a su ficha de usuario y hacer click sobre el botón "Regenerar clave OTP". En ese momento se mostrará un código QR de un solo uso para configurar en su dispositivo móvil.</p>

<p>Cuando todos los usuarios hayan configurado su clave OTP, deberemos acceder al archivo htdocs/conf/conf.php y cambiar la línea:</p>

<p><i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i> por <i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i></p>

<p>A partir de ese momento deberá introducir la clave OTP generada por su dispositivo móvil junto con su usuario y contraseña.<br>Si algún usuario no hubiera configurado su clave OTP todavía, podrá iniciar sesión sin tener que rellenarla.</p>

<p style="text-align: center;border: 1px solid red;line-height:20px">IMPORTANTE: En caso de que el dispositivo se desincronizara con Dolibarr (es decir, que se hayan solicitado más claves de las que se han usado y Dolibarr ya no le autoriza la entrada), deberá realizar el proceso inverso: acceder al archivo htdocs/conf/conf.php y cambiar la línea:<br>

<i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'otp\';</i> por <i style="font-family:monospace;font-weight:bold">$dolibarr_main_authentication=\'dolibarr\';</i>.<br>
Y deje vacío el campo Clave OTP para iniciar sesión. Posteriormente deberá acceder a su ficha de usuario y regenerar la clave OTP para volver a sincronizar su dispositivo móvil.<br>

<strong>Los administradores tienen autorización para regenerar la clave OTP de todos los usuarios.</strong></p>';

print '<div class="titre">Autor</div>';

print '
<p>Módulo desarrollado por Marcos García de La Fuente (<a href="http://www.marcosgdf.com">www.marcosgdf.com</a>) utilizando las siguientes librerías:</p><ul>
<li>Endroid QR Code: <a href="https://github.com/endroid/QrCode">https://github.com/endroid/QrCode</a></li>
<li>Rych-OTP: <a href="https://github.com/rchouinard/rych-otp">https://github.com/rchouinard/rych-otp</a></li>
<li>Rych Random Data Library: <a href="https://github.com/rchouinard/rych-random">https://github.com/rchouinard/rych-random</a></li>
</ul>
<p>En caso de encontrar algún problema o necesitar soporte, envíe un correo electrónico a <a href="mailto:hola@marcosgdf.com">hola@marcosgdf.com</a>.</p>
';

