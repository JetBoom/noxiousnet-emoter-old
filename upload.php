<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/openid/openid.php';

//ini_set('display_errors', '1');

if (!SteamUser::isLoggedIn())
	die('Error: You are not logged in with Steam.');

if (!isset($_POST['text']) || strlen($_POST['text']) < 3 || strlen($_POST['text']) > 100)
	die('Error: You must enter a text trigger between 3 and 100 characters long!');

if (!isset($_FILES['file']))
	die('Error: No file uploaded.');

if ($_FILES['file']['error'] > 0)
	die('Internal Error: ' . $_FILES['file']['error']);

if ($_FILES['file']['size'] < 256)
	die('Error: Sound file is too small!');

if ($_FILES['file']['size'] > 1024 * 1024)
	die('Error: Sound file cannot be more than 1MB (your size: '. $_FILES['file']['size'] .')!');

$basename = basename($_FILES['file']['name']);
$extension = pathinfo($basename, PATHINFO_EXTENSION);

/*$filetype = $_FILES['file']['type'];

if ($filetype != 'audio/ogg' && $filetype != 'audio/wav' && $filetype != 'audio/mp3'
	|| $extension != 'ogg' && $extension != 'wav' && $extension != 'mp3')
	die('Error: File type is not OGG, WAV, or MP3!');*/
if ($extension != 'ogg' && $extension != 'wav' && $extension != 'mp3')
	die('Error: File type is not OGG, WAV, or MP3!');

$steamid = str_replace(':', '_', SteamUser::getSteamID());
$dir = 'unapproved/' . $steamid;

$fid = 1;
while (file_exists($dir .'/'. $fid .'.txt'))
	$fid++;

if ($fid >= 10)
	die('Error: You already have 10 sounds awaiting approval.');

mkdir(str_replace('/', '\\', dirname(__FILE__) .'\\'. $dir), 0777);

move_uploaded_file($_FILES['file']['tmp_name'], str_replace('/', '\\', dirname(__FILE__) .'\\'. $dir . '\\' . $fid . '.' . $extension));

$file_handle = fopen(str_replace('/', '\\', dirname(__FILE__) .'\\'. $dir . '\\' . $fid . '.txt'), 'w');
fwrite($file_handle, $_POST['text']);
fclose($file_handle);

$email_headers = 'From: noreply@noxiousnet.com' . "\r\n" .
'Reply-To: noreply@noxiousnet.com' . "\r\n" .
'X-Mailer: PHP/' . phpversion();
$email_to = 'williammoodhe@gmail.com';
$email_subject = 'NoXiousNet Emoter - Emote awaiting approval';
$email_message = 'This automated message is to inform you that an emote is awaiting approval.';
mail($email_to, $email_subject, $email_message, $email_headers);

header('Location: /emoter');

?>