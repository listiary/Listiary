function _getBr(brClass) {

	var html = "<br class='" + brClass + "'>";
	const tempContainer = document.createElement("div");
	tempContainer.innerHTML = html;
	var elem = tempContainer.firstElementChild;
	tempContainer.remove();
	return elem;
}
function _getElement(html) {

	if(!html || html.length < 3) return null;

	const tempContainer = document.createElement("div");
	tempContainer.innerHTML = html;
	var elem = tempContainer.firstElementChild;
	tempContainer.remove();
	return elem;
}
function _addAfter(anchorElementId, domElement) {

	if(!domElement) return;

	const anchorElement = document.getElementById(anchorElementId);
	const parent = anchorElement.parentNode;
	if (anchorElement.nextSibling)
	{
		parent.insertBefore(domElement, anchorElement.nextSibling);
	}
	else
	{
		parent.appendChild(domElement);
	}
}



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
class CopyFirstLinkTool {

	//constants
	ToolId = "copier_flink";
	ToolUrlId = "~copy-flink";
	ToolName = "Copy First Link";
	ToolCategory = "copiers";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyFLink";
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
			let links = namespaces[id].links;
			let text = "";
			if(links.length > 0) text = links[0].url;

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
class CopyDescribeEntryTool {

	//constants
	ToolId = "copier_dsentry";
	ToolUrlId = "~copy-entries-ds";
	ToolName = "Entries (*.ds)";
	ToolCategory = "copiers";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyDsEntries";
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
			let decorators = namespaces[id].decorators;
			for(let i = 0; i < decorators.length; i++)
			{
				console.log(decorators[i]);
			}
			for(let i = 0; i < links.length; i++)
			{
				text += "\n[" + links[i].url;
				if(links[i].text && links[i].text.length > 0) text += " | " + links[i].text;
				text += "]";
				//console.log(links[i].text + " - " + links[i].url + " - " + links[i].name);
			}
			
