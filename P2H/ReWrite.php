<?php
/**
 *@copyright Copyright (c) 2011, vyouzhi
 *
 *
 */

if (!defined('M5CPL'))	exit;

require_once 'Ajax.php';
require_once 'Path.php';

class ReWrite{
	
	private static $_rw;
	
    public function __construct() {
    
    }

    public final function __clone()
    {
        throw new BadMethodCallException("Clone is not allowed");
    } 
 
    /**
     * getInstance 
     * 
     * @static
     * @access public
     * @return 
     */
    public function getInstance() {
        return (self::$_rw instanceof self) ? self::$_rw : new self(); 
    }
	
	/**
	 * rewrite url and build ajax page
	 * in:  	http://localhost/android/list_index.php?gid=1&cid=69
	 * out:	http://localhost/android/html/list_index/1_69.html
	 */	
	public static function RW($url) {

		if(IS_STATIC) {					
			Ajax::build($url);
			return  self::RWURL($url);
		}else{
			return $url;
		}

	}
	
	/**
	 * 返回静态地址
	 */
	public function RWURL($url) {
		return HTML_PATH.Path::h($url);
	}
	
	/**
	 * rewrite html to php
	 * in:  	http://localhost/android/html/list_index/1_69.html
	 * out:	http://localhost/android/list_index.php?gid=1&cid=69
	 */	
	public static function UnRW($html) {
		
		$dh_str = strtolower(substr($html, strlen(HTML_PATH)));
	
		if($dh_str == 'index'.RW_END) {
			return UPDATE_URL.'index.php';
		}
		
		$dh_arr = explode('/', str_replace('\\', '/', $dh_str));

		$phpname = $dh_arr[0];
		$php = $phpname.'.php';
		if($dh_arr[1] == 'index'.RW_END) {
			return UPDATE_URL.$dh_arr[0].'.php';
		}
		$args_str = str_replace(RW_END, '', $dh_arr[1]);

		$args_arr = explode(RW_RULE, $args_str);
		
		$q = '';
		$args = Path::$args;
		foreach ($args[$phpname] as $k=>$v) {
			if(isset($args_arr[$k])) $q .= $v.'='.$args_arr[$k].'&';
		}

		return UPDATE_URL.$php.'?'.rtrim($q,'&');
	}
		
	
}

?>