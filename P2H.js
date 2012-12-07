<script type="text/javascript">

updateHTML('@JQURL@', '@updateURL@', '@phpURL@');

function updateHTML(JQURL, updateURL, phpURL) {
	if(typeof jQuery==='undefined') {
		loadScriptOnce(JQURL, function(){
			askToUpdate(updateURL, phpURL);
		});
	}else{
			askToUpdate(updateURL, phpURL);
	}	
}

function askToUpdate(updateURL, phpURL) {
	$.getJSON(
			updateURL+"?from=html&callback=?&location="+phpURL,
			function(data) {				
			}
		);
}

function loadScriptOnce(url,callback) {
    var script=document.createElement("script");
    script.type="text/javascript";
    if(script.readyState){
        script.onreadystatechange=function(){
            if(script.readyState=='loaded'||script.readyState=='complete'){
                script.onreadystatechange=null;
                callback();
            }
        }
    }else{script.onload=function(){callback();}}
    script.src=url;
    document.getElementsByTagName("head")[0].appendChild(script);
}
</script>