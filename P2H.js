var updateURL = 'http://localhost/unbox/ppl/bin/P2H/P2H.php';
$.getJSON(
	update_url+"?from=html&location="+window.location,
	function(data){
		if(data.status) alert(data.status);
	}
);	
