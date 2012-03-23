<?php
/**
 * BuildAjax::build('http://localhost/android/list_index.php?gid=1&cid=69');
 */

if (!defined('M5CPL'))	exit;

require_once 'Path.php';
class Ajax {
	
	private static $_tpl = '<html><head><script type="text/javascript" src="http://img2.xda-china.com/android/static/js/jquery-1.2.6.pack.js"></script>
	<script>
	$(function() {
		$.getJSON(
			"@FILE@?%PARAM%",
			function(data){
				if(data.status==0) top.location.href=(eval(data.url));
				else if(data.status==1) top.location.reload();
			}
		);
	});
	</script>
</head><body></body></html>';
        


	/**
	 * 生成ajax请求页
	 */
	public static function build($url) {
		$htmls_dir = Path::get_htmls_dir();
		if(!is_dir($htmls_dir)) {
			mkdir($htmls_dir, 0777);
		}
		$dq = Path::dq($url);		
		if(!is_dir($htmls_dir.$dq['d']) && $dq['d']!='index')	{
			mkdir($htmls_dir.$dq['d'], 0777);
		}
		$html = $htmls_dir.Path::h($url);
		if(!file_exists($html)) {			
			$ajax = $s = '';
			if(!empty($dq['q'])) {
				foreach($dq['q'] as $k=>$v) {
					$s .= $k.'='.$v.'&';
				}
				$ajax = $s.'from=ajax&jsoncallback=?';
			}			
			
			$search = array('%PARAM%', '@FILE@');
			$replace = array($ajax, UPDATE_URL.$dq['d'].'.php');                        
			$con = str_replace($search, $replace, self::$_tpl);
			file_put_contents($html, $con);		
		}	
     }
}