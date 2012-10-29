<script type="text/javascript">

updateHTML('@JQURL@', '@phpURL@');

function updateHTML(JQURL, url) {
	if(typeof jQuery==='undefined') {
		loadScriptOnce(JQURL, function(){
			askToUpdate(url);
		});
	}else{
			askToUpdate(url);
	}	
}

function askToUpdate(url) {
	$.getJSON(
			url+"?from=html&callback=?&location="+window.location,
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