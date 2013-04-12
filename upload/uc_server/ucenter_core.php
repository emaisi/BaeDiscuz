<?php

error_reporting(0);
set_magic_quotes_runtime(0);

$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

define('IN_UC', TRUE);
define('UC_ROOT', dirname(__FILE__).'/');
define('UC_API', strtolower((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'))));
define('UC_DATADIR', UC_ROOT.'data/');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());

//移动UCenter目录时指定正确的bcs路径
require_once UC_ROOT.'../bcs/class_bcsfile.php';

UC::init();

class ucenter_core{
	private static $_file;

	public static function init() {

	}
	
	public static function file() {
		if(!self::$_file) {
			$file = new bcsfile();

			//设置根目录
			$file->set_root_path(UC_ROOT.'../');
			
			//设置需要写到云存储的目录
			$file->add_remote_dir(UC_DATADIR);

			//设置可写的临时目录
			$file->set_temp_dir(sys_get_temp_dir());

			self::$_file  = $file;
		}
		return self::$_file;
	}
}

class UC extends ucenter_core {}

if(!@include UC::file()->save_file(UC_DATADIR.'config.inc.php')) {
	exit('The file <b>data/config.inc.php</b> does not exist, perhaps because of UCenter has not been installed, <a href="install/index.php"><b>Please click here to install it.</b></a>.');
}

function daddslashes($string, $force = 0, $strip = FALSE) {
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

function getgpc($k, $t='R') {
	switch($t) {
		case 'P': $var = &$_POST; break;
		case 'G': $var = &$_GET; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	return isset($var[$k]) ? (is_array($var[$k]) ? $var[$k] : trim($var[$k])) : NULL;
}

?>