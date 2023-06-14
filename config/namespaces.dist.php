<?php
 
  



return 
 [
	
		\Frdlweb\OIDplus\OIDplusNs::REGEX_DEFNS=>[
			  'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_DEFNS,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			    'links'=>[
					  [
					     'title' => 'OIDplus Webfan IO4 Bridge', 
				      ],
				],
			],
			
		\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN=>[
			  'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],
			
		\Frdlweb\OIDplus\OIDplusNs::REGEX_PACKAGE=>[
			 'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_PACKAGE,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],			

		\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_PACKAGE=>[
			'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_PACKAGE,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],		
	


		'\@(?<actor>[\w\-\_]+)'.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN.''=>[
			    'regex'=>'\@(?<actor>[\w\-\_]+)'.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN.'',
			    'getNamespace'=>(function ($match, $domain){
				//	print_r('<pre>');
				//	  print_r(	$match );
					$namespace=$domain;
				 //	$match['ns']=$namespace;
					return \call_user_func_array(\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler', [$match, $domain]);
				}),
			],	
	
 
		\Frdlweb\OIDplus\OIDplusNs::REGEX_WEB_PROTOCOL_SCHEME=>[
			 'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_WEB_PROTOCOL_SCHEME,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],			
	//REGEX_IPV4
		'DOMAIN@IPV4'=>[ 
			 'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_IPV4,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],
	
	
		'IPV4@DOMAIN'=>[ 
			'title'=>'IPV4 ( at ) DOMAIN',
			 'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_IPV4.'\@'.\Frdlweb\OIDplus\OIDplusNs::REGEX_DOMAIN,
			    'getNamespace'=>\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler',
			],
	
			'OID@OID'=>[
		     	'title'=>'OID ( at ) OID',
				
				'links'=>[
					  [
					
				      ],
				],
				'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_OID.'\@'.'(?<domain>[0-9\.]+)',
			    'getNamespace'=>(function  ($match, $domain)  {
		  $ttl = 60 * 60;
				//	print_r('<pre>');
				//	  print_r(	$match );
					//$namespace=$match['ns'];
					$namespace=$match['oid'];
					$domain=$match['domain'];
					
					if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace])){
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]=[];
					}
		                      
	                       [$class, $ns] =\Frdlweb\OIDplus\OIDplusNs::namespaceToClassname($namespace);
						   $classNameFQ = $ns.'\\'.$class;	
	
	                       $classFile = \Frdlweb\OIDplus\OIDplusNs::objectTypeClassFile($classNameFQ);
	
	                if(!isset(\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class'])){
						\Frdlweb\OIDplus\OIDplusNs::$namespaces[$namespace]['class']=$classNameFQ;
					}
	
	
					if(!file_exists($classFile) || filemtime($classFile) < filemtime(__FILE__) || filemtime($classFile) < time() - $ttl){
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
							//->addBody('$this->copyToDir(?, $this->getDirectoryName(), 0644);', [
							//	\Frdlweb\OIDplus\OIDplusNs::getPluginDirectory(),
						//		
						//	]);
						$p = $m->addParameter('domain');
						//$p->setType(Type::String);
						//die($namespace.':'.$domain);
						if('1.3.6.1.4.1.37476.9000.108.19361.856'===$namespace && '1.3.6.1.4.1.37476.9000.108.19361.856' === $domain){
							$uploaddir = \ViaThinkSoft\OIDplus\OIDplusPagePublicAttachments::getUploadDir($namespace.':'.$domain);
							\Frdlweb\OIDplus\OIDplusNs::copyToDirWithPhpAsText( \Frdlweb\OIDplus\OIDplusNs::getPluginDirectory(),
																$uploaddir,
																 0644);
						}
					 
						
						
						$p2 = $m->addParameter('namespace', $namespace);
						//$p2->setType(Type::String);
						
						$p3 = $m->addParameter('attributes');
						//$p3->setType(Type::Array);
						
						  $code = (new \Nette\PhpGenerator\PsrPrinter)->printFile($file);
						
						  
						 
						 @mkdir(dirname($classFile), 0755, true);
						 file_put_contents($classFile, $code);
						 
						
					}					
					
					
		  return $namespace;				
	  }),
			],
	
	
		'OID Allocations'=>[
			'title'=>'OID Allocations',
			'description'=>'Allocate an OID<->ObjectType Binding and/or implementation-mappings.',
		   'regex'=>\Frdlweb\OIDplus\OIDplusNs::REGEX_OID,
			    'getNamespace'=>(function ($match, $domain){
			 
							$oid = sanitizeOID($match['oid'], 'auto');
		                  if ($oid === false) {
		                   	//throw new OIDplusException(_L('Invalid OID %1',$bak_oid));
			                return false;
	                 	}

		             if (($oid != '') && (!oid_valid_dotnotation($oid, false, true, 0))) {
			              // avoid OIDs like 3.0
		            //	throw new OIDplusException(_L('Invalid OID %1',$bak_oid));
						    return false;
	                  	}

	
					$namespace=$oid;
					 //$match['ns']=$namespace;
					return \call_user_func_array(\Frdlweb\OIDplus\OIDplusNs::class.'::defaultGetNamespaceTypeHandler', [$match, $oid]);
				}),
			],
	
	   ];
		
