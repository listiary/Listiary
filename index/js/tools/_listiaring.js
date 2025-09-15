


var menuItemIds = {};				//"copier_elements": 		"MenuCopiers_CopyElements",
var ToolUsageHistory = {
	
	Map: {
		
		"copier_elements": 		1,
		"copier_flink": 		0,
		"copier_entries": 		1,
		"copier_entries_ds": 	0,
		
		"sorter_az":			1,
		"sorter_za":			0,
		
		"picker_random":		0
	},
	CurrentTopTools: [

		"copier_elements", 
		"copier_entries",
		"sorter_az",
		"sorter_za"
	],
	
	RecordUsage: function(toolId) {
        
		const topKeys = Object.entries(this.Map)		// Get up to 4 keys with values > 0
		.filter(([_, value]) => value > 0) 				// Remove entries with value 0
		.sort((a, b) => b[1] - a[1]) 					// Sort by value descending
		.slice(0, 4) 									// Get up to 4 items
		.map(([key]) => key); 							// Extract keys

		if (this._arraysAreEqual(this.CurrentTopTools, topKeys) == false) 
		{
			this.removeTools();
			this.CurrentTopTools = topKeys;
			//this.addTools();
		}
    },
	_arraysAreEqual: function(arr1, arr2) {

		if (arr1.length !== arr2.length) return false;
		return arr1.every((value, index) => value === arr2[index]);
	},
	removeTools: function() {    						//remove from DOM
		
		this.CurrentTopTools.forEach(key => 
		{
			const element = document.getElementById('ToolShortcut_' + key);
			if (element) element.remove();
		});
	},
	addTools: function() {	   							//add to DOM
		
		this.removeTools();

		this.CurrentTopTools.forEach(key => 
		{
			let originalToolTriggerId = menuItemIds[key];
			let shortcutToolTriggerId = 'ToolShortcut_' + key;
			let originalElement = document.getElementById(originalToolTriggerId);
			if (originalElement) 
			{
				let clonedElement = originalElement.cloneNode(true);
				clonedElement.id = shortcutToolTriggerId;
				clonedElement.style.display = "block";
				document.getElementById("sidenav").appendChild(clonedElement);
			}
		});
	}
};
var ActiveToolsTracker = {

	highlighters: [],
	sorters: [],
	copiers: [],
	pickers: [],
	viewers: [],
	filters: [],
	unsetHandlers: {},

	Set: function(ToolId, category) {
		
		if(!category) return;
		if(!this[category]) return;
		
		this[category].push(ToolId);
		//ToolUsageHistory.addTools();
	},
	Unset: function(toolId, category) {
		
		if(!category) return;
		if(!this[category]) return;
		
		if (this[category].includes(toolId)) 
		{
			this[category] = this[category].filter(item => item !== toolId);
		}
	},
	UnsetCategory: function(category) {
		
		if(!category) return;
		if(!this[category]) return;
		
		for(let i = 0; i < this[category].length; i++)
		{
			let id = this[category][i];
			if(this.unsetHandlers[id]) this.unsetHandlers[id]();
		}
		this[category] = [];
	},
	UnsetAll: function() {
		
		this.highlighters = [];
		this.sorters = [];
		this.copiers = [];
		this.pickers = [];
		this.viewers = [];
		this.filters = [];
	}
}




	//highlight sub-menu 		https://htmlcolorcodes.com/color-names/
	
	var RemoveAllHighlighting = new MassRemoveHighlightTool("RemoveAllHighlighting", "MenuHighlighters");
	RemoveAllHighlighting.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	RemoveAllHighlighting.AddAfter("MenuHighlightersTopAnchor");

	var RemoveHighlight = new RemoveHighlightTool("RemoveHighlight", "MenuHighlighters");
	RemoveHighlight.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	RemoveHighlight.AddAfter("MenuHighlightersTopAnchor");

	var HighlightBlue = new HighlightTool("HighlightBlue", "Blue", "MenuHighlighters");
	HighlightBlue.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightBlue.AddAfter("MenuHighlightersTopAnchor");
	HighlightBlue.HighlightColor = "LightSteelBlue";

	var HighlightGreen = new HighlightTool("HighlightGreen", "Green", "MenuHighlighters");
	HighlightGreen.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightGreen.AddAfter("MenuHighlightersTopAnchor");
	HighlightGreen.HighlightColor = "Chartreuse";

	var HighlightYellow = new HighlightTool("HighlightYellow", "Yellow", "MenuHighlighters");
	HighlightYellow.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightYellow.AddAfter("MenuHighlightersTopAnchor");

	//middle
	var HighlightBlack = new HighlightTool("HighlightBlack", "Black", "MenuHighlighters");
	HighlightBlack.ToolName = "Black";
	HighlightBlack.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightBlack.AddAfter("MenuHighlightersMiddleAnchor");
	
	//DimGray
	
	var HighlightSilver = new HighlightTool("HighlightSilver", "Silver", "MenuHighlighters");
	HighlightSilver.ToolName = "Silver";
	HighlightSilver.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightSilver.AddAfter("MenuHighlightersMiddleAnchor");

	var HighlightGold = new HighlightTool("HighlightGold", "Gold", "MenuHighlighters");
	HighlightGold.ToolName = "Gold";
	HighlightGold.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightGold.AddAfter("MenuHighlightersMiddleAnchor");
	
	var HighlightHotOrange = new HighlightTool("HighlightHotOrange", "Orange", "MenuHighlighters");
	HighlightHotOrange.ToolName = "Orange";
	HighlightHotOrange.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightHotOrange.AddAfter("MenuHighlightersMiddleAnchor");
	
	//Tomato is almost the same but one idea more red
	var HighlightCoral = new HighlightTool("HighlightCoral", "Coral", "MenuHighlighters");
	HighlightCoral.ToolName = "Coral";
	HighlightCoral.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightCoral.AddAfter("MenuHighlightersMiddleAnchor");

	var HighlightHotPink = new HighlightTool("HighlightHotPink", "HotPink", "MenuHighlighters");
	HighlightHotPink.ToolName = "HotPink";
	HighlightHotPink.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightHotPink.AddAfter("MenuHighlightersMiddleAnchor");
	
	var HighlightMediumPurple = new HighlightTool("HighlightMediumPurple", "MediumPurple", "MenuHighlighters");
	HighlightMediumPurple.ToolName = "MediumPurple";
	HighlightMediumPurple.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightMediumPurple.AddAfter("MenuHighlightersMiddleAnchor");

	var HighlightDodgerBlue = new HighlightTool("HighlightDodgerBlue", "DodgerBlue", "MenuHighlighters");
	HighlightDodgerBlue.ToolName = "DodgerBlue";
	HighlightDodgerBlue.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightDodgerBlue.AddAfter("MenuHighlightersMiddleAnchor");
	
	var HighlightTurquoise = new HighlightTool("HighlightTurquoise", "Turquoise", "MenuHighlighters");
	HighlightTurquoise.ToolName = "Turquoise";
	HighlightTurquoise.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightTurquoise.AddAfter("MenuHighlightersMiddleAnchor");

	var HighlightSpringGreen = new HighlightTool("HighlightSpringGreen", "SpringGreen", "MenuHighlighters");
	HighlightSpringGreen.ToolName = "SpringGreen";
	HighlightSpringGreen.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	HighlightSpringGreen.AddAfter("MenuHighlightersMiddleAnchor");
	
	
	
	
	//sorters sub-menu
	HighlightBlue.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");
	HighlightGreen.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");
	HighlightYellow.AddNewTriggerAfter("MenuSortersBottomAnchor", "MenuSorters");
	
	var SortArtist = new SortByGenericTool("artist", "Artist", "Artist", "SortArtist");
	SortArtist.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortArtist.AddAfter("MenuSortersMiddleAnchor");

	var SortArtistZa = new SortZaGenericTool("artist", "Artist", "Artist", "SortArtistZa");
	SortArtistZa.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortArtistZa.AddAfter("MenuSortersMiddleAnchor");

	var SortArtistAz = new SortAzGenericTool("artist", "Artist", "Artist", "SortArtistAz");
	SortArtistAz.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortArtistAz.AddAfter("MenuSortersMiddleAnchor");

	var SortTitleZa = new SortZaGenericTool("title", "Title", "Title", "SortTitleZa");
	SortTitleZa.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortTitleZa.AddAfter("MenuSortersMiddleAnchor");

	var SortTitleAz = new SortAzGenericTool("title", "Title", "Title", "SortTitleAz");
	SortTitleAz.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortTitleAz.AddAfter("MenuSortersMiddleAnchor");

	var SortZA = new SortZaTool("SortZA");
	SortZA.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortZA.AddAfter("MenuSortersTopAnchor");

	var SortAZ = new SortAzTool("SortAZ");
	SortAZ.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	SortAZ.AddAfter("MenuSortersTopAnchor");


	// copy sub-menu

	SortAZ.AddNewTriggerAfter("MenuCopiersBottomAnchor", "MenuCopiers");
	HighlightBlue.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");
	HighlightGreen.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");
	HighlightYellow.AddNewTriggerAfter("MenuCopiersMiddleAnchor", "MenuCopiers");

	var CopyDescribeEntry = new CopyDescribeEntryTool("CopyDescribeEntry");
	CopyDescribeEntry.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyDescribeEntry.AddAfter("MenuCopiersTopAnchor");

	var CopyEntry = new CopyEntryTool("CopyEntry");
	CopyEntry.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyEntry.AddAfter("MenuCopiersTopAnchor");

	var CopyFirstLink = new CopyFirstLinkTool("CopyFirstLink");
	CopyFirstLink.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyFirstLink.AddAfter("MenuCopiersTopAnchor");

	var CopyText = new CopyTextTool("CopyText");
	CopyText.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	CopyText.AddAfter("MenuCopiersTopAnchor");
	
	
	// pick sub-menu
	var PickRandom = new PickRandomTool("PickRandom");
	PickRandom.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandom.AddAfter("MenuPickersTopAnchor");
	CopyText.AddNewTriggerAfter("MenuPickersMiddleAnchor", "MenuPickers");
	PickRandom.AddNewTriggerAfter("MenuPickersMiddleAnchor", "MenuPickers");
	SortAZ.AddNewTriggerAfter("MenuPickersMiddleAnchor", "MenuPickers");
	
	
	// view sub-menu
	CopyText.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");
	PickRandom.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");
	SortAZ.AddNewTriggerAfter("MenuViewersBottomAnchor", "MenuViewers");
	
	
	// filters sub-menu
	CopyText.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");
	PickRandom.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");
	SortAZ.AddNewTriggerAfter("MenuFiltersMiddleAnchor", "MenuFilters");
	
	
	// main tool menu
	CopyText.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	PickRandom.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	SortAZ.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");
	HighlightGreen.AddNewTriggerAfter("MenuNextMiddleAnchor", "MenuNext");


	//ToolUsageHistory.addTools();