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

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';


/**
 * Module OTP class descriptor
 */
class modOtp extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param      DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 402000;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'otp';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "technic";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = 'OTP login';
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "HOTP login for Dolibarr";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0.7';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_OTP';
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 2;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'generic';

		$this->module_parts = array(
			'login' => 1,
			'hooks' => array(
				'mainloginpage',
				'usercard'
			)
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		$this->config_page_url = array("admin.php@otp");

		// Dependencies
		$this->hidden = false;            // A condition to hide module
		$this->depends = array();        // List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();    // List of modules id to disable if this one is disabled
		$this->conflictwith = array();    // List of modules id this module is in conflict with
		$this->phpmin = array(5, 3);                    // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3, 3);    // Minimum version of Dolibarr required by module
		$this->langfiles = array(
			"otp@otp"
		);

		//Parent __construct function has been introduced in 3.8
		if (is_callable('parent::__construct')) {
			parent::__construct($db);
		}
	}

	/**
	 *        Function called when module is enabled.
	 *        The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *        It also creates data directories
	 *
	 * @param      string $options Options when enabling module ('', 'noboxes')
	 * @return     int                1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		if (!function_exists('mcrypt_encrypt')) {
			global $langs;

			$langs->load('otp@otp');

			$this->error = $langs->trans('McryptNotActive');

			return 0;
		}

		$this->_load_tables('/otp/sql/');

		return $this->_init(array(), $options);
	}

	/**
	 *        Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *        Data directories are not deleted
	 *
	 * @param      string $options Options when enabling module ('', 'noboxes')
	 * @return     int                1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

}
