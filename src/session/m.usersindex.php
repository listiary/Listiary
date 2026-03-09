<?php

	// Presets
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	require_once __DIR__ . "/php/_config.php";
	require_once __DIR__ . "/php/_sessionlib.php";
	set_exception_handler('catchEx');
	
	// More Presets
	//startSecureSession();
	$link = connectDb();
	
	// Get users
	$dummyUsers = getDummyAccountNames(74);
	$realUsers = getRealUsers($link);
	$users = [];
	foreach ($dummyUsers as $name)
	{
		$star = random_int(1, 100) <= 18;
		$users[] = 
		[
			'username' => $name,
			'star' => $star,
			'id' => 0,
			'style' => 'color:#ccc;' . ($star ? 'margin-left:-19px;' : '')
		];
	}
	foreach ($realUsers as $user)
	{
		$star = $user['is_premium'];
		$users[] = 
		[
			'username' => $user['username'],
			'star' => $star,
			'id' => $user['id'],
			'style' => 'color:blue;' . ($star ? 'margin-left:-19px;' : '')
		];
	}

	// Sort alphabetically by username
	usort($users, function($a, $b)
	{
		return strcmp($a['username'], $b['username']);
	});
	
	//var_dump($users); die();
	
	
	
	

	// Get random real-sounding account names for testing
	function getDummyAccountNames(int $count): array {

		$count = min($count, 200);
		
		$names = ["ArchiveBuilder", "CitationHunter", "HistoryMapper", "SourceVerifier", "FootnoteForge", "PageGardener", "TemplateSmith", "SectionRefactor", "EditNavigator", "KnowledgeCat", "ArchiveNomad", "FactTrail", "DataChronicler", "ReferenceKeeper", "PageWeaver", "RecordCurator", "CatalogPilot", "InfoSurveyor", "ManuscriptMiner", "ContextBuilder", "AtlasEditor", "ChronicleWriter", "DocumentSeeker", "FootnoteCartel", "AnnotationPilot", "PageArchitect", "ArticleScout", "EditArchivist", "ContentRanger", "SourcePathfinder", "KnowledgeHarbor", "CitationSailor", "ArchiveCompass", "DataScribe", "ReferenceNavigator", "ArticleGardener", "CatalogExplorer", "EditMechanic", "RecordArchitect", "FootnoteMapper", "SourceMiner", "KnowledgeSmith", "PageSurveyor", "ChronicleBuilder", "ContextMiner", "ArchiveTactician", "FactAssembler", "ReferencePilot", "SourceAssembler", "ContentArchivist", "QuietLibrarian", "NightOwlReader", "WanderingFootnote", "BlueInkWriter", "PaperTrailFox", "LanternScholar", "RustyCompass", "EchoArchivist", "SilverNotebook", "CuriousAtlas", "WanderingEditor", "HiddenLibrary", "OldMapSeeker", "AmberNotebook", "SilentCartographer", "WanderingQuill", "MarbleNotebook", "CuriousChronicler", "UrbanHistorian", "PaperVoyager", "LanternArchivist", "BronzeAtlas", "QuietSurveyor", "StoryNavigator", "WanderingScribe", "FoggyArchive", "IvoryNotebook", "AtlasDreamer", "EchoNavigator", "CedarArchivist", "CuriousVoyager", "DistantFootnote", "OpenNotebook", "MarbleHistorian", "LanternVoyager", "NorthboundReader", "ArchiveDreamer", "IronNotebook", "PaperArchivist", "SilentNavigator", "AtlasFootnote", "DriftHistorian", "ArchivePilgrim", "QuietChronicler", "FoggyNavigator", "PaperSurveyor", "WanderingAtlas", "CedarNotebook", "HiddenCartographer", "StoryArchivist", "alex_m92", "lina_edit87", "mkovacs73", "historyfan21", "daniel_k_dev", "nika404", "reader_steve", "ivanwiki88", "rafael_works", "mike_writer77", "anna_source92", "pavel_reader", "sara_archive", "tom_data83", "lena_maps", "alex_context", "nina_page91", "viktor_docs", "matej_archive77", "dan_reader24", "luca_builder", "kira_editor66", "geo_source88", "ivan_notes42", "alex_record", "nina_mapper", "mark_edit55", "lena_page21", "tom_source19", "sara_archive91", "nikolay_docs", "martin_builder", "lena_context", "alex_refactor", "tanya_reader", "boris_archive33", "pavel_maps88", "dan_editor74", "nina_record17", "alex_footnote", "sara_chronicle", "viktor_context55", "matej_docs", "lena_builder82", "ivan_archive19", "mark_mapper", "tom_refactor", "nina_article", "alex_citation"];

		shuffle($names);
		return array_slice($names, 0, $count);
	}

	// Get accounts from DB
	function getRealUsers(mysqli $link): array {

		$sql = "SELECT id, username, is_premium FROM accounts";
		$stmt = mysqli_prepare($link, $sql);

		if (!$stmt) 
		{
			throw new RuntimeException('Database prepare failed: ' . mysqli_error($link));
		}

		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		$accounts = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$accounts[] = $row;
		}

		mysqli_stmt_close($stmt);

		return $accounts;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users | Listiary</title>
	<link rel="stylesheet" href="css/m.usersindex.css">
	<link rel="stylesheet" href="css/m.navigatedpage.css">
	<script src="js/m.usersindex.js"></script>
	<script type="text/javascript" src="js/scripts.dom-manipulate.js"></script>
