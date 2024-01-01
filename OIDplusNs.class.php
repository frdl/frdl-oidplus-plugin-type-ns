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
use ViaThinkSoft\OIDPlus\OIDPlus;
use ViaThinkSoft\OIDPlus\OIDplusPluginManifest;
use ViaThinkSoft\OIDPlus\OIDplusObject;
use ViaThinkSoft\OIDPlus\OIDplusException;
use Frdlweb\OIDplus\withAttributesTrait;
use Frdlweb\OIDplus\OIDplusObjectTypePluginNs;
use Webfan\Autoload\LocalPsr4Autoloader;

use ViaThinkSoft\OIDplus\OIDplusAltId as OIDplusAltId;

// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class OIDplusNs extends OIDplusObject {
	use withAttributesTrait;
	/**
	 * @var string
	
	const DEFNS = '';
	const REGEX_DEFNS = '^$';
	
	const DEFNS = '~';
	const REGEX_DEFNS = '(\~)';	
	 */
	
	const DEFNS = 'alloc';
	const REGEX_DEFNS = '(\~|alloc)';	
	
	const REGEX_WEB_PROTOCOL_SCHEME = '(?<protocol>web\+(?<scheme>[\w\-]+))';
	const REGEX_DOMAIN = '(?<subdomain>[a-z0-9\-\_\*^\.]+)?(\.)(?<apex>[a-z0-9\-^\.]+)\.(?<tld>[a-z0-9\-^\.]+)';
	const REGEX_PACKAGE = '(?<vendor>[\w\-\_]+)\/(?<packagename>[\w\-\_]+)';
	const REGEX_OID = '(?<oid>[0-9\.]+)';
	const REGEX_FEDERATED_ENTITY = '\@(?<id>[^\@]+)\@(?<provider>[^\@]+)';
	const REGEX_IPV4 = '(?<ipv4>[\d]\.[\d]\.[\d]\.[\d])';
	const CLASS_TYPES_BASE_NS_GENERATED = 'Frdlweb\OIDplus\TypedNS';
	
	
	const REGEX_SERVICE_URL_DNS = self::REGEX_WEB_PROTOCOL_SCHEME.'\:\/\/'.'(?<host>'.self::REGEX_DOMAIN.')';
	const DELIMITER = '@';
	
	protected $oid;
	protected $domain;
	protected $attributes = [];
	protected static $namespaces = [
		self::DEFNS=>[
			
			],
		
	];
	
	
	protected static $_namespacesPatterns =null;
	
	public static $_ns= self::DEFNS;
 
	
		
			
	
	protected $viewObject = null;								
	protected $serviceObject = null;
	
	protected static $LocalPsr4Autoloader = null;

		
	public function init(bool $html=true) {
      // OIDplusObjectTypePluginNs::webfatInit( );
	}	
	
	public function __construct(string $domain, string $_ns= null, array $attributes = []) {
		$attrDomain = self::getValidClassConfig($domain);
		$_ns = $_ns ?: ($attrDomain['namespace'] ?: explode(':', self::$_ns.':'.$domain )[0]);
		$attrNS = self::getValidClassConfig($_ns);
		$attr = self::getValidClassConfig($_ns.':'.$domain);
		if (!is_array($attrDomain)
		//	|| !is_array($attr)  
		   ) {
		//	throw new OIDplusException(
		//	  _L(sprintf('Invalid Object Attributes for %s in %s', $domain.':'.$_ns, __METHOD__))
		 //  );
		}
		// TODO: syntax checks
		$this->domain = $this->oid = $domain;
	    self::$_ns = $_ns ?: self::$_ns;
		//foreach($attributes as $k=>$v){
		//	if(is_numeric($k))unset($attributes[$k]);
		//}
		//$this->attributes = $attributes;

		$this->setAttributes(array_merge($attrNS ?: [],$attrDomain ?: [],$attr?:  [], $attributes));
	}
	
	
	public static function getValidClassConfig($ns_or_id){
		foreach(self::getNamespacesPatterns() as $namespaceId => $dis){
			if(preg_match('/^(?<identifier>'.$dis['regex'].')$/', $ns_or_id, $match)){
				$match['ns'] = (isset($match['ns'])) ? $match['ns'] :explode(':', $ns_or_id, 2)[0];   
				\Frdlweb\OIDplus\OIDplusNs::$namespaces[$match['ns']]['definitions'] = $dis;
			    $match['ns'] = \call_user_func_array(isset($dis['getNamespace'])
												    ? $dis['getNamespace']
												    : [self::class, 'defaultGetNamespaceTypeHandler'], [$match, $ns_or_id]);
				$match['namespace']  =$match['ns'];  
				return $match;
				//break;
			}
		}		
		return false;
	}
		/**
	 * @param string $node_id
	 * @return OIDplusDomain|null
	 */
	public static function parse(string $node_id)/*: ?OIDplusDomain*/ {
		@list($namespace, $domain) = explode(':', $node_id, 2);		
		if(!isset($domain) || is_null($domain)){
		 $domain='';	
		}
		 
		$match = self::getValidClassConfig($namespace);
		
		if (!is_string($namespace)
			|| !is_array( $match )
			|| !isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace])){
			return null;
		}

			
		if(isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'])){
		  $class = \Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'];	 
		}else{
			$class = \get_called_class();
			//$class = self::class;
		} 
		
        $object =new $class($domain, $namespace, $match);  
		$object->setAttributes(array_merge([
			  'query' => $node_id,
			],$match));
		return $object;
		//return new self($domain, $namespace);
	}	
	
	
	
		/**
	 * @param bool $with_ns
	 * @return string
	 */
	public function nodeId(bool $with_ns=true): string {		 
		$class = \get_class($this);		
		return $with_ns ? trim($class::$_ns, ':@/\\.').':'.trim($this->domain, ':@/\\') : trim($this->domain, ':@/\\.');
	}

	/**
	 * @param string $str
	 * @return string
	 * @throws OIDplusException
	 */
	public function addString(string $str): string {
		$class = \get_class($this);
		if (strpos($str,$class::DELIMITER) !== false 
			  && !$this->isRoot()
		   ) throw new OIDplusException(_L('Please only submit one arc.'));
         return $this->appendArcs($str)->nodeId();
	}
	
	 
	
	
	public function appendArcs($arcs): OIDplusObject {
	    $class = \get_class($this);	
		$parentObject =// clone
			$this;
		$relativeObject = OIDplusObject::parse($arcs); 
	//	$newObject = new self($this->domain);  // self::DELIMITER
		 $newId = trim($parentObject->nodeId(false).$class::DELIMITER.$arcs, $class::DELIMITER); 
		 $match = $class::getValidClassConfig( $newId );
		 $matchLocalArcs = $class::getValidClassConfig( $arcs );
 
		
		  $ns = is_array($match) 
			          && $parentObject->isRoot() 		 
					  &&  OIDplusNs::DEFNS === $parentObject::ns() 
					  ? $match['ns'] : $parentObject::ns();
		
	
		
		if($parentObject::ns() === $ns && $newId === $ns.':'.$ns && is_array($matchLocalArcs) 
		   && @$matchLocalArcs['ns'] === @$matchLocalArcs['identifier']
		 //  && !empty($arcs) && $arcs === @$matchLocalArcs['ns'] && $newId === $ns.':'.$arcs
		   ){
			//$newObject = new \Frdlweb\OIDplus\OIDplusNs::$namespaces[$ns]['class']('', $ns, $match ?: ($matchLocalArcs ?: []) )  ;
			$newObject = new \Frdlweb\OIDplus\OIDplusNs::$namespaces[$ns]['class']($ns.':', $ns, $match ?: ($matchLocalArcs ?: []) )  ;
		}else{
			 $newObject = new \Frdlweb\OIDplus\OIDplusNs::$namespaces[$ns]['class']($newId, $ns, $match ?: ($matchLocalArcs ?: []) )  ;
		}
		 
		
		
		
		 //if ($relativeObject && $parentObject->isRoot() &&  OIDplusNs::DEFNS === $parentObject::ns() 
		 //	&& is_array($matchLocalArcs) 
		 //   ) {
		//	return $str.':'.$this->domain.self::DELIMITER.$this->domain.self::DELIMITER;
		   //   $newId = $arcs.':'; 	
			 //$newId = $arcs.':'$newId;
			 // $newObject = new self($newId, $matchLocalArcs['ns'], $match);   
		 //} 		
		
		$maxlen = OIDplus::baseConfig()->getValue('LIMITS_MAX_ID_LENGTH')-strlen($newObject::root());
		if (strlen($newId) > $maxlen) {
			 //	throw new OIDplusException(_L('The resulting OID "%1" is too long (max allowed length: %2).',$newId,$maxlen));
		}		
		
		return $newObject;
	}
	
	public function one_up()  {
		$oid = $this->domain;
	//	$class = \get_called_class();		
		$class = \get_class($this);
		
		$pathNodes = explode($class::DELIMITER, $oid); 
		
		$p = strpos($oid, $class::DELIMITER);		
		$p2 = strpos($oid, ':');		
		
		$last = array_pop($pathNodes);
		
		if (count($pathNodes)>1) {
			//$oid_up = substr($oid, $p+1);
			return OIDplusObject::parse( $this->getParent()->ns().':'.implode($class::DELIMITER,$pathNodes));
		}
		
	//	if ( $this->isRoot() ) return $class::parse($class::root());

		//$oid_up = substr($oid, $p+1);

		return OIDplusObject::parse( $this->getParent()->ns().':'.implode($class::DELIMITER,$pathNodes));
	} 
	
	public function getParent() {
			//	$class = \get_called_class();		
		$class = \get_class($this);
		
		if (!OIDplus::baseConfig()->getValue('OBJECT_CACHING', true)) {
			$res = OIDplus::db()->query("select parent from ###objects where id = ?", array($this->nodeId()));
			if ($res->any()) {
				$row = $res->fetch_array();
				$parent = $row['parent'];
				$obj = OIDplusObject::parse($parent);
				if ($obj) return $obj;
			}
		} else {
			self::buildObjectInformationCache();
			if (isset(OIDplusObject::$object_info_cache[$this->nodeId()])) {
				$parent = OIDplusObject::$object_info_cache[$this->nodeId()][OIDplusObject::CACHE_PARENT];
				$obj = OIDplusObject::parse($parent);
				//die($obj->nodeId(true));
				if ($obj) return $obj;
			}
		}

		 
		$cur = $this->one_up();
		if (!$cur) return null;
	//	if (!$cur || ($cur->isRoot() && empty($cur->nodeId()) )) return null;
		do {
			// findFitting() checks if that OID exists
			if ($fitting = OIDplusObject::findFitting($cur->nodeId(false)) 
			 	|| $fitting = OIDplusObject::findFitting($cur->nodeId(true)) 
			   ) return $fitting;

			$prev = $cur;
			$cur = $cur->one_up();
			if (!$cur)  return null;
		} while ($prev->nodeId() !== $cur->nodeId());

		 return null;
	}
	
		
	public static function buildObjectInformationCache(?string $id = null) {	
		//@ToDO cache plugin
	/*	return OIDplusObject::buildObjectInformationCache();*/
		
		$id = $id ?: self::root();
		
		if (is_null(OIDplusObject::$object_info_cache)) {
			OIDplusObject::$object_info_cache = array();
		//	$res = OIDplus::db()->query("select * from ###objects where `id` = ? or `id` LIKE ? or `parent` LIKE ?");
		//	$res = OIDplus::db()->query("select * from ###objects where `id` = '?' or `id` LIKE '?' or `parent` LIKE '?'");
			$res = OIDplus::db()->query("select * from ###objects");
			while ($row = $res->fetch_array([$id, '%'.$id.'%', $id.'%'])) {
				OIDplusObject::$object_info_cache[$row['id']] = $row;
			}
		}
		
	}	
	
	
	public static function exists(string $id): bool {
		if (!OIDplus::baseConfig()->getValue('OBJECT_CACHING', true)) {
			$res = OIDplus::db()->query("select id from ###objects where id = ?", array($id));
			return $res->any();
		} else {
			self::buildObjectInformationCache();
			return isset(OIDplusObject::$object_info_cache[$id]);
		}
	}
	
	
	/**
	 * @param string $id
	 * @return bool
	 * @throws OIDplusException
	
	public static function exists(string $id): bool {
		if (!OIDplus::baseConfig()->getValue('OBJECT_CACHING', true)) {
			$res = OIDplus::db()->query("select id from ###objects where id = ?", array($id));
			return $res->any();
		} else {
			self::buildObjectInformationCache();
			return isset(OIDplusObject::$object_info_cache[$id]);
		}
	}
 */
	/**
	 * Get parent gives the next possible parent which is EXISTING in OIDplus
	 * It does not give the immediate parent
	 * @return OIDplusObject|null
	 * @throws OIDplusException
	
	
	 */
		 
		 /**
	 * @return OIDplusDomain|null
	
*/
	/**
	 * @return string|null
	 * @throws OIDplusException
	
	public function getRaMail() {
		if (!OIDplus::baseConfig()->getValue('OBJECT_CACHING', true)) {
			$res = OIDplus::db()->query("select ra_email from ###objects where id = ?", array($this->nodeId()));
			if (!$res->any()) return null;
			$row = $res->fetch_array();
			return $row['ra_email'];
		} else {
			self::buildObjectInformationCache();
			if (isset(OIDplusObject::$object_info_cache[$this->nodeId()])) {
				return OIDplusObject::$object_info_cache[$this->nodeId()][OIDplusObject::CACHE_RA_EMAIL];
			}
			return null;
		}
	}
	 */
	
	
	


	/**
	 * @param OIDplusObject|string $to
	 * @return int|null
	
	public function distance($to) {
		
	//	$class = \get_called_class();		
		$class = \get_class($this);
		
		if (!is_object($to)) $to = OIDplusObject::parse($to);
		if (!$to) return null;
		if (!($to instanceof $this)) return null;

		$a = $to->domain;
		$b = $this->domain;

		if (substr($a,-1) == $class::DELIMITER) $a = substr($a,0,strlen($a)-1);
		if (substr($b,-1) == $class::DELIMITER) $b = substr($b,0,strlen($b)-1);

		$ary = explode($class::DELIMITER, $a);
		$bry = explode($class::DELIMITER, $b);

		$ary = array_reverse($ary);
		$bry = array_reverse($bry);

		$min_len = min(count($ary), count($bry));

		for ($i=0; $i<$min_len; $i++) {
			if ($ary[$i] != $bry[$i]) return null;
		}

		return count($ary) - count($bry);
	}

	  */
	public static function getNamespacesPatterns() : array {
		if(null === self::$_namespacesPatterns){
			$typesfile = __DIR__.\DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.'namespaces.php';
			if(!file_exists($typesfile)){
				$typesfile= __DIR__.\DIRECTORY_SEPARATOR.'config'.\DIRECTORY_SEPARATOR.'namespaces.dist.php';
			}
			self::$_namespacesPatterns = require $typesfile;
		}		 
		return self::$_namespacesPatterns;
	}
	
	
      public static function defaultGetNamespaceTypeHandler ($match, $domain)  {
		  $ttl = 60 * 60;
				//	print_r('<pre>');
				//	  print_r(	$match );
					$namespace=$match['ns'] ?: $match['identifier'];
			 
					if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace])){
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]=[];
					}	  
		     
	                    
		     
		            
		           if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'])){      
					       [$class, $base] =\Frdlweb\OIDplus\OIDplusNs::namespaceToClassname($namespace);
						   $classNameFQ = $base.'\\'.$class;		                
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class']=$classNameFQ;
					}else{
					   $classNameFQ = \Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'];
					   $parts = explode("\\", $classNameFQ);
					   $class = array_pop($parts);
					   $base = implode("\\", $parts); 
				   }
	
	 
		            $classFile = \Frdlweb\OIDplus\OIDplusNs::objectTypeClassFile($classNameFQ);
		  
					if(!file_exists($classFile) || filemtime($classFile) < time() - $ttl){
						// $classBuilder = \Nette\PhpGenerator\ClassType::from(\Frdlweb\OIDplus\OIDplusNs::class, true);
		                 $file = new \Nette\PhpGenerator\PhpFile;
		                 $file->addComment('This file is auto-generated. Do not edit it manually!');
						

						// $ns = new \Nette\PhpGenerator\PhpNamespace($ns);
						 $ns = $file->addNamespace($base);
						 $class = $ns->addClass($class); //new \Nette\PhpGenerator\ClassType($class);
					  	 //$class ->setAbstract(true)
	                      $class->setExtends(\Frdlweb\OIDplus\OIDplusNs::class);
						
						  $DEFNS = $class->addConstant('DEFNS', $namespace);
						
					  if(isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['definitions']['delimiter'])){
						  $class->addConstant('DELIMITER', \Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['definitions']['delimiter']);
					  }
						
						$m = $class->addMethod('__construct')	
						//	->setStatic()
							->setVisibility('public')	
						//	->setReturnReference()
							->addBody('parent::__construct($domain, $namespace, $attributes);')
							->addBody('$this->domain = $this->oid =$domain;')	
							//->addBody('$this->domain =$domain;')	
							->addBody('self::$_ns = ?;', [$namespace]);
						$p = $m->addParameter('domain');
						//$p->setType(Type::String);
						
						
						$p2 = $m->addParameter('namespace', $namespace);
						//$p2->setType(Type::String);
						
						$p3 = $m->addParameter('attributes');
					
						
						
						  $code = (new \Nette\PhpGenerator\PsrPrinter)->printFile($file);
						
						  					

						 @mkdir(dirname($classFile), 0755, true);
						 file_put_contents($classFile, $code);
						 
					}					
					
				//	if(!class_exists($classNameFQ, false)){
					// require_once $classFile;	
					//}
		  
						
		                if('1.3.6.1.4.1.37476.9000.108.19361.856'===$namespace && '1.3.6.1.4.1.37476.9000.108.19361.856' === $domain){
							$uploaddir = \ViaThinkSoft\OIDplus\OIDplusPagePublicAttachments::getUploadDir($namespace.':'.$domain);
							\Frdlweb\OIDplus\OIDplusNs::copyToDirWithPhpAsText( \Frdlweb\OIDplus\OIDplusNs::getPluginDirectory(),
																$uploaddir,
																 0644);
						}
						 	  
		  
		  return $namespace;				
	  }
	
	
	
	
	public static function namespaceToClassname(string $namespace): array {
		$hash_NS = sha1(\get_called_class());
		$hash = sha1($namespace);
		return [sprintf('T%s', $hash ),
				sprintf(self::CLASS_TYPES_BASE_NS_GENERATED.'\NamespaceType%s\T%s', $hash_NS, substr($hash,0,2) )];
	}
	public static function objectTypeClassFile(  $class) { 
		$dir = __DIR__.\DIRECTORY_SEPARATOR.'.generated'.\DIRECTORY_SEPARATOR.'object-type-classes'.\DIRECTORY_SEPARATOR;
		$c = explode('\\', $class);
		if(count($c))array_shift($c);
		if(count($c))array_shift($c);
		if(count($c))array_shift($c);
		$file = $dir.implode(\DIRECTORY_SEPARATOR, $c).'.php';
		return $file;
	  //return OIDplusObjectTypePluginNs::webfatInit( )->getLocalAutoloader()->file($class);
	}
	
	public function getPluginDirectory(): string {
		//$reflector = new \ReflectionClass(get_called_class());
	//	return dirname($reflector->getFilename());
		return __DIR__;
	}

	/**
	 * @return OIDplusPluginManifest|null
	 */
	public function getManifest()/*: ?OIDplusPluginManifest*/ {
		$dir = $this->getPluginDirectory();
		$ini = $dir.DIRECTORY_SEPARATOR.'manifest.xml';
		$manifest = new OIDplusPluginManifest();
		return $manifest->loadManifest($ini) ? $manifest : null;
	}
	
	public function getObjectTypeClassName(){
		//return OIDplusNs::class;
		return \get_class($this);
	}	
	

	
	
	
	public function gla( ){
		return $this->getLocalAutoloader();
	}	
	public function getLocalAutoloader( ){
		 if(null === self::$LocalPsr4Autoloader){
			self::$LocalPsr4Autoloader = new LocalPsr4Autoloader; 
		 }
		return self::$LocalPsr4Autoloader;
	}	
	
		
	
	

	
	public static function getRelativePathFrom($from, $to)
    {
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach($from as $depth => $dir) {
        // find first non-matching dir
        if($dir === $to[$depth]) {
            // ignore this directory
            array_shift($relPath);
        } else {
            // get number of remaining dirs to $from
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                // add traversals up to first matching dir
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
    }
	
	

	/**

	Allocation
	 * @return string
	 */
	public static function objectTypeTitle(): string {
	//	return _L('Apps, Names, Services, Projects, Deployments');
		return _L('Allocation');
	}

	/**
	 * @return string
	 */
	public static function objectTypeTitleShort(): string {
		return _L('Entry');
	}

	/**
	 * @return string
	 */
	public static function ns(): string {
		return self::$_ns;
		//$class = \get_called_class();
		//return $class::$_ns;
		//return '~';
	}

	/**
	 * @return string
	 */
	public static function root(): string {
		//return '~:';
		//$class = \get_called_class();
		return self::class === OIDplusNs::class
			 ? OIDplusNs::DEFNS.':'
			//   ? self::ns().':'
			//  : $class::$_ns.':';
				  : self::$_ns.':';
	}

	/**
	 * @return bool
	 */
	public function isRoot(): bool { 
		return $this->domain == '';// || $this->domain === self::DEFNS;
	}


	/**
	 * @param OIDplusObject $parent
	 * @return string
	 */
	public function crudShowId(OIDplusObject $parent): string {
		return $this->domain;
	}

	/**
	 * @return string
	 * @throws OIDplusException
	 */
	public function crudInsertSuffix(): string {
		return $this->isRoot() ? '' : substr($this->addString(''), strlen(self::ns())+1);
	}

	/**
	 * @param OIDplusObject|null $parent
	 * @return string
	 */
	public function jsTreeNodeName(OIDplusObject $parent = null): string {
		if ($parent == null) return $this->objectTypeTitle();
		return $this->domain;
	}

	/**
	 * @return string
	 */
	public function defaultTitle(): string {
		return _L('Allocations for '.$this->domain);
	}

	/**
	 * @return bool
	 */
	public function isLeafNode(): bool {
		return false;
	}

	/**
	 * @param string $title
	 * @param string $content
	 * @param string $icon
	 * @return void
	 * @throws OIDplusException
	 */
	public function getContentPage(string &$title, string &$content, string &$icon) {
		$icon = file_exists(__DIR__.'/img/main_icon.png') ? OIDplus::webpath(__DIR__,OIDplus::PATH_RELATIVE).'img/main_icon.png' : '';
      // $content='';
		
		
	//	$class = \get_called_class();
		$class = \get_class($this);
		$id=!$this->isRoot() ? @explode(':',$this->nodeId())[1] :  false;	
		$ns=$class::ns();
		if('~'===$ns && is_string($id) && !empty($id)){
			$ns=(string)$id;
		}elseif('~'===$ns && (!is_string($id) || empty($id))){
			$ns='%';
		}
		
		if(!is_string($id) || empty($id)){
		   $id ='';	
		}	
		
		
		
		if ($this->isRoot() && $ns === OIDplusNs::DEFNS) {
			$title = $class::objectTypeTitle();

			$content  .= '<p>'._L('Apps, Names, Services, Projects, Deployments').'</p>';
			$content  .= '<p>'
				._L('Some namespace/entries are not linked autmatically into the index (although they have public deeplinks)!')
				.'</p>';
			
			$res = OIDplus::db()->query("select * from ###objects where parent = ?", array($class::root()));
			if ($res->any()) {
				$content  .= '<p>'._L('Please select a Name/Service/Application or do a search.').'</p>';
			} else {
				$content  .= '<p>'._L(sprintf('Currently, no Name/Service/Application are linked into the namespace "%s".'
			  .'You may want top open <a onclick="window.location.hash=\'alloc_searchform\';return false;" '
											  .' href="javascript:window.location.hash=\'alloc_searchform\';">'
											  .'another namespace(-generator)</a>.',
											  $class::ns())).'</p>';
			}

			if (!$this->isLeafNode()) {
				if (OIDplus::authUtils()->isAdminLoggedIn()) {
					$content .= '<h2>'._L('Manage root objects').'</h2>';
				} else {
					$content .= '<h2>'._L('Available objects').'</h2>';
				}
				$content .= '%%CRUD%%';
			}
		} else {
			$title = $this->getTitle();

			// DOUBLE IN modifyContents : $content .= '<h3>'.$title.'</h3>';
			//$content .= '<h3>'.explode(':',$this->nodeId())[1].'</h3>';
			$content .= '<h3>'.$this->nodeId().'</h3>';

			$content .= '<h2>'._L('Description').'</h2>%%DESC%%'; // TODO: add more meta information about the object type

			if (!$this->isLeafNode()) {
				if ($this->userHasWriteRights()) {
					$content .= '<h2>'._L('Create or change subordinate objects').'</h2>';
				} else {
					$content .= '<h2>'._L('Subordinate objects').'</h2>';
				}
				$content .= '%%CRUD%%';
			}
		}
		
			

		$content .= '<div><a name="alloc_searchform"></name>';
		    $content.='<form onsubmit="return false;">';
		    $content.='<fieldset>';
		    $content .= '<h6>Search in our allocations database:</h6>';
		    $content .= '<label for="search_input_frdl_ns_plugin_namespace">Namespace:</label>';
		    $content .= sprintf('<input id="search_input_frdl_ns_plugin_namespace" value="%s" />', (string)$ns);
		    $content .= '<label for="search_input_frdl_ns_plugin_term">Search-Term:</label>';
		     $content .= sprintf('<input id="search_input_frdl_ns_plugin_term" value="%s" />', (string)$id);
		    
		 $content.='<p>';
		    $content .= '<button class="btn btn-primary" onclick="FrdlNsPluginSearch($(\'#search_input_frdl_ns_plugin_namespace\').val(), $(\'#search_input_frdl_ns_plugin_term\').val(), 1, 1);">'._L('Search').'</button><button class="btn btn-primary" onclick="FrdlNsPluginSearch(\'%\', $(\'#search_input_frdl_ns_plugin_term\').val(), 1, 1);">'._L('Search').' in all Namespaces</button>';
		    $content.='</p>';
		
	    	$content .= '<div id="search_output_frdl_ns_plugin"></div>';
		
		    $content.='</fieldset>';
		    $content.='</form>';
		$content .= '</div>';

		$content.=<<<HTMLCODE
<script>	
\$(document).ready(async ()=>{	
	FrdlNsPluginSearch("$ns", "$id");
	FrdlNsPluginSearch("%", "$id");
});
</script>			
HTMLCODE;		
	}



	
	
	public function getAltIds(): array {
		//return array();
		if ($this->isRoot()) return array();
		$a = $this->_original_oid_getAltIds();
		return $a;
		/*
		if('~:' !== substr($this->nodeId(),0,2) ){
			$a = array_merge($a, [
				new OIDplusAltId('proxy-ns',
													   '~:'.$this->nodeId(),
													   _L('Allocation Proxy Namespace'), 
													   ' ('._L('Proxy from "~:" to dynamic namespace target').')'),
				
				
			]);
		}
	
			$a = array_merge($a, [
				new OIDplusAltId('alloc',
													 //  '~:'.$this->nodeId(),
								                        $this->nodeId(true),
													   _L('Allocations Node'), 
													   ' ('._L('Allocations for '.$this->oid ).')'),
				
				
			]);		
		
		
		if(is_object($this->viewObject) && !is_null($this->viewObject)
		   && \get_class($this->viewObject) !== \get_class($this) && is_callable([$this->viewObject,'getAltIds'])
		   && true !== $this->viewObject instanceof OIDplusNs){
			$a = array_merge($a, $this->viewObject->getAltIds());
		}
		
		if(is_object($this->serviceObject) && !is_null($this->serviceObject)
		   && \get_class($this->serviceObject) !== \get_class($this) && is_callable([$this->serviceObject,'getAltIds']) 
		   && true !== $this->serviceObject instanceof OIDplusNs){
			$a = array_merge($a, $this->serviceObject->getAltIds());
		}
		return $a;
		*/
	}
	public function _original_oid_getAltIds(): array {
		if ($this->isRoot()) return array();
		$ids = [];

		if ($uuid = oid_to_uuid($this->oid)) {
			// UUID-OIDs are representation of an UUID
			$ids[] = new OIDplusAltId('guid', $uuid, _L('GUID representation of this OID'));
		} else {
			// All other OIDs can be formed into an UUID by making them a namebased OID
			// You could theoretically also do this to an UUID-OID, but we exclude this case to avoid that users are confused
			$ids[] = new OIDplusAltId('guid', gen_uuid_md5_namebased(UUID_NAMEBASED_NS_OID, $this->oid), _L('Name based version 3 / MD5 UUID with namespace %1','UUID_NAMEBASED_NS_OID'));
			$ids[] = new OIDplusAltId('guid', gen_uuid_sha1_namebased(UUID_NAMEBASED_NS_OID, $this->oid), _L('Name based version 5 / SHA1 UUID with namespace %1','UUID_NAMEBASED_NS_OID'));
		}

		$oid_parts = explode('.',$this->nodeId(false));

		// (VTS B1) Members
		if ($this->nodeId(false) == '1.3.6.1.4.1.37476.1') {
			$aid = 'D276000186B1';
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('No PIX allowed').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B1');
		} else {
			if ((count($oid_parts) == 9) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1') && ($oid_parts[6] == '37476') && ($oid_parts[7] == '1')) {
				$number = str_pad($oid_parts[8],4,'0',STR_PAD_LEFT);
				$aid = 'D276000186B1'.$number;
				$aid_is_ok = aid_canonize($aid);
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('Optional PIX allowed, without prefix').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B1');
			}
		}

		// (VTS B2) Products
		if ($this->nodeId(false) == '1.3.6.1.4.1.37476.2') {
			$aid = 'D276000186B2';
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('No PIX allowed').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B2');
		} else {
			if ((count($oid_parts) == 9) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1') && ($oid_parts[6] == '37476') && ($oid_parts[7] == '2')) {
				$number = str_pad($oid_parts[8],4,'0',STR_PAD_LEFT);
				$aid = 'D276000186B2'.$number;
				$aid_is_ok = aid_canonize($aid);
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('Optional PIX allowed, without prefix').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B2');
			}
		}

		// (VTS B2 00 05) OIDplus System AID / Information Object AID
		if ((count($oid_parts) == 10) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1') && ($oid_parts[6] == '37476') && ($oid_parts[7] == '30') && ($oid_parts[8] == '9')) {
			$sid = $oid_parts[9];
			$sid_hex = strtoupper(str_pad(dechex((int)$sid),8,'0',STR_PAD_LEFT));
			$aid = 'D276000186B20005'.$sid_hex;
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('OIDplus System Application Identifier (ISO/IEC 7816)'), ' ('._L('No PIX allowed').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B20005');
		}
		else if ((count($oid_parts) == 11) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1') && ($oid_parts[6] == '37476') && ($oid_parts[7] == '30') && ($oid_parts[8] == '9')) {
			$sid = $oid_parts[9];
			$obj = $oid_parts[10];
			$sid_hex = strtoupper(str_pad(dechex((int)$sid),8,'0',STR_PAD_LEFT));
			$obj_hex = strtoupper(str_pad(dechex((int)$obj),8,'0',STR_PAD_LEFT));
			$aid = 'D276000186B20005'.$sid_hex.$obj_hex;
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('OIDplus Information Object Application Identifier (ISO/IEC 7816)'), ' ('._L('No PIX allowed').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186B20005');
		}

		// (VTS F0) IANA PEN to AID Mapping (PIX allowed)
		if ((count($oid_parts) == 7) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1')) {
			$pen = $oid_parts[6];
			$aid = 'D276000186F0'.$pen;
			if (strlen($aid)%2 == 1) $aid .= 'F';
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('Optional PIX allowed, with "FF" prefix').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186F0');
			$ids[] = new OIDplusAltId('iana-pen', $pen, _L('IANA Private Enterprise Number (PEN)'));
		}

		// (VTS F1) FreeOID to AID Mapping (PIX allowed)
		if ((count($oid_parts) == 9) && ($oid_parts[0] == '1') && ($oid_parts[1] == '3') && ($oid_parts[2] == '6') && ($oid_parts[3] == '1') && ($oid_parts[4] == '4') && ($oid_parts[5] == '1') && ($oid_parts[6] == '37476') && ($oid_parts[7] == '9000')) {
			$number = $oid_parts[8];
			$aid = 'D276000186F1'.$number;
			if (strlen($aid)%2 == 1) $aid .= 'F';
			$aid_is_ok = aid_canonize($aid);
			if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('Optional PIX allowed, with "FF" prefix').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186F1');
		}

		// (VTS F6) Mapping OID-to-AID if possible
		try {
			$test_der = \OidDerConverter::hexarrayToStr(\OidDerConverter::oidToDER($this->nodeId(false)));
		} catch (\Exception $e) {
			$test_der = '00'; // error, should not happen
		}
		if (substr($test_der,0,3) == '06 ') { // 06 = ASN.1 type of Absolute ID
			if (($oid_parts[0] == '2') && ($oid_parts[1] == '999')) {
				// Note that "ViaThinkSoft E0" AID are not unique!
				// OIDplus will use the relative DER of the 2.999.xx OID as PIX
				$aid_candidate = 'D2 76 00 01 86 E0 ' . substr($test_der, strlen('06 xx 88 37 ')); // Remove ASN.1 06=Type, xx=Length and the 2.999 arcs "88 37"
				$aid_is_ok = aid_canonize($aid_candidate);
				if (!$aid_is_ok) {
					// If DER encoding is not possible (too long), then we will use a 32 bit small hash.
					$small_hash = str_pad(dechex(smallhash($this->nodeId(false))),8,'0',STR_PAD_LEFT);
					$aid_candidate = 'D2 76 00 01 86 E0 ' . strtoupper(implode(' ',str_split($small_hash,2)));
					$aid_is_ok = aid_canonize($aid_candidate);
				}
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid_candidate, _L('Application Identifier (ISO/IEC 7816)'), '', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186E0');
			} else if (($oid_parts[0] == '0') && ($oid_parts[1] == '4') && ($oid_parts[2] == '0') && ($oid_parts[3] == '127') && ($oid_parts[4] == '0') && ($oid_parts[5] == '7')) {
				// Illegal usage of E8 by German BSI, plus using E8+Len+OID instead of E8+OID like ISO does
				// PIX probably not used
				$aid_candidate = 'E8 '.substr($test_der, strlen('06 ')); // Remove ASN.1 06=Type
				$aid_is_ok = aid_canonize($aid_candidate);
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid_candidate, _L('Application Identifier (ISO/IEC 7816)'));
			} else if (($oid_parts[0] == '1') && ($oid_parts[1] == '0')) {
				// ISO Standard AID (OID 1.0.xx)
				// Optional PIX allowed
				$aid_candidate = 'E8 '.substr($test_der, strlen('06 xx ')); // Remove ASN.1 06=Type and xx=Length
				$aid_is_ok = aid_canonize($aid_candidate);
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid_candidate, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('Optional PIX allowed, without prefix').')');
			} else {
				// All other OIDs can be mapped using the "ViaThinkSoft F6" scheme, but only if the DER encoding is not too long
				// No PIX allowed
				$aid_candidate = 'D2 76 00 01 86 F6 '.substr($test_der, strlen('06 xx ')); // Remove ASN.1 06=Type and xx=Length
				$aid_is_ok = aid_canonize($aid_candidate);
				if ($aid_is_ok) $ids[] = new OIDplusAltId('aid', $aid_candidate, _L('Application Identifier (ISO/IEC 7816)'), ' ('._L('No PIX allowed').')', 'https://oidplus.viathinksoft.com/oidplus/?goto=aid%3AD276000186F6');
			}
		}

		return $ids;
	}		
	
	
	
	
	
	
	
	/**
	 * @return string
	 
	 public function getDirectoryName():string {
		if ($this->isRoot()) return $this->ns();
		return $this->ns().'_'.\DIRECTORY_SEPARATOR.str_replace('.', \DIRECTORY_SEPARATOR, $this->nodeId(false));
	}
	 
	public function getDirectoryName(): string {
		if ($this->isRoot()) return $this->ns();
		return sha1($this->ns()).'_'.md5($this->nodeId(false));
	}
*/
	public function getDirectoryName():string {
		if ($this->isRoot()) return self::$_ns;
		$hash = sha1($this->nodeId(false));
		$hash_NS = sha1($this->ns());
		$ns = preg_replace("/([^A-Za-z0-9\-\_\\\.\/])/", '.', self::$_ns);
		$id = preg_replace("/([^A-Za-z0-9\-\_\\\.\/])/", '.', $this->nodeId(false));
		
		$oldDirectoryName = 'ns'.\DIRECTORY_SEPARATOR.substr($hash_NS, 0,4)
			.\DIRECTORY_SEPARATOR.substr($hash_NS, strlen($hash_NS)-5,4)
			.\DIRECTORY_SEPARATOR.str_replace('.', \DIRECTORY_SEPARATOR, 
											  $ns		
											  .\DIRECTORY_SEPARATOR.substr($hash, 0,4)		
											  .\DIRECTORY_SEPARATOR.substr($hash, strlen($hash_NS)-5,4)
											  .\DIRECTORY_SEPARATOR.$id);
		
		
		$newDirectoryName = 'ns'.\DIRECTORY_SEPARATOR.substr($hash_NS, 0,4)
			.\DIRECTORY_SEPARATOR.substr($hash_NS, strlen($hash_NS)-5,4)
			.\DIRECTORY_SEPARATOR.str_replace(['.', ':'], ['_', '--'], //\DIRECTORY_SEPARATOR, 
											  $ns		
											  .\DIRECTORY_SEPARATOR.substr($hash, 0,4)		
											  .\DIRECTORY_SEPARATOR.substr($hash, strlen($hash)-5,4)
											  .\DIRECTORY_SEPARATOR.
											  preg_replace("/([^A-Za-z0-9\-\_\.])/", '~', $id)
											 );
		
		$base = 'userdata'.\DIRECTORY_SEPARATOR.'attachments'.\DIRECTORY_SEPARATOR;
		
		if(is_dir($base.$oldDirectoryName)){
			rename($base.$oldDirectoryName, $base.$newDirectoryName);
		}
		
		
		return $newDirectoryName;
	}
	 
	
	/**
	 * @param string $mode
	 * @return string
	 */
	public static function treeIconFilename(string $mode): string {
		return 'img/'.$mode.'_icon16.png';
	}
	
	
	
	public static function copyToDir(string $from, string $to, $mod = 0644, $modDir = 0644) {
       $dir = $from;
	   $new_dir = $to;
		$moved=[];
		$failed=[];
		$files = \scandir($dir);
		foreach($files as $file){
  
			if(!empty($file) && $file != '.' && $file != '..') {
     
				$source = $dir.'/'.$file;
      
				$destination = $new_dir.'/'.$file;
      
				if(!is_dir($new_dir)){
					mkdir($new_dir, $modDir, true);
				}else{
				    chmod($new_dir, $modDir);	
				}
				
				if(copy($source, $destination)) {
                    $moved[]=[$file, $source, $destination];
					chmod($destination, $mod);
				}else{
					$failed[]=[$file, $source, $destination];
				}
  
			}   

		}
		return ['ok'=>$moved, 'error'=>$failed];
	}	
	
	public static function copyToDirWithPhpAsText(string $from, string $to, $mod = 0644, $modDir = 0644) {
       $dir = $from;
	   $new_dir = $to;
		$moved=[];
		$failed=[];
		$files = \scandir($dir);
		foreach($files as $file){
  
			if(!empty($file) && $file != '.' && $file != '..') {
     
				$source = $dir.'/'.$file;
      
				$ext = pathinfo($file, \PATHINFO_EXTENSION);
				
				$destination = $new_dir.'/'.$file;
				
				if('php' === $ext){
					$destination.='.txt';
				}
      
				if(!is_dir($new_dir)){
					mkdir($new_dir, $modDir, true);
				}else{
				    chmod($new_dir, $modDir);	
				}
				
				if(copy($source, $destination)) {
                    $moved[]=[$file, $source, $destination];
					chmod($destination, $mod);
				}else{
					$failed[]=[$file, $source, $destination];
				}
  
			}   

		}
		return ['ok'=>$moved, 'error'=>$failed];
	}		
	
}
