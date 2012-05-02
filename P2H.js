var ROOT = 'http://localhost/p2h/';
//var updateURL = ROOT+'P2H.php';


update(jqURL);

function update(jqURL) {
	loadJQ(jqURL);
	$.getJSON(
			update_url+"?from=html&location="+window.location,
			function(data){
				if(data.status) alert(data.status);
			}
		);
}

function loadJQ(url) {
	if(typeof jQuery !='undefined') return;
	document.write('<script type="text/javascript" src="'+url+'"><\/script>');
}