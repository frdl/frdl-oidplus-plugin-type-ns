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

// phpcs:disable PSR1.Files.SideEffects
\defined('INSIDE_OIDPLUS') or die;
// phpcs:enable PSR1.Files.SideEffects

class OIDplusNs extends OIDplusObject {
	use withAttributesTrait;
	/**
	 * @var string
	 */
	const DEFNS = '~';
	const REGEX_DEFNS = '(\~)';
	const REGEX_WEB_PROTOCOL_SCHEME = '(?<protocol>web\+(?<scheme>[\w\-]+))';
	const REGEX_DOMAIN = '(?<subdomain>[a-z0-9\-\_\*^\.]+)?(\.)(?<apex>[a-z0-9\-^\.]+)\.(?<tld>[a-z0-9\-^\.]+)';
	const REGEX_PACKAGE = '(?<vendor>[\w\-\_]+)\/(?<packagename>[\w\-\_]+)';
	const REGEX_OID = '(?<oid>[0-9\.]+)';
	const REGEX_IPV4 = '(?<ipv4>[\d]\.[\d]\.[\d]\.[\d])';
	const CLASS_TYPES_BASE_NS_GENERATED = 'Frdlweb\OIDplus\TypedNS';
	
	protected $domain;
	protected static $namespaces = [
		self::DEFNS=>[
			
			],
		
	];
	
	
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
	
	protected static $_namespacesPatterns =null;
	
	public static $_ns= self::DEFNS;
 
	
		
			
	
	protected $viewObject = null;								
	protected $serviceObject = null;
	
	protected static $LocalPsr4Autoloader = null;

		
	public function init(bool $html=true) {
       OIDplusObjectTypePluginNs::webfatInit( );
	}	
	
