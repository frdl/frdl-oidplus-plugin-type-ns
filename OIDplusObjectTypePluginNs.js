$(document).ready(async ()=>{	
var t = false;
function f(){
 setTimeout(async ()=>{
  
  if('undefined'===typeof require){
	  if(t)clearTimeout(t);
	  t=setTimeout(f,450);
	  return;
  }
	 
	(await require('frdlweb')).Webfan.EventEmitter.DEFAULT.on('mutation-observer', EventData=>{
		var {name, data} = EventData;
		if(data.changed.document || data.changed.location){
			 f();
		}
	});
	 
	 
 (async(table)=>{
  if(null===table){
   return;
  }
   table.querySelectorAll('a').forEach(async (a)=>{
    if(''===a.innerHTML && a.hasAttribute('href') && 'javascript:'!== a.getAttribute('href').substr(0,'javascript:'.length) 
	    && 'openAndSelectNode' === a.getAttribute('onclick').substr(0,'openAndSelectNode'.length)
	  ){
        a.innerHTML=(await require('frdlweb')).lib.urldecode(a.getAttribute('href').replace(/\?goto\=/, ''));
    }
  });
 })(document.getElementById('crudTable'));
 },1000);
}
f();
});


async function FrdlNsPluginSearch(ns, term, search_title, search_description){			
  	var CacheKey=(ns+':'+term).toString();
	if('undefined'!==typeof FrdlNsPluginSearch.cache[CacheKey]){
		$("#search_output_frdl_ns_plugin").prepend(FrdlNsPluginSearch.cache[CacheKey]);
		return;
	}
			
			
		$.ajax({
			url:"ajax.php",
			method:"POST",
			beforeSend: function(jqXHR, settings) {
				$.xhrPool.abortAll();
				$.xhrPool.add(jqXHR);
			},
			complete: function(jqXHR, text) {
				$.xhrPool.remove(jqXHR);
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
					$("#search_output_frdl_ns_plugin").prepend(FrdlNsPluginSearch.cache[CacheKey]);
				});
			}
		});	
 
}
FrdlNsPluginSearch.cache={};
