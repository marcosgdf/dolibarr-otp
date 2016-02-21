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
 * Copyright (C) 2001      Eric Seigne         <erics@rycks.com>
 * Copyright (C) 2004-2012 Destailleur Laurent <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin       <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class OtpTranslate extends Translate
{

	public function trans($key)
	{
		global $conf;

		if (! empty($this->tab_translate[$key]))	// Translation is available
		{
			$str=$this->tab_translate[$key];

			// Overwrite translation (TODO Move this at a higher level when we load tab_translate to avoid doing it for each trans call)
			$overwritekey='MAIN_OVERWRITE_TRANS_'.$this->defaultlang;
			if (! empty($conf->global->$overwritekey))    // Overwrite translation with key1:newstring1,key2:newstring2
			{
				$tmparray=explode(',', $conf->global->$overwritekey);
				foreach($tmparray as $tmp)
				{
					$tmparray2=explode(':',$tmp);
					if ($tmparray2[0]==$key) { $str=$tmparray2[1]; break; }
				}
			}

			// We replace some HTML tags by __xx__ to avoid having them encoded by htmlentities
			$str=str_replace(array('<','>','"',),array('__lt__','__gt__','__quot__'),$str);

			// Crypt string into HTML
			$str=htmlentities($str,ENT_QUOTES,$this->charset_output);

			// Restore HTML tags
			$str=str_replace(array('__lt__','__gt__','__quot__'),array('<','>','"',),$str);

			return $str;
		}
		else								// Translation is not available
		{
			if ($key[0] == '$') { return dol_eval($key,1); }
			return $this->getTradFromKey($key);
		}
	}

	/**
	 * Return translated value of key. Search in lang file, then into database.
	 * Key must be any complete entry into lang file: CurrencyEUR, ...
	 * If not found, return key.
	 * WARNING: To avoid infinite loop (getLabelFromKey->transnoentities->getTradFromKey), getLabelFromKey must
	 * not be called with same value than input.
	 *
	 * @param	string		$key		Key to translate
	 * @return 	string					Translated string
	 * @version 3.4.5
	 */
	protected function getTradFromKey($key)
	{
		global $db;

		//print 'xx'.$key;
		$newstr=$key;
		if (preg_match('/^Currency([A-Z][A-Z][A-Z])$/i',$key,$reg))
		{
			$newstr=$this->getLabelFromKey($db,$reg[1],'c_currencies','code_iso','label');
		}
		else if (preg_match('/^SendingMethod([0-9A-Z]+)$/i',$key,$reg))
		{
			$newstr=$this->getLabelFromKey($db,$reg[1],'c_shipment_mode','code','libelle');
		}
		else if (preg_match('/^PaymentTypeShort([0-9A-Z]+)$/i',$key,$reg))
		{
			$newstr=$this->getLabelFromKey($db,$reg[1],'c_paiement','code','libelle');
		}
		else if (preg_match('/^Civility([0-9A-Z]+)$/i',$key,$reg))
		{
			$newstr=$this->getLabelFromKey($db,$reg[1],'c_civilite','code','civilite');
		}
		else if (preg_match('/^OrderSource([0-9A-Z]+)$/i',$key,$reg))
		{
			// TODO Add a table for OrderSourceX
			//$newstr=$this->getLabelFromKey($db,$reg[1],'c_ordersource','code','label');
		}
		return $newstr;
	}

}