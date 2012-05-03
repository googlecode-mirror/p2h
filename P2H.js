var JQURL = 'http://localhost/zhupp_google/p2h/jquery-1.2.min.js';
var updateURL = 'http://localhost/zhupp_google/p2h/demo/P2HUpdate.php';
main(JQURL, updateURL);

function main(JQURL, updateURL) {
	if(typeof jQuery==='undefined') {
		loadScript(JQURL, function(){
			ajax(updateURL);
		});
	}else{
		$(function(){
			ajax(updateURL);
		});
	}	
}

function getFilename() {
	var path = window.location.pathname.split('/');
	/* path[0] is empty path[1] is html dir name  */
	var dir = '';
	var dir = path[path.length-2];

	if(dir == "html" || dir == "" || path.length < 2) {
		dir = 'index';
	}
	return dir+'.php';
}

function ajax(updateURL) {
	$.getJSON(
			updateURL+"?from=html&jsoncallback=?&location="+window.location,
			function(data) {
				if(data.status) alert(data.status);
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