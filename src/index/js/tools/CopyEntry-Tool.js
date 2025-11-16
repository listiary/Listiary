class CopyEntryTool {

	//constants
	ToolId = "copier_entry";
	ToolUrlId = "~copy-entries";
	ToolName = "Copy Entries";
	ToolCategory = "copiers";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyEntries";
	TriggerClass = "MenuCopiers";
	
	
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ActiveToolsTracker.Set(this.ToolId, this.ToolCategory);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
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
			let links = namespaces[id].links;
			for(let i = 0; i < links.length; i++)
			{
				text += "\n" + links[i].url;
			}
			
			let spans = element.querySelectorAll('span');
			spans.forEach(span => 
			{
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					event.stopPropagation();
					copycb(text);
				}
				highlight_TextPop_WithEvents(span);
			});
			
			let anchors = element.querySelectorAll('a');
			anchors.forEach(anchor => 
			{
				anchor.onclick = (event) => 
				{
					event.stopPropagation();
					event.preventDefault();
					copycb(text); 
				};
				highlight_TextPop_WithEvents(anchor);
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		
		articleId = articleId.replace(this.ToolUrlId, "");
		addLargeTree(articleId);
	}


	_createTriggerElement(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Select();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

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
