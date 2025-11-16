class MassRemoveHighlightTool {

	//constants
	ToolId = "unhighlighter";
	ToolUrlId = "~remove-all-highlight";
	ToolName = "Remove All";
	ToolCategory = "highlighters";
	ToolIconGrey = "index/img/x-circle-bold-gray.png";
	ToolIconBlack = "index/img/x-circle-bold.png";
	TriggerId = "MenuHighlighters_RemoveAllHighlight";
	TriggerClass = "MenuHighlighters";
	
	//DOM items
	TriggerElement = null;
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;



	constructor(instanceName, triggerClass = null) {
		
		this.InstanceName = instanceName;		
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init(toolMap, historyMap, activeToolsTracker) {
		
		this.ToolMap = toolMap;
		this.HistoryMap = historyMap;
		this.ActiveToolsTracker = activeToolsTracker;

		this.ToolMap[this.ToolId] = this.TriggerId;
		var html = this._createTriggerElement(this.ToolName, this.ToolIconGrey);
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		this.TriggerElement = tempContainer.firstElementChild;
		tempContainer.remove();
	}
	AddAfter(anchorElementId) {
		
		if(!this.TriggerElement) return;
		
		const anchorElement = document.getElementById(anchorElementId);
		const parent = anchorElement.parentNode;
		if (anchorElement.nextSibling) 
		{
			parent.insertBefore(this.TriggerElement, anchorElement.nextSibling);
		} 
		else 
		{
			parent.appendChild(this.TriggerElement);
		}
	}
	Remove() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		
		//add it as version
		let newArticleId = articleId.replace("~highlighted", "");
		articleId = newArticleId;

		for(let i = 0; i < namespaces[articleId].items.length; i++)
		{
			var itemId = namespaces[articleId].items[i].id;
			var item = namespaces[itemId];

			if(item && item.decorators && item.decorators["highlight"])
			{
				delete item.decorators["highlight"];
			}
		}

		addLargeTree(articleId);
	}

	_createTriggerElement(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Remove();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
