<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: index.php 1059 2011-03-01 07:25:09Z monkey $
*/

require dirname(__FILE).'/ucenter_core.php';
unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

$_GET		= daddslashes($_GET, 1, TRUE);
$_POST		= daddslashes($_POST, 1, TRUE);
$_COOKIE	= daddslashes($_COOKIE, 1, TRUE);
$_SERVER	= daddslashes($_SERVER);
$_FILES		= daddslashes($_FILES);
$_REQUEST	= daddslashes($_REQUEST, 1, TRUE);
require UC_ROOT.'./release/release.php';

$m = getgpc('m');
$a = getgpc('a');
if(empty($m) && empty($a)) {
	header('Location: admin.php');
	exit;
}

define('RELEASE_ROOT', '');

if(UC::file()->file_exists(UC_ROOT.RELEASE_ROOT.'model/base.php')) {
	require UC_ROOT.RELEASE_ROOT.'model/base.php';
} else {
	require UC_ROOT.'model/base.php';
}

if(in_array($m, array('app', 'frame', 'user', 'pm', 'pm_client', 'tag', 'feed', 'friend', 'domain', 'credit', 'mail', 'version'))) {

	if(UC::file()->file_exists(UC_ROOT.RELEASE_ROOT."control/$m.php")) {
		include UC_ROOT.RELEASE_ROOT."control/$m.php";
	} else {
		include UC_ROOT."control/$m.php";
	}

	$classname = $m.'control';
	$control = new $classname();
	$method = 'on'.$a;
	if(method_exists($control, $method) && $a{0} != '_') {
		$data = $control->$method();
		echo is_array($data) ? $control->serialize($data, 1) : $data;
		exit;
	} elseif(method_exists($control, '_call')) {
		$data = $control->_call('on'.$a, '');
		echo is_array($data) ? $control->serialize($data, 1) : $data;
		exit;
	} else {
		exit('Action not found!');
	}

} else {

	exit('Module not found!');

}

$mtime = explode(' ', microtime());
$endtime = $mtime[1] + $mtime[0];

?>