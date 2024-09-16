<?php
//1 = Live on PayPal Network 
//2 = Testing with the PayPal Sandbox
//3 = Debug
$verifymode = 1;

function windowfy($inp)
{
	return str_replace('/', '\\', dirname(__FILE__) .'\\'. $inp);
}

if ($verifymode == 3)
{
	ini_set('display_errors', '1');

	$_POST['txn_type'] = 'web-accept';
	$_POST['receiver_email'] = 'williammoodhe@gmail.com';
	$_POST['business'] = 'williammoodhe@gmail.com';
	$_POST['custom'] = 'STEAM_0_1_3307510';
	$_POST['payment_status'] = 'Completed';
	$_POST['mc_currency'] = 'USD';
	$_POST['mc_gross'] = '5.00';

	echo 'a';
}

$price_per = 4.00;

if (!isset($_POST['txn_type']))
{
	header("Location: /emoter");
	die();
}

// Now we Read the Posted IPN
$postkeys = array();
$postvalues = array();
foreach ($_POST as $key => $value)
{
	if ($key != 'cmd')
	{
		$postkeys[] = $key;
		$postvalues[] = urldecode($value);
	}
}


$postipn = 'cmd=_notify-validate';
//$orgipn = '<b>Posted IPN variables in order received:</b><br /><br />';

for ($i=0; $i < count($postkeys); $i++)
{
	$postk = $postkeys[$i];
	$postv = $postvalues[$i];
	$postipn .= "&" . $postk . "=" . urlencode($postv);
	//$orgipn .= "<b>" . $i . "</b> Key: " . $postk . " <b>=</b> " . $postv . "<br />";
}

if ($verifymode == 1)
{
	$port = fsockopen("www.paypal.com", 80, $errno, $errstr, 30);
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\nHost: www.paypal.com\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($postipn) . "\r\n\r\n";
}
elseif ($verifymode == 2)
{
	$port = fsockopen("www.sandbox.paypal.com", 80, $errno, $errstr, 30);
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\nHost: www.sandbox.paypal.com\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($postipn) . "\r\n\r\n";
}
elseif ($verifymode != 3)
	die();

//$orgipn .= "Sent POST to PayPal: " . $header . $postipn;
//$handle = fopen("stuff.html", 'a');
//fwrite($handle, $orgipn);
//fclose($handle);

if ($verifymode != 3)
{
	fputs($port, $header . $postipn);
	while (!feof($port))
		$reply = trim(fgets($port, 1024));

	//$handle = fopen("stuff.html", 'a');
	//fwrite($handle, "<br />Reply: " . $reply);
	//fclose($handle);

	if ($reply != 'VERIFIED')
		die();
}

if ($_POST['receiver_email'] != "williammoodhe@gmail.com" || $_POST['business'] != "williammoodhe@gmail.com")
	die();

$txn_type = $_POST['txn_type'];

if ($txn_type != 'web-accept' && $txn_type != 'web_accept')
	die();

$steamid = $_POST['custom'];
preg_match('/STEAM_[0-9]_[0-9]_([0-9]+)/', $steamid, $matches);
if (!$matches[1])
	die();

$paymentstatus = $_POST['payment_status'];

if ($paymentstatus != 'Completed' && $paymentstatus != 'Cleared' && $paymentstatus != 'Pending')
	die();

//$fid = intval($_POST['item_number']);
//if ($fid == 0)
	//die();

$payment = 0;
if ($_POST['mc_currency'] == 'USD')
	$payment = $_POST['mc_gross'];
else
	$payment = $_POST['settle_amount'];

if ($verifymode == 3)
	echo 'payment:', $payment, '<br>';

//if ($price > $payment)
	//die();

$dir = windowfy('waiting/' . $steamid);

//$c = 1;
//while (file_exists($dir .'/'. $c .'.txt'))
	//$c++;

mkdir($dir, 0777);

if ($verifymode == 3)
	echo 'b<br>';

foreach (glob('approved/' . $steamid . '/*.{wav,mp3,ogg}', GLOB_BRACE) as $k => $filename)
{
	if ($verifymode == 3)
		echo 'found file:', $filename, '<br>';

	$payment -= $price_per;
	if ($payment < 0)
		break;

	$basename = basename($filename);
	$ext = pathinfo($basename, PATHINFO_EXTENSION);
	$fid = pathinfo($basename, PATHINFO_FILENAME);

	$c = 1;
	while (file_exists($dir .'/'. $c . '.txt'))
		$c++;

	rename(windowfy($filename), $dir . '/'. $c . '.' . $ext);

	foreach (glob('approved/' . $steamid . '/'. $fid .'.txt', GLOB_BRACE) as $kk => $txtname)
		rename(windowfy($txtname), $dir . '/'. $c . '.txt');
}

rmdir(windowfy('approved/'. $steamid));

?>