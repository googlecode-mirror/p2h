<script type="text/javascript">
main('@JQURL@', '@phpURL@');

function main(JQURL, url) {
	if(typeof jQuery==='undefined') {
		loadScript(JQURL, function(){
			ajax(url);
		});
	}else{
		$(function(){
			ajax(url);
		});
	}	
}

function ajax(url) {
	$.getJSON(
			url+"?from=html&jsoncallback=?&location="+window.location,
			function(data) {
				if(data.status) console.log(data.status);
			}
		);
}

function loadScript(url,callback) {
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