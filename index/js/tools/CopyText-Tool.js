class CopyTextTool {

	//constants
	ToolId = "copier_text";
	ToolUrlId = "~copy-text";
	ToolName = "Copy Text";
	ToolCategory = "copiers";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyText";
	TriggerClass = "MenuCopiers";
	
	
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
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
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
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			let id = element.id;
			let text = namespaces[id].text;
			
			let anchors = element.querySelectorAll('a');
			anchors.forEach(anchor => 
			{
				anchor.style.cursor = 'pointer';
				anchor.href = "javascript:void(0);";
				anchor.onclick = (event) => 
				{
					event.preventDefault();
					event.stopPropagation();
					this._copycb(text);
				}
				highlight_TextPop_WithEvents(anchor);
			});
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
	_copycb(text) { 										//Copy text to clipboard
	
		navigator.clipboard.writeText(text).then(() => 
		{
			//console.log('Copied:', text);
		})
		.catch(err => 
		{
			console.error('Failed to copy:', err);
		});
	}
	_isAlphaNumeric_Regex(letter) {

		return /^[a-zA-Z0-9]$/.test(letter);
	}
	_isAlphaNumeric_Ranges(letter) {
	
		const code = letter.charCodeAt(0);
		return (
			(code >= 48 && code <= 57) || // 0-9
			(code >= 65 && code <= 90) || // A-Z
			(code >= 97 && code <= 122)   // a-z
		);
	}
}
