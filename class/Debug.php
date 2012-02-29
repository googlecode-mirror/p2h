<?php
if (!defined('M5CPL'))exit;
/**
 * 
 * 调试类
 * @author zhupp 20110823
 *
 */
class Debug {
	protected static $sep = "\t\t";//分割符

	public static function write($con,$filename='log.txt') {
		if(!DEBUG_STATIC) return false;
		file_put_contents($filename, date('Y-m-d H:i:s').self::$sep.$con.PHP_EOL,FILE_APPEND);
	}

	
	
}


?>