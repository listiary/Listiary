class ListiaryActiveToolsTracker {

    highlighters = [];
    sorters = [];
    copiers = [];
    pickers = [];
    viewers = [];
    filters = [];
    languages = [];
    plugins = [];
    UnsetHandlers = {};


    RegisterTool(toolId, handler) {

        this.UnsetHandlers[toolId] = handler;
    }
    SetTool(toolId, category) {

        if(!category) return;
		if(!this[category]) return;
        if(this[category][toolId]) return;
        this[category].push(toolId);
	}
	UnsetTool(toolId, category) {

        if(!category) return;
		if(!this[category]) return;

		if (this[category].includes(toolId))
		{
			this[category] = this[category].filter(item => item !== toolId);
		}
    }
    UnsetCategory(category) {

		if(!category) return;
		if(!this[category]) return;

		for(let i = 0; i < this[category].length; i++)
		{
			let id = this[category][i];
			if(this.UnsetHandlers[id]) this.UnsetHandlers[id]();
		}
		this[category] = [];
	}
	UnsetAllCategories() {

        UnsetCategory("highlighters");
        UnsetCategory("sorters");
        UnsetCategory("copiers");
        UnsetCategory("pickers");
        UnsetCategory("viewers");
        UnsetCategory("filters");
        UnsetCategory("languages");
        UnsetCategory("plugins");
	}
}
class ListiaryToolManager {

    ActiveToolsTracker = new ListiaryActiveToolsTracker();

    //private
    AnchorElementId = "MenuNextMiddleAnchor";
    TriggersClass = "MenuNext";

    TopToolsLength = 4;
    FavoriteTools = {

        "MenuCopiers_CopyFLink" : 10,
        "MenuHighlighters_HighlightYellow" : 10,
		"MenuSorters_SortAz" : 10,
		"MenuPickers_PickRandom" : 10,
    };
    CurrentTopTools = [];

    //maps
    InstanceMap = {};
    UsageMap = {};

    constructor(topToolsLength = 4) {

        this.TopToolsLength = topToolsLength;
	}
	RegisterTool(toolId, toolInstance) {

        this.InstanceMap[toolId] = toolInstance;
        this.UsageMap[toolId] = this.FavoriteTools[toolId] || 0;
        this.ActiveToolsTracker.SetTool(toolId, toolInstance.UnSelect, toolInstance.ToolCategory);
    }
    RecordUsage(toolId) {

        //increment counter
        if (this.UsageMap[toolId] != null) this.UsageMap[toolId]++;
        else this.UsageMap[toolId] = 1;

        //get new top array
        const topKeys = Object.entries(this.UsageMap)	    // Get up to 4 keys with values > 0
            .filter(([_, value]) => value > 0) 				// Remove entries with value 0
            .sort((a, b) => b[1] - a[1]) 					// Sort by value descending
            .slice(0, this.TopToolsLength) 					// Get up to `this.TopToolsLength` items
            .map(([key]) => key); 							// Extract keys

        //if new top array is not the same as the last, do
		if (this._arraysAreIdentical(this.CurrentTopTools, topKeys) == false)
		{
			this.removeCurrentTopTools();
			this.CurrentTopTools = topKeys;
			this.addCurrentTopTools(this.AnchorElementId);
		}
    }
    DrawTools(anchorId = null, className = null) {

        const topKeys = Object.entries(this.UsageMap)	    // Get up to 4 keys with values > 0
            .filter(([_, value]) => value > 0) 				// Remove entries with value 0
            .sort((a, b) => b[1] - a[1]) 					// Sort by value descending
            .slice(0, this.TopToolsLength) 					// Get up to `this.TopToolsLength` items
            .map(([key]) => key); 							// Extract keys

        this.CurrentTopTools = topKeys.slice().reverse();
        if(className == null) className = this.TriggersClass;
        if(anchorId == null) anchorId = this.AnchorElementId;
        this.addCurrentTopTools(anchorId, className);
    }

    removeCurrentTopTools() {	   							         //remove from DOM

		this.CurrentTopTools.forEach(key =>
		{
            let toolInstance = this.InstanceMap[key];
            let triggerElement = toolInstance.NextTriggerElement;
            triggerElement.remove();
		});
	}
	addCurrentTopTools(anchorElementId, className) {                 //add to DOM

        this.CurrentTopTools.forEach(key =>
		{
            let toolInstance = this.InstanceMap[key];
            let triggerElement = toolInstance.NextTriggerElement;
            if(className) toolInstance.NextTriggerElement.className = className;
            _addAfter(anchorElementId, triggerElement);
		});
    }

    _arraysAreEqual(arr1, arr2) {

		if (arr1.length !== arr2.length) return false;
		return arr1.every((value, index) => value === arr2[index]);
	}
    _arraysAreIdentical(arr1, arr2) {

        if (arr1.length !== arr2.length) return false;

        const sorted1 = [...arr1].sort();
        const sorted2 = [...arr2].sort();

        return sorted1.every((value, index) => value === sorted2[index]);
    }
}

