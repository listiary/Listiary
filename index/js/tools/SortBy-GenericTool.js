class SortByGenericTool {

	//constants
	ToolId = null;
	ToolUrlId = null;
	ToolName = null;
	ToolCategory = "sorters";
	ToolIconGrey = "index/img/sort-ascending-bold-gray.png";
	ToolIconBlack = "index/img/sort-ascending-bold.png";
	TriggerId = null;
	TriggerClass = "MenuSorters";
	
	DecoratorName = null;
	IsSet = false;
	
	//DOM items
	TriggerElement = null;
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;


	constructor(decoratorName, toolName, menuName, instanceName) {			//"artist", "Artist", "Artist", "INSTANCE_NAME"
		
		if(!decoratorName) return;
		if(decoratorName.length < 1) return;
		
		this.ToolId = "sorter_" + decoratorName + "_by";
		this.ToolUrlId = "~sorted_" + decoratorName.toLowerCase() + "_by";
		this.ToolName = "Sort by " + toolName;
		this.TriggerId = "MenuSorters_Sort" + menuName;
		this.DecoratorName = decoratorName;
		this.InstanceName = instanceName;
		this.IsSet = true;
	}
	Init(toolMap, historyMap, activeToolsTracker) {
		
		if(this.IsSet == false) return;
		
		this.ToolMap = toolMap;
		this.HistoryMap = historyMap;
		this.ActiveToolsTracker = activeToolsTracker;
		activeToolsTracker.unsetHandlers[this.ToolId] = this.UnSort.bind(this);

		this.ToolMap[this.ToolId] = this.TriggerId;
		var html = this._createTriggerElement(this.ToolName, this.ToolIconGrey);
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		this.TriggerElement = tempContainer.firstElementChild;
		tempContainer.remove();
	}
	AddAfter(anchorElementId) {
		
		if(this.IsSet == false) return;
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
	Sort() {
		
		if(this.IsSet == false) return;
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ActiveToolsTracker.Set(this.ToolId, this.ToolCategory);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSort();";
		
		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//do actual change
		//Remove items with type "comment" or "nlcomment" or "empty"
		newTree.items = newTree.items
			.filter(
				item => item.type !== "comment" && 
				item.type !== "nlcomment" && 
				item.type !== "empty");

		//Sift through tracks
		let notSorted = [];
		let artists = {};
		newTree.items.forEach(item => {
			
			if(!item.decorators || 
				!item.decorators[this.DecoratorName] || 
				item.decorators[this.DecoratorName].length < 1) 
			{
				notSorted.push(item);
				return;
			}
			const artistName = item.decorators[this.DecoratorName];
			if(!artists[artistName]) artists[artistName] = [];
			artists[artistName].push(item);
		});
		let newList = [];
		let singleSongs = [];
		
		Object.entries(artists).forEach(([artistName, tracks]) => {
		
			if (tracks.length === 1) 
			{
				singleSongs.push(tracks[0]);
			} 
			else 
			{
				if(newList.length == 0)
				{
					var comment = this._createCommentElement(artistName);
					newList.push(comment);
				}
				else
				{
					var nlcomment = this._createNlCommentElement(artistName);
					newList.push(nlcomment);
				}
				newList.push(...tracks);
			}
		});
		
		var nlcomment = this._createNlCommentElement("Singles");
		newList.push(nlcomment);
		for(let i = 0; i < singleSongs.length; i++)
		{
			newList.push(singleSongs[i]);
		}
		
		var nlcomment = this._createNlCommentElement("Unsorted");
		newList.push(nlcomment);
		for(let i = 0; i < notSorted.length; i++)
		{
			newList.push(notSorted[i]);
		}
		
		//assign result
		newTree.items = newList;
		
		//add it as version
		var newArticleId = articleId + ToolUrlId;
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
	}
	UnSort() {

		if(this.IsSet == false) return;
		
		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Sort();";
		
		articleId = articleId.split('~')[0];
		addLargeTree(articleId);
	}
	
	
	_createTriggerElement(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Sort();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
	_enboldTriggerElement() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = 'black';
		menuitemSpan.style.fontStyle = 'italic';
		menuitemSpan.style.fontWeight = 'bold';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconBlack;
	}
	_deboldTriggerElement() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = '#818181';
		menuitemSpan.style.fontStyle = 'normal';
		menuitemSpan.style.fontWeight = 'normal';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconGrey;
	}
	_createCommentElement(commentText) {
		
		var comment = {
			
			decorators: [],
			id: "",
			links: [],
			name: "leaf",
			parentItem: null,
			text: "",
			type: "comment"
		};
		
		comment.id = this._generateRandomId(16);
		comment.text = commentText;
		return comment;
	}
	_createNlCommentElement(commentText) {
		
		var comment = {
			
			decorators: [],
			id: "",
			links: [],
			name: "leaf",
			parentItem: null,
			text: "",
			type: "nlcomment"
		};
		
		comment.id = this._generateRandomId(16);
		comment.text = commentText;
		return comment;
	}
	_generateRandomId(length) {

		return Math.random().toString(36).substring(2, 2 + length);
	}
}
