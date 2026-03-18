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
	$dummyUsers = getDummyAccountNames(76);
	$realUsers = getRealUsers($link);




	// Get random real-sounding account names for testing
	function getDummyAccountNames(int $count): array {

		$count = min($count, 200);
		
		$names = ["ArchiveBuilder", "CitationHunter", "HistoryMapper", "SourceVerifier", "FootnoteForge", "PageGardener", "TemplateSmith", "SectionRefactor", "EditNavigator", "KnowledgeCartographer", "ArchiveNomad", "FactTrail", "DataChronicler", "ReferenceKeeper", "PageWeaver", "RecordCurator", "CatalogPilot", "InfoSurveyor", "ManuscriptMiner", "ContextBuilder", "AtlasEditor", "ChronicleWriter", "DocumentSeeker", "FootnoteCartel", "AnnotationPilot", "PageArchitect", "ArticleScout", "EditArchivist", "ContentRanger", "SourcePathfinder", "KnowledgeHarbor", "CitationSailor", "ArchiveCompass", "DataScribe", "ReferenceNavigator", "ArticleGardener", "CatalogExplorer", "EditMechanic", "RecordArchitect", "FootnoteMapper", "SourceMiner", "KnowledgeSmith", "PageSurveyor", "ChronicleBuilder", "ContextMiner", "ArchiveTactician", "FactAssembler", "ReferencePilot", "SourceAssembler", "ContentArchivist", "QuietLibrarian", "NightOwlReader", "WanderingFootnote", "BlueInkWriter", "PaperTrailFox", "LanternScholar", "RustyCompass", "EchoArchivist", "SilverNotebook", "CuriousAtlas", "WanderingEditor", "HiddenLibrary", "OldMapSeeker", "AmberNotebook", "SilentCartographer", "WanderingQuill", "MarbleNotebook", "CuriousChronicler", "UrbanHistorian", "PaperVoyager", "LanternArchivist", "BronzeAtlas", "QuietSurveyor", "StoryNavigator", "WanderingScribe", "FoggyArchive", "IvoryNotebook", "AtlasDreamer", "EchoNavigator", "CedarArchivist", "CuriousVoyager", "DistantFootnote", "OpenNotebook", "MarbleHistorian", "LanternVoyager", "NorthboundReader", "ArchiveDreamer", "IronNotebook", "PaperArchivist", "SilentNavigator", "AtlasFootnote", "DriftHistorian", "ArchivePilgrim", "QuietChronicler", "FoggyNavigator", "PaperSurveyor", "WanderingAtlas", "CedarNotebook", "HiddenCartographer", "StoryArchivist", "alex_m92", "lina_edit87", "mkovacs73", "historyfan21", "daniel_k_dev", "nika404", "reader_steve", "ivanwiki88", "rafael_works", "mike_writer77", "anna_source92", "pavel_reader", "sara_archive", "tom_data83", "lena_maps", "alex_context", "nina_page91", "viktor_docs", "matej_archive77", "dan_reader24", "luca_builder", "kira_editor66", "geo_source88", "ivan_notes42", "alex_record", "nina_mapper", "mark_edit55", "lena_page21", "tom_source19", "sara_archive91", "nikolay_docs", "martin_builder", "lena_context", "alex_refactor", "tanya_reader", "boris_archive33", "pavel_maps88", "dan_editor74", "nina_record17", "alex_footnote", "sara_chronicle", "viktor_context55", "matej_docs", "lena_builder82", "ivan_archive19", "mark_mapper", "tom_refactor", "nina_article", "alex_citation"];

		shuffle($names);
		return array_slice($names, 0, $count);
	}

	// Get accounts from DB
	function getRealUsers(mysqli $link): array {

		$sql = "SELECT id, username FROM accounts";
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
	<script src="js/m.usersindex.js"></script>
</head>
<body>
	<div class="logo">
		<img src="img/listiary-logo.png" alt="Listiary Logo">
	</div>
	<div class="search-container">
		<input id="searchInput" type="text" placeholder="Start typing a username ...">
		<button onclick="search()">Search</button>
	</div>
	<div class="users-grid">
	<?php foreach ($realUsers as $user): ?>
		<div class="user-item">
			<a href="https://development.listiary.org/session/m.user.php?id=<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?></a>
		</div>
	<?php endforeach; ?>
	<?php foreach ($dummyUsers as $user): ?>
		<div class="user-item">
			<a href="javascript: void(0);"><?= htmlspecialchars($user) ?></a>
		</div>
	<?php endforeach; ?>
	</div>
	<script>
		// Optional: search while typing
		document.getElementById("searchInput").addEventListener("keyup", search);
	</script>
</body>
</html>