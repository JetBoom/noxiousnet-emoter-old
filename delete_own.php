<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/openid/openid.php';

//ini_set('display_errors', '1');

if (!SteamUser::isLoggedIn())
	die('Error: You are not logged in with Steam.');

$steamid = str_replace(':', '_', SteamUser::getSteamID());

$fid = intval($_GET['fid']);
$dir = 'unapproved';
if ($_GET['t'] == '1')
	$dir = 'approved';
elseif ($_GET['t'] == '2')
	$dir = 'rejected';

array_map('unlink', glob($dir . '/' . $steamid . '/'. $fid . '.*'));

rmdir(str_replace('/', '\\', dirname(__FILE__) .'\\'. $dir . '\\' . $steamid));

header('Location: ./');

?>