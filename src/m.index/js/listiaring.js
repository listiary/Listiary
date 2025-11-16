function addHighlightsSubmenu() {

	//highlight sub-menu
	//https://htmlcolorcodes.com/color-names/

	//RemoveAllHighlighting = new MassRemoveHighlightTool("RemoveAllHighlighting", "MenuHighlighters");
	RemoveAllHighlighting = new MassRemoveHighlightTool("RemoveAllHighlighting", "Remove All",
		"m.index/img/x-circle-bold-gray.png", "m.index/img/x-circle-bold.png",
		"MenuHighlighters_RemoveAllHighlight", "MenuHighlighters");

	//RemoveHighlight = new RemoveHighlightTool("RemoveHighlight", "MenuHighlighters");
	RemoveHighlight = new RemoveHighlightTool("RemoveHighlight", "Erase",
		"m.index/img/pencil-simple-slash-bold-gray.png", "m.index/img/pencil-simple-slash-bold.png",
		"MenuHighlighters_EraseHighlight", "MenuHighlighters");

	HighlightBlue = new HighlightTool("LightSteelBlue", "HighlightBlue", "Highlight Blue",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightBlue", "MenuHighlighters");

	HighlightGreen = new HighlightTool("Chartreuse", "HighlightGreen", "Highlight Green",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightGreen", "MenuHighlighters");

	HighlightYellow = new HighlightTool("Yellow", "HighlightYellow", "Highlight Yellow",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightYellow", "MenuHighlighters");

	HighlightBlack = new HighlightTool("Black", "HighlightBlack", "Black",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightBlack", "MenuHighlighters");

	HighlightSilver = new HighlightTool("Silver", "HighlightSilver", "Silver",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightSilver", "MenuHighlighters");

	HighlightGold = new HighlightTool("Gold", "HighlightGold", "Gold",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightGold", "MenuHighlighters");

	HighlightHotOrange = new HighlightTool("Orange", "HighlightHotOrange", "Orange",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightOrange", "MenuHighlighters");

	HighlightCoral = new HighlightTool("Coral", "HighlightCoral", "Coral",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightCoral", "MenuHighlighters");

	HighlightHotPink = new HighlightTool("HotPink", "HighlightHotPink", "Hot Pink",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightHotPink", "MenuHighlighters");

	HighlightMediumPurple = new HighlightTool("MediumPurple", "HighlightMediumPurple", "Medium Purple",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightMediumPurple", "MenuHighlighters");

	HighlightDodgerBlue = new HighlightTool("DodgerBlue", "HighlightDodgerBlue", "Dodger Blue",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightDodgerBlue", "MenuHighlighters");

	HighlightTurquoise = new HighlightTool("Turquoise", "HighlightTurquoise", "Turquoise",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightTurquoise", "MenuHighlighters");

	HighlightSpringGreen = new HighlightTool("SpringGreen", "HighlightSpringGreen", "Spring Green",
		"m.index/img/pencil-simple-line-bold-gray.png", "m.index/img/pencil-simple-line-bold.png",
		"MenuHighlighters_HighlightSpringGreen", "MenuHighlighters");

	RemoveAllHighlighting.Init(ToolManager, "highlighters");
	RemoveHighlight.Init(ToolManager, "highlighters");
	HighlightBlue.Init(ToolManager, "highlighters");
	HighlightGreen.Init(ToolManager, "highlighters");
	HighlightYellow.Init(ToolManager, "highlighters");
	HighlightBlack.Init(ToolManager, "highlighters");
	HighlightSilver.Init(ToolManager, "highlighters");
	HighlightGold.Init(ToolManager, "highlighters");
	HighlightHotOrange.Init(ToolManager, "highlighters");
	HighlightCoral.Init(ToolManager, "highlighters");
	HighlightHotPink.Init(ToolManager, "highlighters");
	HighlightMediumPurple.Init(ToolManager, "highlighters");
	HighlightDodgerBlue.Init(ToolManager, "highlighters");
	HighlightTurquoise.Init(ToolManager, "highlighters");
	HighlightSpringGreen.Init(ToolManager, "highlighters");

	RemoveAllHighlighting.AddAfter("MenuHighlightersTopAnchor");
	RemoveHighlight.AddAfter("MenuHighlightersTopAnchor");
	HighlightBlue.AddAfter("MenuHighlightersTopAnchor");
	HighlightGreen.AddAfter("MenuHighlightersTopAnchor");
	HighlightYellow.AddAfter("MenuHighlightersTopAnchor");
	HighlightBlack.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightSilver.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightGold.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightHotOrange.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightCoral.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightHotPink.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightMediumPurple.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightDodgerBlue.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightTurquoise.AddAfter("MenuHighlightersMiddleAnchor");
	HighlightSpringGreen.AddAfter("MenuHighlightersMiddleAnchor");
}
function addSortersSubmenu() {

	//SortArtist = new SortByGenericTool("artist", "Artist", "Artist", "SortArtist");
	SortArtist = new ListiaryToolDummy("SortArtist", "Sort by Artist",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortArtist", "MenuSorters");

	//SortArtistZa = new SortZaGenericTool("artist", "Artist", "Artist", "SortArtistZa");
	SortArtistZa = new ListiaryToolDummy("SortArtistZa", "Sort by Artist Z-A",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortArtistZa", "MenuSorters");

	//SortArtistAz = new SortAzGenericTool("artist", "Artist", "Artist", "SortArtistAz");
	SortArtistAz = new ListiaryToolDummy("SortArtistAz", "Sort by Artist Z-A",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortArtistAz", "MenuSorters");

	//SortTitleZa = new SortZaGenericTool("title", "Title", "Title", "SortTitleZa");
	SortTitleZa = new ListiaryToolDummy("SortTitleZa", "Sort by Title Z-A",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortTitleZa", "MenuSorters");

	//SortTitleAz = new SortAzGenericTool("title", "Title", "Title", "SortTitleAz");
	SortTitleAz = new ListiaryToolDummy("SortTitleAz", "Sort by Title A-Z",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortTitleAz", "MenuSorters");

	//SortZA = new SortZaTool("SortZA");
	SortZA = new SortZaTool("SortZA", "Sort Z-A",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortAz", "MenuSorters");

	//SortAZ = new SortAzTool("SortAZ");
	SortAZ = new SortAzTool("SortAZ", "Sort A-Z",
		"m.index/img/sort-ascending-bold-gray.png", "m.index/img/sort-ascending-bold.png", "MenuSorters_SortAz", "MenuSorters");

	//HighlightBlue.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");
	//HighlightGreen.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");
	//HighlightYellow.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");

	//dummies don't need Init
	//SortArtist.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	//SortArtistZa.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	//SortArtistAz.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	//SortTitleZa.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	//SortTitleAz.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	//SortZA.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortZA.Init(ToolManager, "sorters");
	//SortAZ.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortAZ.Init(ToolManager, "sorters");

	SortArtist.AddAfter("MenuSortersMiddleAnchor");
	SortArtistZa.AddAfter("MenuSortersMiddleAnchor");
	SortArtistAz.AddAfter("MenuSortersMiddleAnchor");
	SortTitleZa.AddAfter("MenuSortersMiddleAnchor");
	SortTitleAz.AddAfter("MenuSortersMiddleAnchor");
	SortZA.AddAfter("MenuSortersTopAnchor");
	SortAZ.AddAfter("MenuSortersTopAnchor");
}
function addCopiersSubmenu() {

	CopyDescribeEntry = new CopyDescribeEntryTool("CopyDescribeEntry", "Entries (*.ds)",
		"m.index/img/copy-bold-gray.png", "m.index/img/copy-bold.png", "MenuCopiers_CopyDsEntries", "MenuCopiers");

	CopyEntry = new CopyFirstLinkTool("CopyFirstLink", "Copy Entry",
		"m.index/img/copy-bold-gray.png", "m.index/img/copy-bold.png", "MenuCopiers_CopyEntries", "MenuCopiers");

	//CopyFirstLink = new CopyFirstLinkTool("CopyFirstLink");
	CopyFirstLink = new CopyFirstLinkTool("CopyFirstLink", "Copy First Link",
		"m.index/img/copy-bold-gray.png", "m.index/img/copy-bold.png", "MenuCopiers_CopyFLink", "MenuCopiers");

	//CopyText = new CopyTextTool("CopyText");
	CopyText = new CopyTextTool("CopyText", "Copy Text",
		"m.index/img/copy-bold-gray.png", "m.index/img/copy-bold.png", "MenuCopiers_CopyText", "MenuCopiers");

	//SortAZ.AddNewTriggerAfter("MenuCopiersBottomAnchor", "MenuCopiers");
	//HighlightBlue.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");
	//HighlightGreen.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");
	//HighlightYellow.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");

	CopyDescribeEntry.Init(ToolManager, "copiers");
	//CopyDescribeEntry.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyEntry.Init(ToolManager, "copiers");
	//CopyFirstLink.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyFirstLink.Init(ToolManager, "copiers");
	//CopyText.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyText.Init(ToolManager, "copiers");

	CopyDescribeEntry.AddAfter("MenuCopiersTopAnchor");
	CopyEntry.AddAfter("MenuCopiersTopAnchor");
	CopyFirstLink.AddAfter("MenuCopiersTopAnchor");
	CopyText.AddAfter("MenuCopiersTopAnchor");
}
function addFiltersSubmenu() {

	FilterLinkfulDummy = new ListiaryToolDummy("FilterLinkfulDummy", "Linkful",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterLinkfulDummy", "MenuFilters");

	FilterLinklessDummy = new ListiaryToolDummy("FilterLinklessDummy", "Linkless",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterLinklessDummy", "MenuFilters");

	FilterVisitedDummy = new ListiaryToolDummy("FilterVisitedDummy", "Visited",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterVisitedDummy", "MenuFilters");

	FilterNotVisitedDummy = new ListiaryToolDummy("FilterNotVisitedDummy", "Not-visited",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterNotVisitedDummy", "MenuFilters");

	FilterRVisitedDummy = new ListiaryToolDummy("FilterRVisitedDummy", "rem Visited",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterRVisitedDummy", "MenuFilters");

	FilterRLinkfulDummy = new ListiaryToolDummy("FilterRLinkfulDummy", "rem Linkful",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterRLinkfulDummy", "MenuFilters");

	FilterRLinklessDummy = new ListiaryToolDummy("FilterRLinklessDummy", "rem Linkless",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterRLinklessDummy", "MenuFilters");

	FilterRNotVisitedDummy = new ListiaryToolDummy("FilterRNotVisitedDummy", "rem Not-visited",
		"m.index/img/funnel-bold-gray.png", "m.index/img/funnel-bold.png",
		"MenuFilters_FilterRNotVisitedDummy", "MenuFilters");

	FilterRNotVisitedDummy.AddAfter("MenuFiltersTopAnchor");
	FilterRLinklessDummy.AddAfter("MenuFiltersTopAnchor");
	FilterRLinkfulDummy.AddAfter("MenuFiltersTopAnchor");
	FilterRVisitedDummy.AddAfter("MenuFiltersTopAnchor");
	FilterNotVisitedDummy.AddAfter("MenuFiltersTopAnchor");
	FilterVisitedDummy.AddAfter("MenuFiltersTopAnchor");
	FilterLinklessDummy.AddAfter("MenuFiltersTopAnchor");
	FilterLinkfulDummy.AddAfter("MenuFiltersTopAnchor");
}
function addViewersSubmenu() {

	ViewDummyD = new ListiaryToolDummy("ViewDummyD", "view song genre",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewSongGenreDummy", "MenuViewers");

	ViewDummyC = new ListiaryToolDummy("ViewDummyC", "view song year",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewSongYearDummy", "MenuViewers");

	ViewDummyB = new ListiaryToolDummy("ViewDummyB", "view song date",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewSongDateDummy", "MenuViewers");

	ViewDummyA = new ListiaryToolDummy("ViewDummyA", "view artists",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewArtistsDummy", "MenuViewers");

	ViewAsListDummy = new ListiaryToolDummy("ViewAsListDummy", "view as list",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewAsListDummy", "MenuViewers");

	ViewElementsDummy = new ListiaryToolDummy("ViewElementsDummy", "view elements",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewElementsDummy", "MenuViewers");

	ViewLinksDummy = new ListiaryToolDummy("ViewLinksDummy", "view links",
		"m.index/img/eye-bold-gray.png", "m.index/img/eye-bold.png",
		"MenuViewers_ViewLinksDummy", "MenuViewers");

	ViewDummyD.AddAfter("MenuViewersMiddleAnchor");
	ViewDummyC.AddAfter("MenuViewersMiddleAnchor");
	ViewDummyB.AddAfter("MenuViewersMiddleAnchor");
	ViewDummyA.AddAfter("MenuViewersMiddleAnchor");
	ViewAsListDummy.AddAfter("MenuViewersTopAnchor");
	ViewElementsDummy.AddAfter("MenuViewersTopAnchor");
	ViewLinksDummy.AddAfter("MenuViewersTopAnchor");
}
function addPickersSubmenu() {

	PickRandomY = new ListiaryToolUnselectableDummy("PickRandomY", "clear picks",
		"m.index/img/x-circle-bold-gray.png", "m.index/img/x-circle-bold.png", "MenuPickers_ClearPicks", "MenuPickers");

	PickRandomX = new ListiaryToolDummy("PickRandomX", "pick many",
		"m.index/img/check-circle-bold-gray.png", "m.index/img/check-circle-bold.png", "MenuPickers_PickMany", "MenuPickers");

	PickRandomD = new PickRandomTool("PickRandomD", "Favorite",
		"m.index/img/arrow-fat-down-bold-gray.png", "m.index/img/arrow-fat-down-bold.png", "MenuPickers_PickFavorite", "MenuPickers");

	PickRandomC = new PickRandomTool("PickRandomC", "Visited",
		"m.index/img/arrow-fat-down-bold-gray.png", "m.index/img/arrow-fat-down-bold.png", "MenuPickers_PickVisited", "MenuPickers");

	PickRandomB = new PickRandomTool("PickRandomB", "Not Visited",
		"m.index/img/arrow-fat-down-bold-gray.png", "m.index/img/arrow-fat-down-bold.png", "MenuPickers_PickNotVisited", "MenuPickers");

	PickRandom = new PickRandomTool("PickRandom", "Pick Random",
		"m.index/img/arrow-fat-down-bold-gray.png", "m.index/img/arrow-fat-down-bold.png", "MenuPickers_PickRandom", "MenuPickers");

	PickRandomY.AddAfter("MenuPickersTopAnchor");
	PickRandomX.AddAfter("MenuPickersTopAnchor");

	_addAfter("MenuPickersTopAnchor", _getBr("MenuPickers"));
	_addAfter("MenuPickersTopAnchor", _getBr("MenuPickers"));

	PickRandom.Init(ToolManager, "pickers");
	PickRandomB.Init(ToolManager, "pickers");
	PickRandomC.Init(ToolManager, "pickers");
	PickRandomD.Init(ToolManager, "pickers");

	PickRandomD.AddAfter("MenuPickersTopAnchor");
	PickRandomC.AddAfter("MenuPickersTopAnchor");
	PickRandomB.AddAfter("MenuPickersTopAnchor");
	PickRandom.AddAfter("MenuPickersTopAnchor");
}
function addPluginsSubmenu() {

	AppTutor = new ListiaryToolDummy("AppTutor", "App Tutorial",
		"m.index/img/graduation-cap-bold.png", "m.index/img/graduation-cap-bold.png",
		"MenuPlugins_AppTutor", "MenuPlugins");

	StatViewer = new ListiaryToolDummy("StatViewer", "Stat Viewer",
		"m.index/img/perspective-bold.png", "m.index/img/perspective-bold.png",
		"MenuPlugins_StatViewer", "MenuPlugins");

	ModalShower = new ListiaryToolDummy("ModalShower", "Modal Shower",
		"m.index/img/article-bold.png", "m.index/img/article-bold.png",
		"MenuPlugins_ModalShower", "MenuPlugins");

	LocalFileLoader = new LocalLoaderPlugin("LocalFileLoader", "Local Loader",
		"m.index/img/folder-open-bold.png", "m.index/img/folder-open-bold.png",
		"MenuPlugins_LocalFileLoader", "MenuPlugins");

	SoundcloudPlayer = new ListiaryToolDummy("SoundcloudPlayer", "Soundcloud",
		"m.index/img/soundcloud-logo-bold.png", "m.index/img/soundcloud-logo-bold.png",
		"MenuPlugins_SoundcloudPlayer", "MenuPlugins");

	YoutubePlayer = new ListiaryToolDummy("YoutubePlayer", "Youtube Player",
		"m.index/img/youtube-logo-bold.png", "m.index/img/youtube-logo-bold.png",
		"MenuPlugins_YoutubePlayer", "MenuPlugins");

	StreamPlayer = new StreamPlayerPlugin("StreamPlayer", "Stream Player",
		"m.index/img/waveform-bold.png", "m.index/img/waveform-bold.png",
		"MenuPlugins_StreamPlayer", "MenuPlugins");


	AppTutor.AddAfter("MenuPluginsTopAnchor");
	StatViewer.AddAfter("MenuPluginsTopAnchor");
	ModalShower.AddAfter("MenuPluginsTopAnchor");

	//LocalFileLoader
	LocalFileLoader.Init(ToolManager, "plugins");
	LocalFileLoader.AddAfter("MenuPluginsTopAnchor");
	LocalFileLoader.AddControlsAfter("MenuPluginsMiddleAnchor");

	SoundcloudPlayer.AddAfter("MenuPluginsTopAnchor");
	YoutubePlayer.AddAfter("MenuPluginsTopAnchor");

	//StreamPlayer
	StreamPlayer.Init(ToolManager, "plugins");
	StreamPlayer.AddAfter("MenuPluginsTopAnchor");
	StreamPlayer.AddControlsAfter("MenuPluginsMiddleAnchor");
}
function addAdditionalTriggers() {

	// view sub-menu
	//CopyText.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");
	//PickRandom.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");
	//SortAZ.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");

	// filters sub-menu
	//CopyText.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");
	//PickRandom.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");
	//SortAZ.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");

	// main tool menu
	//CopyText.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	//PickRandom.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	//SortAZ.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	//HighlightGreen.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
}

//do
var ToolManager = new ListiaryToolManager();
var RemoveAllHighlighting, RemoveHighlight, HighlightBlue, HighlightGreen;
var HighlightYellow, HighlightBlack, HighlightSilver, HighlightGold, HighlightHotOrange, HighlightCoral;
var HighlightHotPink, HighlightMediumPurple, HighlightDodgerBlue, HighlightTurquoise, HighlightSpringGreen;
var SortArtist, SortArtistZa, SortArtistAz, SortTitleZa, SortTitleAz, SortZA, SortAZ;
var CopyDescribeEntry, CopyEntry, CopyFirstLink, CopyText;
var FilterRNotVisitedDummy, FilterRLinklessDummy, FilterRLinkfulDummy, FilterRVisitedDummy;
var FilterNotVisitedDummy, FilterVisitedDummy, FilterLinklessDummy, FilterLinkfulDummy;
var ViewDummyD, ViewDummyC, ViewDummyB, ViewDummyA, ViewAsListDummy, ViewElementsDummy, ViewLinksDummy;
var StreamPlayer, YoutubePlayer, SoundcloudPlayer, LocalFileLoader, ModalShower, StatViewer, AppTutor;
var PickRandomY, PickRandomX, PickRandomD, PickRandomC, PickRandomB, PickRandom;

addHighlightsSubmenu();
addSortersSubmenu();
addCopiersSubmenu();
addFiltersSubmenu();
addViewersSubmenu();
addPluginsSubmenu();
addPickersSubmenu();
addAdditionalTriggers();
ToolManager.DrawTools("MenuNextMiddleAnchor", "MenuNext");
