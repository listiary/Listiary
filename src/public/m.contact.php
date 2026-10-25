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
    <title>Contact | Listiary</title>
	<!-- https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
	<link rel="stylesheet" href="/css/m.contact.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="wrapper">
        <br /><br />
		<h2>Get in Touch</h2>
		<p>
			Explore our <a class="black-link" target="_blank" href="<?php echo $faq_url; ?>">FAQ</a>, 
			<a class="black-link" target="_blank" href="<?php echo $doc_url; ?>">read documentation</a><br>
			or contact us.
		</p>
		<div class="flex-gap"></div>
        <form action="" method="post">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Email address">
            </div>    
            <div class="form-group">
				<textarea name="message" class="form-control" placeholder="Message" rows="8"></textarea>
            </div>
            <div class="form-group centered">
                <input type="submit" style="width: 100%;" class="btn btn-primary" value="Send">
            </div>
        </form>
		<br/><br/>
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