<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admin.php 1059 2011-03-01 07:25:09Z monkey $
*/

require_once dirname(__FILE).'/ucenter_core.php';

unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

$_GET		= daddslashes($_GET, 1, TRUE);
$_POST		= daddslashes($_POST, 1, TRUE);
$_COOKIE	= daddslashes($_COOKIE, 1, TRUE);
$_SERVER	= daddslashes($_SERVER);
$_FILES		= daddslashes($_FILES);
$_REQUEST	= daddslashes($_REQUEST, 1, TRUE);
require UC_ROOT.'./release/release.php';
require UC_ROOT.'model/base.php';
require UC_ROOT.'model/admin.php';

$m = getgpc('m');
$a = getgpc('a');
$m = empty($m) ? 'frame' : $m;
$a = empty($a) ? 'index' : $a;

define('RELEASE_ROOT', '');

if(in_array($m, array('admin', 'app', 'badword', 'cache', 'db', 'domain', 'frame', 'log', 'note', 'feed', 'mail', 'setting', 'user', 'credit', 'seccode', 'tool', 'plugin', 'pm'))) {
	include UC_ROOT."control/admin/$m.php";
	$control = new control();
	$method = 'on'.$a;
	if(method_exists($control, $method) && $a{0} != '_') {
		$control->$method();
	} elseif(method_exists($control, '_call')) {
		$control->_call('on'.$a, '');
	} else {
		exit('Action not found!');
	}
} else {
	exit('Module not found!');
}

$mtime = explode(' ', microtime());
$endtime = $mtime[1] + $mtime[0];
//echo '<script>document.getElementById(\'debug_time\').innerHTML = \''.number_format($endtime - $starttime, 5).'\'</script>'."\n";


?>