	public function __construct(string $domain, string $_ns= self::DEFNS, array $attributes = []) {
		// TODO: syntax checks
		$this->domain = $this->oid = $domain;
	    self::$_ns = $_ns;
		//foreach($attributes as $k=>$v){
		//	if(is_numeric($k))unset($attributes[$k]);
		//}
		$this->attributes = $attributes;
 
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
		foreach(self::getNamespacesPatterns() as $namespaceId => $dis){
			if(preg_match('/^'.$dis['regex'].'$/', $namespace, $match)){
				$match['ns'] = $match[0];   
				$matches=$match;
				$d=$domain;
				$namespace = \call_user_func_array($dis['getNamespace'], [$matches, $d]);
				break;
			}
		}
		if (false === $namespace || !isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace])) return null;

			
		if(isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'])){
		  $class = \Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'];	 
		}else{
			$class = \get_called_class();
		} 
		
        $object =new $class($domain, $namespace, $match);   
		return $object;
		//return new self($domain, $namespace);
	}
	 
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
					$namespace=$match['ns'];
					
					if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace])){
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]=[];
					}
		                      
	                       [$class, $ns] =\Frdlweb\OIDplus\OIDplusNs::namespaceToClassname($namespace);
						   $classNameFQ = $ns.'\\'.$class;	
	
	                       $classFile = \Frdlweb\OIDplus\OIDplusNs::objectTypeClassFile($classNameFQ);
	
	                if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'])){
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class']=$classNameFQ;
					}
	
	
					if(!file_exists($classFile) || filemtime($classFile) < time() - $ttl){
						// $classBuilder = \Nette\PhpGenerator\ClassType::from(\Frdlweb\OIDplus\OIDplusNs::class, true);
		                 $file = new \Nette\PhpGenerator\PhpFile;
		                 $file->addComment('This file is auto-generated. Do not edit it manually!');
						

						// $ns = new \Nette\PhpGenerator\PhpNamespace($ns);
						 $ns = $file->addNamespace($ns);
						 $class = $ns->addClass($class); //new \Nette\PhpGenerator\ClassType($class);
					  	 //$class ->setAbstract(true)
	                      $class->setExtends(\Frdlweb\OIDplus\OIDplusNs::class);
						
						  $DEFNS = $class->addConstant('DEFNS', $namespace);
						
						$m = $class->addMethod('__construct')	
						//	->setStatic()
							->setVisibility('public')	
						//	->setReturnReference()
							->addBody('parent::__construct($domain, $namespace, $attributes);')
							->addBody('$this->domain = $this->oid =$domain;')	
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
		array_shift($c);
		array_shift($c);
		array_shift($c);
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
	
		
	
	
	public function getAltIds(): array {
		$a =[];
		if('~:' !== substr($this->nodeId(),0,2) ){
			$a = array_merge($a, [
				new \ViaThinkSoft\OIDplus\OIDplusAltId('proxy-ns',
													   '~:'.$this->nodeId(),
													   _L('Allocation Proxy Namespace'), 
													   ' ('._L('Proxy from "~:" to dynamic namespace target').')'),
				
				
			]);
		}
		
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
		//return '~';
	}

	/**
	 * @return string
	 */
	public static function root(): string {
		//return '~:';
		return self::ns().':';
	}

	/**
	 * @return bool
	 */
	public function isRoot(): bool {
		return $this->domain == '';
	}

	/**
	 * @param bool $with_ns
	 * @return string
	 */
	public function nodeId(bool $with_ns=true): string {
		return $with_ns ? self::root().$this->domain : $this->domain;
	}

	/**
	 * @param string $str
	 * @return string
	 * @throws OIDplusException
	 */
	public function addString(string $str): string {
		if ($this->isRoot()) {
			return self::root().$str;
		} else {
			if (strpos($str,'.') !== false) throw new OIDplusException(_L('Please only submit one arc.'));
			return self::root().$str.'.'.$this->nodeId(false);
		}
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
		return $this->domain;
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
		   $id =false;	
		}	
		
		
		
		if ($this->isRoot()) {
			$title = self::objectTypeTitle();

			$content  .= '<p>'._L('Apps, Names, Services, Projects, Deployments').'</p>';
			$content  .= '<p>'
				._L('Some namespace/entries are not linked autmatically into the index (although they have public deeplinks)!')
				.'</p>';
			
			$res = OIDplus::db()->query("select * from ###objects where parent = ?", array(self::root()));
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

			$content .= '<h3>'.explode(':',$this->nodeId())[1].'</h3>';

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
async function FrdlNsPluginSearch(ns, term, search_title, search_description){			
  	var CacheKey=(ns+':'+term).toString();
	if('undefined'!==typeof FrdlNsPluginSearch.cache[CacheKey]){
		\$("#search_output_frdl_ns_plugin").prepend(FrdlNsPluginSearch.cache[CacheKey]);
		return;
	}
			
			
		\$.ajax({
			url:"ajax.php",
			method:"POST",
			beforeSend: function(jqXHR, settings) {
				\$.xhrPool.abortAll();
				\$.xhrPool.add(jqXHR);
			},
			complete: function(jqXHR, text) {
				\$.xhrPool.remove(jqXHR);
			},
			data: {
				csrf_token:csrf_token,
				plugin: OIDplusPagePublicSearch.oid,
				action:"search",
				namespace: ns,
				term: term,
				search_title: search_title || 0,
				search_description: search_description || 0,
				search_asn1id: 1,
				search_iri: 1
			},
			error: oidplus_ajax_error,
			success: function (data) {
				oidplus_ajax_success(data, function (data) {
					FrdlNsPluginSearch.cache[CacheKey]=data.output;
					\$("#search_output_frdl_ns_plugin").prepend(FrdlNsPluginSearch.cache[CacheKey]);
				});
			}
		});	
 
}
FrdlNsPluginSearch.cache={};		
\$(document).ready(async ()=>{	
	FrdlNsPluginSearch("$ns", "$id");
	FrdlNsPluginSearch("%", "$id");
});
</script>			
HTMLCODE;		
	}

	/**
	 * @return OIDplusDomain|null
	 */
	public function one_up()/*: ?OIDplusDomain*/ {
		$oid = $this->domain;

		$p = strpos($oid, '.');
		if ($p === false) return self::parse('');

		$oid_up = substr($oid, $p+1);

		return self::parse(self::ns().':'.$oid_up);
	}

	/**
	 * @param OIDplusObject|string $to
	 * @return int|null
	 */
	public function distance($to) {
		if (!is_object($to)) $to = OIDplusObject::parse($to);
		if (!$to) return null;
		if (!($to instanceof $this)) return null;

		$a = $to->domain;
		$b = $this->domain;

		if (substr($a,-1) == '.') $a = substr($a,0,strlen($a)-1);
		if (substr($b,-1) == '.') $b = substr($b,0,strlen($b)-1);

		$ary = explode('.', $a);
		$bry = explode('.', $b);

		$ary = array_reverse($ary);
		$bry = array_reverse($bry);

		$min_len = min(count($ary), count($bry));

		for ($i=0; $i<$min_len; $i++) {
			if ($ary[$i] != $bry[$i]) return null;
		}

		return count($ary) - count($bry);
	}

	/**
	 * @return string
	 */
	public function getDirectoryName(): string {
		if ($this->isRoot()) return $this->ns();
		return sha1($this->ns()).'_'.md5($this->nodeId(false));
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	public static function treeIconFilename(string $mode): string {
		return 'img/'.$mode.'_icon16.png';
	}
}
