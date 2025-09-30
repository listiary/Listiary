<?php

	//get URL parameters
	$article = ""; 
	$domain = "";
	getUrlParameters();


	// Read URL parameters into our global variables
	function getUrlParameters() {

		global $article, $domain;

			//get parameters
			if(isset($_GET['article']) == false) die("'article' parameter of the url is not set");
			$article = $_GET['article'];
				//echo "Got article parameter - '" . $article . "'<br />";
			
			if(isset($_GET['domain']) == false) die("'domain' parameter of the url is not set");
			$domain = $_GET['domain'];
				//echo "Got domain parameter - '" . $domain . "'<br />";

			//$curPath = $_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
			//$newfileUrl = "_newfile.php?article=" . $article;
	}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="description" content="Created by Describe Compiler v0.9.2">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="author" content="Demon of reason">
	<link rel="stylesheet" type="text/css" href="css/styles.css" />
	<title>Proto World - Edit Article</title>
</head>
<body style="height:100%;">

	<table 
	id="skeleton" 
	style="position: absolute; width:100%; height:100%; background-color:gray; border: 0px solid black; border-collapse: collapse; table-layout: fixed">
		<tbody>
			<tr>
				<td 
				colspan="10" 
				style="background-color:white; width:100%; vertical-align: top; padding:30px; padding-top: 0px;" 
				id="LargeContainer">
				
					<form id="editForm" style='width:100%; height:100%;'>
						<input id="newArticleName" type='text' style='color: black; border: 1; background: none; box-shadow: none; border-radius: 0px; width: 100%;' value='<?php echo $article; ?>'>
						<textarea id="editArea" style='width:100%; min-height:450px; resize: none;' name='content' id='editorBox'></textarea>
						<div style='width:100%; min-height:60px; background-color: #ccccff; padding: 10px; padding-right: 0px; border: 2px white;'>
							<div style='margin-bottom: 10px;'>Edit summary (Briefly describe your changes)</div>
							<input id="summaryBox" type='text' style='width:98%; margin-bottom: 10px;' id='editSummary' name='summary'>
							<input type='button' style='background-color: #0000ff; border: 1; background: none; box-shadow: none; border-radius: 0px;' value='Publish changes' onclick='submitForm(getNewFileUrl(<?php echo '"' . $domain . '"' ?>));'>
							<input type='button' style='color: red; border: 1; background: none; box-shadow: none; border-radius: 0px; margin-left: 10px;' value='Cancel' onclick='hideEditor("../index.php");'>
					</form>
				
				</td>
			</tr>
		</tbody>
	</table>
	<script src="js/scripts.js"></script>
</body>
</html>