<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/globals.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/openid/openid.php';

$subtitle = 'Emoter';

function page_content()
{
	$price_per_emote = 4.00;
	
	echo '
	<div class="panel_content" style="width:550px;margin:0 auto;background:#151515;">';

	echo '	<div style="text-align:center;">
			<h1 style="font-size:26px;">Emoter</h1>
			<b>Get your own emotes added!</b><br>
			$'. sprintf("%01.2f", $price_per_emote) .' per emote! WOW! WHAT A BARGAIN!<br><br>
			<img src="emotesahoy.png" title="SO MUCH FUN EMOTES!">
			<br>
		</div>
		<br>
		<h2>Instructions</h2>
		<ul>
			<li>First, sign-in with Steam.</li>
			<li>Upload your sound files along with a text trigger for each.</li>
			<li>We will review your sounds within 24 hours. You can have up to 5 emotes waiting to be approved at once.</li>
			<li>Once your sound has been approved, you will see <span style="color:lime;font-weight:bold;">APPROVED</span> next to it. If your sound is rejected for breaking the rules below, you will see <span style="color:red;font-weight:bold;">REJECTED</span>.</li>
			<li>Click the BUY button to purchase all of your approved emotes.</li>
			<li>Your emotes will be added within a day of the payment (this process is currently manual).</li>
			<li>You will see <span style="color:cyan;font-weight:bold;">WAITING</span> next to emotes that are paid for.</li>
			<li>Your emote is successfully added when it is no longer on this list.</li>
		</ul>
		<h2>Rules</h2>
		<ul>
			<li>Sounds must be 5 seconds or less in length.</li>
			<li>No noise, square waves, music, or extremely offensive material.</li>
			<li>Must have a text trigger that makes sense. Text triggers may be changed if they are too short, misspelled, or would trigger during normal conversation.</li>
			<li>Suggested format: OGG Vorbis, Mono, 44.1khz (other formats such as WAV or MP3 are supported but will be converted)</li>
		</ul>
		<div style="font-size:10px;text-align:center;">
			If your emote has been added but is not working for you, try restarting your game.<br>If that still doesn\'t work, <a href="mailto:admin@noxiousnet.com">send us an e-mail</a>.
		</div>';

	if (SteamUser::isLoggedIn())
	{
		$steamid = str_replace(':', '_', SteamUser::getSteamID());
		
		$waiting_sounds = glob('waiting/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
		$waiting_text = glob('waiting/'. $steamid .'/*.txt', GLOB_BRACE);
		$approved_sounds = glob('approved/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
		$approved_text = glob('approved/'. $steamid .'/*.txt', GLOB_BRACE);
		$unapproved_sounds = glob('unapproved/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
		$unapproved_text = glob('unapproved/'. $steamid .'/*.txt', GLOB_BRACE);
		$rejected_sounds = glob('rejected/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
		$rejected_text = glob('rejected/'. $steamid .'/*.txt', GLOB_BRACE);

		$nwaiting = count($waiting_sounds);
		$napproved = count($approved_sounds);
		$nunapproved = count($unapproved_sounds);
		$nrejected = count($rejected_sounds);

		if ($nwaiting > 0 || $napproved > 0 || $nunapproved > 0 || $nrejected > 0)
		{
			echo '
			<hr>
			<table style="width:100%;">
			<thead>
				<th style="width:30%;">Text</th>
				<th>Sound</th>
				<th>Status</th>
			</thead>
			<tbody>';

			foreach ($waiting_sounds as $k => $v)
			{
				echo '
				<tr>
					<td>&quot;'. htmlentities(file_get_contents($waiting_text[$k])) . '&quot;</td>
					<td><audio src="'. $v .'" controls="controls" preload="none"></audio></td>
					<td style="color:cyan;font-weight:bold;">WAITING</td>
				</tr>';
			}
			foreach ($approved_sounds as $k => $v)
			{
				$fid = pathinfo(basename($v), PATHINFO_FILENAME);

				echo '
				<tr>
					<td>&quot;'. htmlentities(file_get_contents($approved_text[$k])) . '&quot;</td>
					<td><audio src="'. $v .'" controls="controls" preload="none"></audio></td>
					<td style="color:lime;font-weight:bold;text-align:center;">
						APPROVED<br>
						<a href="delete_own.php?fid='. $fid .'&t=1" onclick="return confirm(\'Are you sure you want to delete it?\')">[Delete]</a>';
					echo '
					</td>
				</tr>';
			}
			foreach ($unapproved_sounds as $k => $v)
			{
				$fid = pathinfo(basename($v), PATHINFO_FILENAME);

				echo '
				<tr>
					<td>&quot;'. htmlentities(file_get_contents($unapproved_text[$k])) . '&quot;</td>
					<td><audio src="'. $v .'" controls="controls" preload="none"></audio></td>
					<td style="color:orange;font-weight:bold;text-align:center;">
						UNAPPROVED<br>
						<a href="delete_own.php?fid='. $fid .'&t=0" onclick="return confirm(\'Are you sure you want to delete it?\')">[Delete]</a>
					</td>
				</tr>';
			}
			foreach ($rejected_sounds as $k => $v)
			{
				$fid = pathinfo(basename($v), PATHINFO_FILENAME);

				echo '
				<tr>
					<td>&quot;'. htmlentities(file_get_contents($rejected_text[$k])) . '&quot;</td>
					<td><audio src="'. $v .'" controls="controls" preload="none"></audio></td>
					<td style="color:red;font-weight:bold;text-align:center;">
						REJECTED<br>
						<a href="delete_own.php?fid='. $fid .'&t=2")">[Delete]</a>
					</td>
				</tr>';
			}

			echo '
			</tbody>
			</table>';

			if ($napproved > 0)
			{
				echo '<br>
					<div style="text-align:center;font-size:24px;color:lime;font-weight:bold;">
					Total: $'. sprintf("%01.2f", $price_per_emote * $napproved) .'<br>
					<form method="post" action="https://www.paypal.com/cgi-bin/webscr">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="item_number" value="11">
					<input type="hidden" name="amount" value="'. sprintf("%01.2f", $price_per_emote * $napproved) .'">
					<input type="hidden" name="business" value="williammoodhe@gmail.com">
					<input type="hidden" name="item_name" value="NoXiousNet Emoter Payment">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="notify_url" value="http://www.noxiousnet.com/emoter/handler.php">
					<input type="hidden" name="return" value="http://www.noxiousnet.com/emoter">
					<input type="hidden" name="cancel_return" value="http://www.noxiousnet.com/emoter">
					<input type="hidden" name="tax" value="0">
					<input type="hidden" name="test_ipn" value="0">
					<input type="hidden" name="rm" value="2">
					<input type="hidden" name="bn" value="PP-NN">
					<input type="hidden" name="custom" value="'. $steamid .'">
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" style="border:0;background:0;">
					<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
					</div>';
			}
		}

		if ($nunapproved < 10)
		{
			echo '
				<hr>
				<h3 style="font-size:22px;text-align:center;">Upload</h3>
				<form action="upload.php" method="post" enctype="multipart/form-data">
				<label for="file">Sound:</label>
				<input type="file" name="file" id="file"><br>
				<label for="text">Text trigger:</label>
				<input type="text" name="text" id="text"><br>
				<input type="submit" name="submit" value="Upload">
				</form>';
		}

		if ($steamid == 'STEAM_0_1_3307510')
		{
			echo '
				<hr>
				<h3 style="font-size:22px;text-align:center;">Admin</h3>
				<br>
				<table style="width:100%;font-size:9px;">
				<thead>
					<th>Owner</th>
					<th style="width:30%;">Text</th>
					<th>Sound</th>
					<th>Status</th>
				</thead>
				<tbody>';

			foreach (glob('waiting/*', GLOB_ONLYDIR) as $_ => $sdir)
			{
				$sid = basename($sdir);
				$waiting_sounds = glob($sdir .'/*.{wav,mp3,ogg}', GLOB_BRACE);
				$waiting_text = glob($sdir .'/*.txt', GLOB_BRACE);
				$nwaiting = count($waiting_sounds);

				foreach ($waiting_sounds as $fid => $filename)
				{
					$basefilename = pathinfo(basename($filename), PATHINFO_FILENAME);
					echo '
					<tr>
						<td colspan="2">&quot;'. htmlentities(file_get_contents($waiting_text[$fid])) . '&quot;</td>
						<td><audio src="'. $filename .'" controls="controls" preload="none"></audio></td>
						<td style="color:cyan;font-weight:bold;">
							WAITING<br>
							<a href="'. $filename .'">[Download]</a><br>
							<a href="delete.php?steamid='. $sid .'&fid='. $basefilename .'" onclick="return confirm(\'Are you sure you want to delete it?\')">[Delete]</a>
						</td>
					</tr>';
				}
			}

			foreach (glob('unapproved/*', GLOB_ONLYDIR) as $_ => $sdir)
			{
				$sid = basename($sdir);
				$unapproved_sounds = glob($sdir .'/*.{wav,mp3,ogg}', GLOB_BRACE);
				$unapproved_text = glob($sdir .'/*.txt', GLOB_BRACE);
				$nunapproved = count($unapproved_sounds);

				foreach ($unapproved_sounds as $fid => $filename)
				{
					$basefilename = pathinfo(basename($filename), PATHINFO_FILENAME);
					echo '
					<tr>
						<td>'. $sid .'</td>
						<td>&quot;'. htmlentities(file_get_contents($unapproved_text[$fid])) . '&quot;</td>
						<td><audio src="'. $filename .'" controls="controls" preload="none"></audio></td>
						<td style="color:orange;font-weight:bold;">
							UNAPPROVED<br>
							<a href="approve.php?steamid='. $sid .'&fid='. $basefilename .'&a=1">[A]</a> <a href="approve.php?steamid='. $sid .'&fid='. $basefilename .'&a=0">[R]</a>
						</td>
					</tr>';
				}
			}

			/*$waiting_sounds = glob('waiting/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
			$waiting_text = glob('waiting/'. $steamid .'/*.txt', GLOB_BRACE);
			$approved_sounds = glob('approved/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
			$approved_text = glob('approved/'. $steamid .'/*.txt', GLOB_BRACE);
			$rejected_sounds = glob('rejected/'. $steamid .'/*.{wav,mp3,ogg}', GLOB_BRACE);
			$rejected_text = glob('rejected/'. $steamid .'/*.txt', GLOB_BRACE);

			$nwaiting = count($waiting_sounds);
			$napproved = count($approved_sounds);
			$nrejected = count($rejected_sounds);*/

			echo '
			</tbody>
			</table>';
		}
	}
	else
	{
		echo '
			<div style="text-align:center;font-size:22px;font-weight:bold;color:red;">
				You need to be signed in with Steam first.
				<br>
				<br>
				'. SteamUser::getLoginArea(true) .'
			</div>';
	}

	echo '
	</div>';
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/pagebase.php';

?>
