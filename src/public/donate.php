<?php

	//include config
	require_once __DIR__ . "/../config/config.php";

	//documentation urls
	$faq_url = CONTACTPAGE_MOBILE_FAQ_URL;
	$doc_url = CONTACTPAGE_MOBILE_DOCUMENTATION_URL;
	$home = getHomepageHtml();
	$email = getEmailHtml();
	$gh = getGithubHtml();
	$discord = getDiscordHtml();

	//functions
	function getHomepageHtml(): string {
		
		$add_home = CONTACTPAGE_MOBILE_ADD_HOMEPAGE;
		$home_url = CONTACTPAGE_MOBILE_HOMEPAGE_URL;
		$home = !$add_home ? "" : "<a class='black-link-bold' href='$home_url'>Listiary Demo Instance</a><br>";
		return $home;
	}
	function getEmailHtml(): string {
		
		$email = "email: " . CONTACTPAGE_MOBILE_EMAIL;
		
		return $email;
	}
	function getGithubHtml(): string {
		
		$add_gh = CONTACTPAGE_MOBILE_ADD_GITHUB;
		$gh1_name = CONTACTPAGE_MOBILE_GITHUB1_NAME;
		$gh2_name = CONTACTPAGE_MOBILE_GITHUB2_NAME;
		$gh1_url = CONTACTPAGE_MOBILE_GITHUB1_URL;
		$gh2_url = CONTACTPAGE_MOBILE_GITHUB2_URL;
		$gh = "";
		if($add_gh && is_string($gh1_url) && trim($gh1_url) !== "" && 
			is_string($gh1_name) && trim($gh1_name) !== "")
		{
			$gh = "github: ";
			$gh .= "<a class='black-link' href='$gh1_url'>$gh1_name</a>";
			if(is_string($gh2_url) && trim($gh2_url) !== "" && 
				is_string($gh2_url) && trim($gh2_url) !== "")
			{
				$gh .= ", <a class='black-link' href='$gh2_url'>$gh2_name</a>";
			}
			$gh .= "<br>";
		}
		
		return $gh;
	}
	function getDiscordHtml(): string {
		
		$add_discord = CONTACTPAGE_MOBILE_ADD_DISCORD;
		$discord_handle = CONTACTPAGE_MOBILE_DISCORD;
		$discord = !$add_discord ? "" : "discord: " . $discord_handle;
		
		return $discord;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donate | Listiary</title>
	<!-- https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css -->
    <link rel="stylesheet" href="/css/bootstrap.min.css"> 
	<link rel="stylesheet" href="/css/donate.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="wrapper" style="margin-left: auto; margin-right: auto; width: 50%; min-width: 400px;">
        <br /><br />
		<h2 style="text-align: left; font-weight: bold;">Donation</h2>
		<p style="text-align: left;">
			Listiary is free and always will be. Free from ads and corporate interests, free from government influences and censorship, just like all knowledge should be. However, developing and running Listiary is not free for us, and we would've been able to go only so far by working on our spare time and paying out of our own pockets. Luckily, Listiary is sustained by donations from users like you. We ask, if you found Listiary invaluable or useful, to consider donating money, to sustain the project and develop new features.
		</p>
		<div class="flex-gap"></div>
        <form action="" method="post">
			<table id="skeleton" style="width: 100%;">
			<tbody>
				<tr>
					<td colspan="5" style="padding-bottom:10px">
					<input type="button" style="padding-bottom: 10px;" name="username" class="form-control" value="One Time">
					</td>
					<td colspan="5" style="padding-bottom:10px">
					<input type="button" style="padding-bottom: 10px;" name="username" class="form-control" value="Monthly">
					</td>
				</tr>
				<tr>
					<td colspan="3">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="5 USD">
					</td>
					<td colspan="3">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="10 USD">
					</td>
					<td colspan="3">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="20 USD">
					</td>
				</tr>
				<tr>
					<td colspan="3" style="padding-bottom:15px">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="50 USD">
					</td>
					<td colspan="3" style="padding-bottom:15px">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="100 USD">
					</td>
					<td colspan="3" style="padding-bottom:15px">
					<input type="button" onclick="window.location.href='https://opencollective.com/listiary'" name="username" class="form-control" value="200 USD">
					</td>
				</tr>
				<tr>
				<td colspan="10">
					<input type="text" name="xxx" class="form-control" value="5"  style="margin-bottom:10px">
					<input type="submit" style="width: 100%;" class="btn btn-primary" value="Send">
				</td>
				</tr>
			</tbody>
			</table>
        </form>
		<div class="flex-gap"></div>
		<p class="lastp">
			<?php echo $home; ?>
			<?php echo $gh; ?>
			<?php echo $email; ?><br>
			<?php echo $discord; ?>
		</p>
    </div>    
</body>
</html>