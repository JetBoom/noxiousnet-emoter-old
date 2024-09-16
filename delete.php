<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/openid/openid.php';

//ini_set('display_errors', '1');

if (!SteamUser::isLoggedIn())
	die('Error: You are not logged in with Steam.');

if (SteamUser::getSteamID() != 'STEAM_0:1:3307510')
	die('Error: You are not authorized to delete things.');

$steamid = str_replace(':', '_', $_GET['steamid']);
$fid = $_GET['fid'];

array_map('unlink', glob('waiting/' . $steamid . '/'. $fid . '.*'));

rmdir(str_replace('/', '\\', dirname(__FILE__) .'\\waiting\\'. $steamid));

header('Location: ./');

?>