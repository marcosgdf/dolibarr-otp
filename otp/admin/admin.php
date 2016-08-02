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

if (!file_exists('../../main.inc.php')) {
	require_once __DIR__.'/../../../main.inc.php';
} else {
	require_once __DIR__.'/../../main.inc.php';
}

if (!file_exists('../../core/lib/admin.lib.php')) {
	require_once __DIR__.'/../../../core/lib/admin.lib.php';
} else {
	require_once __DIR__.'/../../core/lib/admin.lib.php';
}

require_once __DIR__.'/../class/OtpTranslate.class.php';
require_once __DIR__.'/../lib/otp.lib.php';

$otplangs = new OtpTranslate('', $conf);
$otplangs->setDefaultLang($langs->getDefaultLang());

$otplangs->load('otp@otp');
$otplangs->load('admin');

print OTPrenderTemplate('admin', array(
	'compatible' => function_exists('mcrypt_encrypt'),
	'langs' => $otplangs
));
