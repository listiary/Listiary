class PickRandomTool {

	//constants
	ToolId = "picker_random";
	ToolUrlId = "~picked";
	ToolName = "Pick Random";
	ToolCategory = "pickers";
	ToolIconGrey = "index/img/arrow-fat-down-bold-gray.png";
	ToolIconBlack = "index/img/arrow-fat-down-bold.png";
	TriggerId = "MenuPickers_PickRandom";
	TriggerClass = "MenuPickers";
	HighlightColor = "Black";
	
	//DOM items
	TriggerElement = null;
	AdditionalTriggers = [];
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;



	constructor(instanceName, triggerClass = null) {
		
		this.InstanceName = instanceName;
		//this.HighlightColor = color;		
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init(toolMap, historyMap, activeToolsTracker) {
		
		this.ToolMap = toolMap;
		this.HistoryMap = historyMap;
		this.ActiveToolsTracker = activeToolsTracker;
		activeToolsTracker.unsetHandlers[this.ToolId] = this.UnSelect.bind(this);

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
	AddNewTriggerAfter(anchorElementId, triggerClass) {
		
		//create the new trigger
		var html = this._createNewTriggerElement(this.ToolName, this.ToolIconGrey, this.AdditionalTriggers.length, triggerClass);
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		this.AdditionalTriggers.push(tempContainer.firstElementChild);
		tempContainer.remove();

		const anchorElement = document.getElementById(anchorElementId);
		const parent = anchorElement.parentNode;
		if (anchorElement.nextSibling) 
		{
			parent.insertBefore(this.AdditionalTriggers[this.AdditionalTriggers.length - 1], anchorElement.nextSibling);
		} 
		else 
		{
			parent.appendChild(this.AdditionalTriggers[this.AdditionalTriggers.length - 1]);
		}
	}
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ActiveToolsTracker.Set(this.ToolId, this.ToolCategory);
		this._enboldTriggerElement();
		//this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			this.AdditionalTriggers[i].href = "javascript:" + this.InstanceName + ".UnSelect();";
		}
		
		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//add it as version
		var newArticleId = articleId + this.ToolUrlId;
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
		
		//do actual change
		const elements = Array.from(document.querySelectorAll('.treedata'));
		const filteredElements = elements.filter(element => // remove comments and empty ones
		{
			let node = namespaces[element.id];
			if(!node) return false;
			if(node.type != "item") return false;
			else return true;
		});
		
		//select a random element
		const randomElement = filteredElements[Math.floor(Math.random() * filteredElements.length)];
		randomElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
		
		//change it
		let spans = randomElement.querySelectorAll('span');
		spans.forEach(span => 
		{
			highlightPicked(span);
		});
		let anchors = randomElement.querySelectorAll('a');
		anchors.forEach(anchor => 
		{
			highlightPicked(anchor);
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			this.AdditionalTriggers[i].href = "javascript:" + this.InstanceName + ".Select();";
		}
		
		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//add it as version
		let newArticleId = articleId.replace(this.ToolUrlId, "");
		newArticleId = newArticleId.replace("~picked", "");
		newArticleId = newArticleId + "~picked";
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
		
		articleId = articleId.replace(this.ToolUrlId, "");
		addLargeTree(articleId);
	}


	_createTriggerElement(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Select();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
	_createNewTriggerElement(triggerText, triggerIcon, idnum, triggerClass) {
		
		var html = "<a id='" + this.TriggerId + idnum + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Select();' class='" + triggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
	_enboldTriggerElement() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = 'black';
		menuitemSpan.style.fontStyle = 'italic';
		menuitemSpan.style.fontWeight = 'bold';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconBlack;
		
		this._enboldAdditionalTriggerElements();
	}
	_deboldTriggerElement() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = '#818181';
		menuitemSpan.style.fontStyle = 'normal';
		menuitemSpan.style.fontWeight = 'normal';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconGrey;
		
		this._deboldAdditionalTriggerElement();
	}
	_enboldAdditionalTriggerElements() {
		
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			let menuitemSpan = this.AdditionalTriggers[i].querySelector('span');
			menuitemSpan.style.color = 'black';
			menuitemSpan.style.fontStyle = 'italic';
			menuitemSpan.style.fontWeight = 'bold';
			let menuitemImg = this.AdditionalTriggers[i].querySelector('img');
			menuitemImg.src = this.ToolIconBlack;	
		}
	}
	_deboldAdditionalTriggerElement() {
		
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			let menuitemSpan = this.AdditionalTriggers[i].querySelector('span');
			menuitemSpan.style.color = '#818181';
			menuitemSpan.style.fontStyle = 'normal';
			menuitemSpan.style.fontWeight = 'normal';
			let menuitemImg = this.AdditionalTriggers[i].querySelector('img');
			menuitemImg.src = this.ToolIconGrey;
		}
	}
}