			let spans = element.querySelectorAll('span');
			spans.forEach(span => 
			{
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					event.stopPropagation();
					this._copycb(text);
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
					this._copycb(text); 
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
class CopyElementsTool {

	//constants
	ToolId = "copier_elements";
	ToolUrlId = "~copy-elements";
	ToolName = "Copy Elements";
	ToolCategory = "copiers";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyElements";
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
			let spans = element.querySelectorAll('span');
			spans.forEach(span => 
			{
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					event.stopPropagation();
					copycb(span.textContent);
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
					copycb(anchor.href); 
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
class SortAzTool {

	//constants
	ToolId = "sorter_az";
	ToolUrlId = "~sorted-az";
	ToolName = "Sort A-Z";
	ToolCategory = "sorters";
	ToolIconGrey = "index/img/sort-ascending-bold-gray.png";
	ToolIconBlack = "index/img/sort-ascending-bold.png";
	TriggerId = "MenuSorters_SortAz";
	TriggerClass = "MenuSorters";
	
	
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
		activeToolsTracker.unsetHandlers[this.ToolId] = this.UnSort.bind(this);

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
	Sort() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ActiveToolsTracker.Set(this.ToolId, this.ToolCategory);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSort();";
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			this.AdditionalTriggers[i].href = "javascript:" + this.InstanceName + ".UnSort();";
		}
		
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

		//Split to alphanumeric and others
		let alphanumerics = [];
		let nonAlphanumerics = [];
		newTree.items.forEach(item => {
			
			if(item.text.length < 1) return;
			const firstChar = item.text[0];
			if (/[a-zA-Z0-9]/.test(firstChar)) alphanumerics.push(item);
			else nonAlphanumerics.push(item);
		});
		
		//Sort
		alphanumerics = alphanumerics.sort((a, b) => a.text.localeCompare(b.text));
		nonAlphanumerics = nonAlphanumerics.sort((a, b) => a.text.localeCompare(b.text));
		
		//Add comments
		let newList = [];
		if(nonAlphanumerics.length > 0)
		{
			var firstComment = this._createCommentElement('#');
			newList.push(firstComment);
			for (let i = 0; i < nonAlphanumerics.length; i++) 
			{
				newList.push(nonAlphanumerics[i]);
			}
		}
		var curLetter = alphanumerics[0].text[0].toUpperCase();
		var firstNlc = this._createNlCommentElement(curLetter);
		newList.push(firstNlc);
		for (let i = 0; i < alphanumerics.length; i++) 
		{
			if (curLetter == alphanumerics[i].text[0].toUpperCase())
			{
				newList.push(alphanumerics[i]);	
			}
			else
			{
				curLetter = alphanumerics[i].text[0].toUpperCase();
				var nlc = this._createNlCommentElement(curLetter);
				newList.push(nlc);
				newList.push(alphanumerics[i]);
			}
		}
		
		//assign result
		newTree.items = newList;
		
		//add it as version
		var newArticleId = articleId + this.ToolUrlId;
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
	}
	UnSort() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Sort();";
		for(let i = 0; i < this.AdditionalTriggers.length; i++)
		{
			this.AdditionalTriggers[i].href = "javascript:" + this.InstanceName + ".Sort();";
		}
		
		articleId = articleId.split('~')[0];
		addLargeTree(articleId);
	}
	
	
	_createTriggerElement(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Sort();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
	_createNewTriggerElement(triggerText, triggerIcon, idnum, triggerClass) {
		
		var html = "<a id='" + this.TriggerId + idnum + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + this.InstanceName + ".Sort();' class='" + triggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

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
class SortZaTool {

	//constants
	ToolId = "sorter_za";
	ToolUrlId = "~sorted-za";
	ToolName = "Sort Z-A";
	ToolCategory = "sorters";
	ToolIconGrey = "index/img/sort-ascending-bold-gray.png";
	ToolIconBlack = "index/img/sort-ascending-bold.png";
	TriggerId = "MenuSorters_SortZa";
	TriggerClass = "MenuSorters";
	
	
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
		activeToolsTracker.unsetHandlers[this.ToolId] = this.UnSort.bind(this);

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
	Sort() {
		
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

		//Split to alphanumeric and others
		let alphanumerics = [];
		let nonAlphanumerics = [];
		newTree.items.forEach(item => {
			
			if(item.text.length < 1) return;
			const firstChar = item.text[0];
			if (/[a-zA-Z0-9]/.test(firstChar)) alphanumerics.push(item);
			else nonAlphanumerics.push(item);
		});
		
		//Sort
		alphanumerics = alphanumerics.sort((a, b) => b.text.localeCompare(a.text));
		nonAlphanumerics = nonAlphanumerics.sort((a, b) => b.text.localeCompare(a.text));
		
		//Add comments
		let newList = [];
		var curLetter = alphanumerics[0].text[0].toUpperCase();
		var firstNlc = this._createCommentElement(curLetter);
		newList.push(firstNlc);
		for (let i = 0; i < alphanumerics.length; i++) 
		{
			if (curLetter == alphanumerics[i].text[0].toUpperCase())
			{
				newList.push(alphanumerics[i]);	
			}
			else
			{
				curLetter = alphanumerics[i].text[0].toUpperCase();
				var nlc = this._createNlCommentElement(curLetter);
				newList.push(nlc);
				newList.push(alphanumerics[i]);
			}
		}
		if(nonAlphanumerics.length > 0)
		{
			var firstComment = this._createNlCommentElement('#');
			newList.push(firstComment);
			for (let i = 0; i < nonAlphanumerics.length; i++) 
			{
				newList.push(nonAlphanumerics[i]);
			}
		}
		
		//assign result
		newTree.items = newList;
		
		//add it as version
		var newArticleId = articleId + this.ToolUrlId;
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
	}
	UnSort() {

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
class SortAzGenericTool {

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
		
		this.ToolId = "sorter_" + decoratorName + "_az";
		this.ToolUrlId = "~sorted-" + decoratorName.toLowerCase() + "-az";
		this.ToolName = "Sort by " + toolName + " A-Z";
		this.TriggerId = "MenuSorters_Sort" + menuName + "Az";
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

		//Split to alphanumeric and others
		let notSorted = [];
		let alphanumerics = [];
		let nonAlphanumerics = [];
		newTree.items.forEach(item => {
			
			if(!item.decorators || 
				!item.decorators[this.DecoratorName] || 
				item.decorators[this.DecoratorName].length < 1) 
			{
				notSorted.push(item);
				return;
			}
			const firstChar = item.decorators[this.DecoratorName][0];
			if (/[a-zA-Z0-9]/.test(firstChar)) alphanumerics.push(item);
			else nonAlphanumerics.push(item);
		});
		
		//Sort
		alphanumerics = alphanumerics.sort((a, b) => a.decorators[this.DecoratorName].localeCompare(b.decorators[this.DecoratorName]));
		nonAlphanumerics = nonAlphanumerics.sort((a, b) => a.decorators[this.DecoratorName].localeCompare(b.decorators[this.DecoratorName]));
		
		//Add comments
		let newList = [];
		if(nonAlphanumerics.length > 0)
		{
			var firstComment = this._createCommentElement('#');
			newList.push(firstComment);
			for (let i = 0; i < nonAlphanumerics.length; i++) 
			{
				newList.push(nonAlphanumerics[i]);
			}
		}
		var curLetter = alphanumerics[0].decorators[this.DecoratorName][0].toUpperCase();
		var firstNlc = this._createNlCommentElement(curLetter);
		newList.push(firstNlc);
		for (let i = 0; i < alphanumerics.length; i++) 
		{
			if (curLetter == alphanumerics[i].decorators[this.DecoratorName][0].toUpperCase())
			{
				newList.push(alphanumerics[i]);	
			}
			else
			{
				curLetter = alphanumerics[i].decorators[this.DecoratorName][0].toUpperCase();
				var nlc = this._createNlCommentElement(curLetter);
				newList.push(nlc);
				newList.push(alphanumerics[i]);
			}
		}
		
		var nlcomment = this._createNlCommentElement("Unsortable");
		newList.push(nlcomment);
		for (let i = 0; i < notSorted.length; i++) 
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
class SortZaGenericTool {

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
		
		this.ToolId = "sorter_" + decoratorName + "_za";
		this.ToolUrlId = "~sorted_" + decoratorName.toLowerCase() + "_za";
		this.ToolName = "Sort by " + toolName + " Z-A";
		this.TriggerId = "MenuSorters_Sort" + menuName + "Za";
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

		//Split to alphanumeric and others
		let notSorted = [];
		let alphanumerics = [];
		let nonAlphanumerics = [];
		newTree.items.forEach(item => {
			
			if(!item.decorators || 
				!item.decorators[this.DecoratorName] || 
				item.decorators[this.DecoratorName].length < 1) 
			{
				notSorted.push(item);
				return;
			}
			const firstChar = item.decorators[this.DecoratorName][0];
			if (/[a-zA-Z0-9]/.test(firstChar)) alphanumerics.push(item);
			else nonAlphanumerics.push(item);
		});
		
		//Sort
		alphanumerics = alphanumerics.sort((a, b) => b.decorators[this.DecoratorName].localeCompare(a.decorators[this.DecoratorName]));
		nonAlphanumerics = nonAlphanumerics.sort((a, b) => b.decorators[this.DecoratorName].localeCompare(a.decorators[this.DecoratorName]));
		
		//Add comments
		let newList = [];
		var curLetter = alphanumerics[0].decorators[this.DecoratorName][0].toUpperCase();
		var firstNlc = this._createCommentElement(curLetter);
		newList.push(firstNlc);
		for (let i = 0; i < alphanumerics.length; i++) 
		{
			if (curLetter == alphanumerics[i].decorators[this.DecoratorName][0].toUpperCase())
			{
				newList.push(alphanumerics[i]);	
			}
			else
			{
				curLetter = alphanumerics[i].decorators[this.DecoratorName][0].toUpperCase();
				var nlc = this._createNlCommentElement(curLetter);
				newList.push(nlc);
				newList.push(alphanumerics[i]);
			}
		}
		if(nonAlphanumerics.length > 0)
		{
			var firstComment = this._createNlCommentElement('#');
			newList.push(firstComment);
			for (let i = 0; i < nonAlphanumerics.length; i++) 
			{
				newList.push(nonAlphanumerics[i]);
			}
		}
		var nlcomment = this._createNlCommentElement("Unsortable");
		newList.push(nlcomment);
		for (let i = 0; i < notSorted.length; i++) 
		{
			newList.push(notSorted[i]);
		}
		
		//assign result
		newTree.items = newList;
		
		//add it as version
		var newArticleId = articleId + this.ToolUrlId;
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
class HighlightTool {

	//constants
	ToolId = "highlighter";
	ToolUrlId = "~highlight";
	ToolName = "Highlight";
	ToolCategory = "highlighters";
	ToolIconGrey = "index/img/pencil-simple-line-bold-gray.png";
	ToolIconBlack = "index/img/pencil-simple-line-bold.png";
	TriggerId = "MenuHighlighters_HighlightElements";
	TriggerClass = "MenuHighlighters";
	HighlightColor = null;
	
	//DOM items
	TriggerElement = null;
	AdditionalTriggers = [];
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;



	constructor(instanceName, color, triggerClass = null) {
		
		this.InstanceName = instanceName;
		
		this.HighlightColor = color;
		
		//add to this generic tool's properties
		var m = color.trim().toLowerCase();
		var n = m[0].toUpperCase() + m.slice(1);
		this.ToolName = this.ToolName + " " + n;
		this.ToolId += "-" + m;
		this.ToolUrlId += "-" + this.HighlightColor;
		
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
		
		//do actual change
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
					highlight_Text(span, this.HighlightColor);
					
					let elemId = element.id;
					let node = namespaces[elemId];
					node.decorators["highlight"] = this.HighlightColor;
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
					highlight_Text(anchor, this.HighlightColor);
					
					let elemId = element.id;
					let node = namespaces[elemId];
					node.decorators["highlight"] = this.HighlightColor;
				}
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

		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//add it as version
		let newArticleId = articleId.replace(this.ToolUrlId, "");
		newArticleId = newArticleId.replace("~highlighted", "");
		newArticleId = newArticleId + "~highlighted";
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
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
					
					let elemId = element.id;
					let node = namespaces[elemId];
					delete node.decorators["highlight"];
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
					
					let elemId = element.id;
					let node = namespaces[elemId];
					delete node.decorators["highlight"];
				}
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		
		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//add it as version
		let newArticleId = articleId.replace(this.ToolUrlId, "");
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
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
}
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



class FilterLinkfulDummyTool {

	ToolName = "leave linkful";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterLinkfulDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_Linkful();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterLinklessDummyTool {

	ToolName = "leave linkless";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterLinklessDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_Linkless();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterVisitedDummyTool {

	ToolName = "leave visited";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterVisitedDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_Visited();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterNotVisitedDummyTool {

	ToolName = "leave not-visited";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterNotVisitedDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_NotVisited();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterRLinkfulDummyTool {

	ToolName = "remove linkful";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterLinkfulDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_RLinkful();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterRLinklessDummyTool {

	ToolName = "remove linkless";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterLinklessDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_RLinkless();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterRVisitedDummyTool {

	ToolName = "remove visited";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterVisitedDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_RVisited();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class FilterRNotVisitedDummyTool {

	ToolName = "remove not-visited";
	ToolIconGrey = "index/img/funnel-bold-gray.png";
	ToolIconBlack = "index/img/funnel-bold.png";
	TriggerId = "MenuFilters_FilterNotVisitedDummy";
	TriggerClass = "MenuFilters";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:setFilter_RNotVisited();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}




class ViewLinksDummyTool {

	ToolName = "view links";
	ToolIconGrey = "index/img/eye-bold-gray.png";
	ToolIconBlack = "index/img/eye-bold.png";
	TriggerId = "MenuViewers_ViewLinksDummy";
	TriggerClass = "MenuViewers";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:void(0);' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class ViewElementsDummyTool {

	ToolName = "view elements";
	ToolIconGrey = "index/img/eye-bold-gray.png";
	ToolIconBlack = "index/img/eye-bold.png";
	TriggerId = "MenuViewers_ViewelementsDummy";
	TriggerClass = "MenuViewers";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:void(0);' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class ViewAsListDummyTool {

	ToolName = "view as list";
	ToolIconGrey = "index/img/eye-bold-gray.png";
	ToolIconBlack = "index/img/eye-bold.png";
	TriggerId = "MenuViewers_ViewaslistDummy";
	TriggerClass = "MenuViewers";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:void(0);' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}
class ViewDummyTool {

	ToolName = "view ??";
	ToolIconGrey = "index/img/eye-bold-gray.png";
	ToolIconBlack = "index/img/eye-bold.png";
	TriggerId = "MenuViewers_ViewDummy";
	TriggerClass = "MenuViewers";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:void(0);' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}



class PluginDummyTool {

	ToolName = "?? Plugin";
	ToolIconGrey = "index/img/flask-bold.png";
	ToolIconBlack = "index/img/flask-bold.png";
	TriggerId = "MenuPlugin_PluginDummy";
	TriggerClass = "MenuPlugins";
	InstanceName = null;
	TriggerElement = null;

	constructor(instanceName, triggerClass = null) {

		this.InstanceName = instanceName;
		if(triggerClass != null) this.TriggerClass = triggerClass;
	}
	Init() {

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

	_createTriggerElement(triggerText, triggerIcon) {

		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:void(0);' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	}
}



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


	// filter sub-menu
	var FilterRNotVisitedDummy = new FilterRNotVisitedDummyTool("FilterRNotVisited");
	FilterRNotVisitedDummy.Init();
	FilterRNotVisitedDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterRLinklessDummy = new FilterRLinklessDummyTool("FilterRLinkless");
	FilterRLinklessDummy.Init();
	FilterRLinklessDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterRLinkfulDummy = new FilterRLinkfulDummyTool("FilterRLinkful");
	FilterRLinkfulDummy.Init();
	FilterRLinkfulDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterRVisitedDummy = new FilterRVisitedDummyTool("FilterRVisited");
	FilterRVisitedDummy.Init();
	FilterRVisitedDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterNotVisitedDummy = new FilterNotVisitedDummyTool("FilterNotVisited");
	FilterNotVisitedDummy.Init();
	FilterNotVisitedDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterVisitedDummy = new FilterVisitedDummyTool("FilterVisited");
	FilterVisitedDummy.Init();
	FilterVisitedDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterLinklessDummy = new FilterLinklessDummyTool("FilterLinkless");
	FilterLinklessDummy.Init();
	FilterLinklessDummy.AddAfter("MenuFiltersTopAnchor");

	var FilterLinkfulDummy = new FilterLinkfulDummyTool("FilterLinkful");
	FilterLinkfulDummy.Init();
	FilterLinkfulDummy.AddAfter("MenuFiltersTopAnchor");
	


	// viewers sub-menu
	var ViewDummyD = new ViewDummyTool("ViewD");
	ViewDummyD.ToolName = "view song genre";
	ViewDummyD.Init();
	ViewDummyD.AddAfter("MenuViewersMiddleAnchor");

	var ViewDummyC = new ViewDummyTool("ViewC");
	ViewDummyC.ToolName = "view song year";
	ViewDummyC.Init();
	ViewDummyC.AddAfter("MenuViewersMiddleAnchor");

	var ViewDummyB = new ViewDummyTool("ViewB");
	ViewDummyB.ToolName = "view song date";
	ViewDummyB.Init();
	ViewDummyB.AddAfter("MenuViewersMiddleAnchor");

	var ViewDummyA = new ViewDummyTool("ViewA");
	ViewDummyA.ToolName = "view artists";
	ViewDummyA.Init();
	ViewDummyA.AddAfter("MenuViewersMiddleAnchor");

	var ViewAsListDummy = new ViewAsListDummyTool("ViewElements");
	ViewAsListDummy.Init();
	ViewAsListDummy.AddAfter("MenuViewersTopAnchor");

	var ViewElementsDummy = new ViewElementsDummyTool("ViewElements");
	ViewElementsDummy.Init();
	ViewElementsDummy.AddAfter("MenuViewersTopAnchor");

	var ViewLinksDummy = new ViewLinksDummyTool("ViewLinks");
	ViewLinksDummy.Init();
	ViewLinksDummy.AddAfter("MenuViewersTopAnchor");


	// plugins
	var aaa = "<a id='MenuPlugins_LocalFileLoader_LoadPrevFile' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:LocalFileLoader.LoadPreviousFile();' class='MenuPlugins'><img src='index/img/folder-open-bold-gray.png' style='margin-top:6px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em;'>Load Prev File</span></a>";
	var bbb = "<a id='MenuPlugins_LocalFileLoader_LoadNextFile' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:LocalFileLoader.LoadNextFile();' class='MenuPlugins'><img src='index/img/folder-open-bold-gray.png' style='margin-top:6px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em;'>Load Next File</span></a>";

	_addAfter("MenuPluginsMiddleAnchor", _getElement(bbb));
	_addAfter("MenuPluginsMiddleAnchor", _getElement(aaa));

	var PluginDummyB = new PluginDummyTool("LFLoader");
	PluginDummyB.ToolName = "Local File Loader";
	PluginDummyB.Init();
	PluginDummyB.AddAfter("MenuPluginsTopAnchor");

	var PluginDummyA = new PluginDummyTool("StreamPlayer");
	PluginDummyA.ToolName = "Stream Player";
	PluginDummyA.Init();
	PluginDummyA.AddAfter("MenuPluginsTopAnchor");

	// pick sub-menu
	//javascript:clearPickers();
	var PickRandomY = new PickRandomTool("PickRandom");
	PickRandomY.ToolName = "clear picks";
	PickRandomY.ToolIconGrey = "index/img/x-circle-bold-gray.png";
	PickRandomY.ToolIconBlack = "index/img/x-circle-bold.png";
	PickRandomY.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandomY.AddAfter("MenuPickersTopAnchor");

	//javascript:setPicker_OptionPickMany();
	var PickRandomX = new PickRandomTool("PickRandom");
	PickRandomX.ToolName = "pick many";
	PickRandomX.ToolIconGrey = "index/img/check-circle-bold-gray.png";
	PickRandomX.ToolIconBlack = "index/img/check-circle-bold.png";
	PickRandomX.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandomX.AddAfter("MenuPickersTopAnchor");

	_addAfter("MenuPickersTopAnchor", _getBr("MenuPickers"));
	_addAfter("MenuPickersTopAnchor", _getBr("MenuPickers"));

	var PickRandomD = new PickRandomTool("PickRandom");
	PickRandomD.ToolName = "Favorite";
	PickRandomD.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandomD.AddAfter("MenuPickersTopAnchor");

	var PickRandomC = new PickRandomTool("PickRandom");
	PickRandomC.ToolName = "Visited";
	PickRandomC.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandomC.AddAfter("MenuPickersTopAnchor");

	var PickRandomB = new PickRandomTool("PickRandom");
	PickRandomB.ToolName = "Not Visited";
	PickRandomB.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
	PickRandomB.AddAfter("MenuPickersTopAnchor");

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
