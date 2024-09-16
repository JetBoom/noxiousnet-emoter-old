<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/openid/openid.php';

//ini_set('display_errors', '1');

function windowfy($inp)
{
	return str_replace('/', '\\', dirname(__FILE__) .'\\'. $inp);
}

if (!SteamUser::isLoggedIn())
	die('Error: You are not logged in with Steam.');

if (SteamUser::getSteamID() != 'STEAM_0:1:3307510')
	die('Error: You are not authorized to approve things.');

$steamid = str_replace(':', '_', $_GET['steamid']);
$fid = $_GET['fid'];
$approve = $_GET['a'] == '1';
$dir = '';

if ($approve)
	$dir = 'approved/' . $steamid;
else
	$dir = 'rejected/' . $steamid;

$count = 1;
while (file_exists($dir .'/'. $count .'.txt'))
	$count++;

mkdir(windowfy($dir), 0777);

if (!$approve && $count >= 5) // Don't allow more than 5 rejected entries.
{
	$count = 1;
	array_map('unlink', glob($dir . '/*.*'));
}

foreach (glob('unapproved/' . $steamid . '/' . $fid . '.{wav,mp3,ogg}', GLOB_BRACE) as $k => $filename)
{
	$basename = basename($filename);
	$ext = pathinfo($basename, PATHINFO_EXTENSION);
	//$fid = pathinfo($basename, PATHINFO_FILENAME);

	$c = 1;
	while (file_exists($dir .'/'. $c . '.txt'))
		$c++;

	rename(windowfy($filename), $dir . '/'. $c . '.' . $ext);

	foreach (glob('unapproved/' . $steamid . '/'. $fid .'.txt', GLOB_BRACE) as $kk => $txtname)
		rename(windowfy($txtname), $dir . '/'. $c . '.txt');
}

rmdir(windowfy('unapproved/'. $steamid));

header('Location: ./');

?>