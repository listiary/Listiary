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
	$users = getUserDataArrays($dummyUsers, $realUsers);	
	//var_dump($users); die();
	
	// Get users grid
	const USE_HEADINGS = false;
	$html = getUsersHtml($users, USE_HEADINGS, 150);
	
	//search if the parameter is set
	$search = getSearchParam();
	if ($search !== "") 
	{
		echo '<script>document.addEventListener("DOMContentLoaded", () => {search();});</script>';
	}

	
	
	// Get the search param from URL GET parameter 'search'
	function getSearchParam(): string {

		if (!isset($_GET['search'])) {
			return "";
		}

		$search = trim($_GET['search']);

		if (!preg_match('/^[a-z0-9]+$/i', $search)) {
			return "";
		}

		return $search;
	}

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

	// Get the users structures that will be used to populate the page
	function getUserDataArrays(array $dummyUsers, array $realUsers): array {
		
		$users = [];
		foreach ($dummyUsers as $name)
		{
			$r = random_int(1, 100);
			$color = 'color:#ccc;';
			if ($r <= 3) $color = 'color:blue;';
			elseif ($r <= 8) $color = 'color:red;';      	// 3 + 5
			elseif ($r <= 16) $color = 'color:green;';		// 8 + 8
			elseif ($r <= 86) $color = 'color:black;';		// 16 + 70

			$star = random_int(1, 100) <= 10;
			$star = false;
			$users[] = 
			[
				'username' => $name,
				'star' => $star,
				'id' => 0,
				'isreal' => false,
				'style' => $color . ($star ? 'margin-left:-19px;' : '')
			];
		}
		foreach ($realUsers as $user)
		{
			$star = $user['is_premium'];
			$star = false;
			$users[] = 
			[
				'username' => $user['username'],
				'star' => $star,
				'id' => $user['id'],
				'isreal' => true,
				'style' => 'color:blue;' . ($star ? 'margin-left:-19px;' : '')
			];
		}

		// Sort alphabetically by username
		usort($users, function($a, $b)
		{
			return strcmp($a['username'], $b['username']);
		});
		
		return $users;
	}

	// Get html for users
	function getUsersHtml(array $users, bool $useHeadings, int $useHeadingsOver): string {
		
		$html = '';
		if($useHeadings === false || count($users) < $useHeadingsOver)
		{
			foreach ($users as $user)
			{
				$html .= '<div class="user-item"><a style="' . $user['style'] . '" href="';
				if($user['isreal']) $html .= 'https://development.listiary.org/m.session/m.user.php?id=' . $user['id'] . '">';
				else $html .= 'javascript:void(0);">';
				$html .= ($user['star'] ? '✦ ' : '') . htmlspecialchars($user['username']) . '</a></div>';
			}
		}
		else
		{
			// Group users by letter
			$groups = [];
			foreach ($users as $user) 
			{
				$letter = strtoupper(mb_substr($user['username'], 0, 1));
				$groups[$letter][] = $user;
			}
			ksort($groups);

			// Merge small groups
			$finalGroups = [];
			$currentGroup = [];
			$startLetter = '';
			$endLetter = '';
			$count = 0;
			foreach ($groups as $letter => $letterUsers) {

				if ($startLetter === '') {
					$startLetter = $letter;
				}

				$endLetter = $letter;
				$currentGroup = array_merge($currentGroup, $letterUsers);
				$count += count($letterUsers);

				if ($count >= 20) {
					$finalGroups[] = [
						'start' => $startLetter,
						'end' => $endLetter,
						'users' => $currentGroup
					];

					$currentGroup = [];
					$startLetter = '';
					$endLetter = '';
					$count = 0;
				}
			}
			
			// Catch leftover users
			if (!empty($currentGroup)) {
				$finalGroups[] = [
					'start' => $startLetter,
					'end' => $endLetter,
					'users' => $currentGroup
				];
			}
			
			// Build the HTML
			foreach ($finalGroups as $group) 
			{
				$title = $group['start'];
				if ($group['start'] !== $group['end'])
				{
					$title .= '-' . $group['end'];
				}
				$html .= '<div class="user-letter">' . $title . '</div>';
				foreach ($group['users'] as $user) 
				{
					$html .= '<div class="user-item"><a style="' . $user['style'] . '" href="';
					if ($user['isreal']) $html .= 'https://development.listiary.org/m.session/m.user.php?id=' . $user['id'] . '">';
					else $html .= 'javascript:void(0);">';
					$html .= ($user['star'] ? '✦ ' : '') . htmlspecialchars($user['username']) . '</a></div>';
				}
			}
		}
		return $html;
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
					
						<!-- <div class="logo" style="display: flex; justify-content: center;">
							<a href="../m.index.php"><img src="img/listiary-logo-small.png" alt="Listiary Logo"></a>
						</div> -->
						<div class="search-container">
							<input id="searchInput" type="text" placeholder="Start typing ..." value="<?= htmlspecialchars($search) ?>">
							<button onclick="searchClick()"><svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="20" y1="20" x2="16.5" y2="16.5"></line></svg></button>
						</div>
						<div class="users-grid">
							<?php echo $html; ?>
						</div>
						<br><br><br>
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