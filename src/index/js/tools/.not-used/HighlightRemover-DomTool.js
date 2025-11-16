class RemoveHighlightTool {

	//constants
	ToolId = "unhighlighter";
	ToolUrlId = "~remove-highlight";
	ToolName = "Erase";
	ToolCategory = "highlighters";
	ToolIconGrey = "index/img/pencil-simple-slash-bold-gray.png";
	ToolIconBlack = "index/img/pencil-simple-slash-bold.png";
	TriggerId = "MenuHighlighters_EraseHighlight";
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
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			let spans = element.querySelectorAll('span');
			spans.forEach(span => 
			{
				if(text != "") text += " ";
				text += span.innerText;
				
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					event.stopPropagation();
					unhighlight_Text(span);
				}
			});
			let anchors = element.querySelectorAll('a');
			let text = "";
			anchors.forEach(anchor => 
			{
				if(text != "") text += " ";
				text += anchor.innerText;
				
				anchor.style.cursor = 'pointer';
				anchor.href = "javascript:void(0);";
				anchor.onclick = (event) => 
				{
					event.preventDefault();
					event.stopPropagation();
					unhighlight_Text(anchor);
				}
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		
		//articleId = articleId.replace(this.ToolUrlId, "");
		//addLargeTree(articleId);
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
}