</head>
	<body>

		<!-- The hamburger menu -->
		<div id="sidenav" >

			<!-- This is the menu 'X' button -->
			<a href="javascript: closeNav();" class="closebtn"
			style="position: absolute; top: 0; right: 0; font-size: 36px; margin-right: 10px;
			padding: 8px; text-decoration: none; color: #818181; display: block; transition: 0.3s;">
				<svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' style='margin-top:14px;' viewBox='0 0 25 25'>
					<path d='M0,0 L16,16' stroke='#818181' stroke-width='2' fill='none'/>
					<path d='M0,16 L16,0' stroke='#818181' stroke-width='2' fill='none'/>
				</svg>
			</a>
		

			<!-- MenuMain -->
			<a href="../m.index.php" class="MenuMain">Wiki Index</a>
			<a href="m.usersindex.php" class="MenuMain">Users Index</a>
			<a href="#" target="_blank" class="MenuMain">About</a>
			<a href="https://forums.listiary.org/" target="_blank" class="MenuMain">Forums</a>
			<a href="../contact/mobile/contact.php" class="MenuMain">Contact us</a>
			<a href="../contact/mobile/reportbug.php" class="MenuMain">Report a bug</a>
			<a href="../contact/mobile/donate.php" class="MenuMain">Donate</a>
			<br class="MenuMain">
			<hr id="MenuMainBottomAnchor" style="margin: 22px;" class="MenuMain"/>
			<a href="../docs/m.terms_of_service.php" target="_blank" class="MenuMain">Terms of Use</a>
			<a href="../docs/m.privacy_policy.php" target="_blank" class="MenuMain">Privacy Policy</a>
			<a href="https://library.listiary.org/" target="_blank" class="MenuMain">Describe Library</a>
			<a href="https://documentation.listiary.org/" target="_blank" class="MenuMain">Describe Docs</a>
			<a href="https://github.com/viktorchernev/DescribeCompiler" target="_blank" class="MenuMain">Describe Repo</a>
		</div>

		<!-- Triggers -->
		<span id="sidenav-trigger" onclick="openNav();">&#9776;</span>


		<!-- Main page skeleton -->
		<table id="skeleton">
			<tbody>
				<tr>
					<td colspan="10" id="LargeTopContainer" 
						style="background: linear-gradient(rgb(153, 179, 255) 0%, rgb(153, 179, 255) calc(60% + 1px), transparent calc(60% + 2px));">
						<div style="left: 0; bottom: 0; width: 100%;">
							<div style="font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;">
								<div style="margin-right:35px;">
									<a class="breadcrumbmain" style="display: inline;" href="m.usersindex.php">Users Index</a>
								</div>
								<hr>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="10" id="LargeContainer" >
					
						<div class="logo" style="display: flex; justify-content: center;">
							<img src="img/listiary-logo.png" alt="Listiary Logo">
						</div>
						<div class="search-container">
							<input id="searchInput" type="text" placeholder="Start typing a username ...">
							<button onclick="search()">Search</button>
						</div>
						<div class="users-grid">
							<?php foreach ($users as $user): ?>
								<div class="user-item">
									<a style="<?= $user['style'] ?>" 
										href="https://development.listiary.org/session/m.user.php?id=<?= $user['id'] ?>">
										<?= ($user['star'] ? '✦ ' : '') . htmlspecialchars($user['username']) ?>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
						<script>
							// Optional: search while typing
							document.getElementById("searchInput").addEventListener("keyup", search);
						</script>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>