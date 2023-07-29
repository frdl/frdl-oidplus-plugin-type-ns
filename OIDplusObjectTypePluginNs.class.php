<?php

/*
 * OIDplus 2.0
 * Copyright 2019 - 2023 Daniel Marschall, ViaThinkSoft
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Frdlweb\OIDplus;
use ViaThinkSoft\OIDPlus\OIDplus;
use ViaThinkSoft\OIDPlus\OIDplusException;

use Webfan\Autoload\LocalPsr4Autoloader;

// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class OIDplusObjectTypePluginNs extends \ViaThinkSoft\OIDPlus\OIDplusObjectTypePlugin {

	protected static $instance = null;
	protected static $StubRunner = null;
	protected static $autoloaderRegistered=false;
	
	
	public static function webfatInit( ) {	
		
		
		if(null === self::$instance){
		     $io4plugin = OIDplus::getPluginByOid('1.3.6.1.4.1.37476.9000.108.19361.24196');			
         	     if (!$io4plugin) throw new OIDplusException(_L("Plugin 1.3.6.1.4.1.37476.9000.108.19361.24196 missing in ".__CLASS__), null, 404);               $io4plugin->getWebfat(true, false);
	             $io4plugin->getWebfat(true, false);
			
		  if(!self::$autoloaderRegistered){
		      self::$autoloaderRegistered=true;	
			  $loader = new \Webfan\Autoload\LocalPsr4Autoloader; 
			  $loader->addNamespace(OIDplusNs::CLASS_TYPES_BASE_NS_GENERATED,
						   __DIR__.\DIRECTORY_SEPARATOR.'.generated'.\DIRECTORY_SEPARATOR.'object-type-classes',
						   false);
		     $loader->register(true) ;		
		 }
				
			
		   self::$instance = new OIDplusNs(OIDplusNs::root(), OIDplusNs::ns(), ['ns'=>OIDplusNs::ns(),]);	
		}
		return self::$instance;
	}

	public function init(bool $html=true) {
    //  self::webfatInit( );
	}	
	
	
	/**
	 * @return string
	 */
	public static function getObjectTypeClassName(): string { 
		self::webfatInit( );
		return OIDplusNs::class;
	}
	/**
	public function registerAutoloading(){
	    self::webfatInit( );
	}

	 * @param string $static_node_id
	 * @param bool $throw_exception
	 * @return string

	public static function prefilterQuery(string $static_node_id, bool $throw_exception): string {
		if (str_starts_with($static_node_id,'ns:')) {
			$static_node_id = str_replace(' ', '', $static_node_id);
		}
		return $static_node_id;
	}
	 */
}
