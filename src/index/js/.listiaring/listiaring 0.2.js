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
			this.addTools();
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
	addTools: function() {	   						//add to DOM
		
		this.removeTools();
		
		console.log("CurrentTopTools:", this.CurrentTopTools);
		console.log("Type:", Object.prototype.toString.call(this.CurrentTopTools));
		console.log("this in addTools:", this);
		console.log("Forced Array Check:", Array.isArray([...this.CurrentTopTools]));
		console.log("Before forEach");

		this.CurrentTopTools.forEach(key => 
		{
			console.log("Inside forEach - should not be skipped");
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
		console.log("After forEach");
	}
};

// Need to redesign this class.
var ActiveToolsTracker = {
	
	highlighters: [],
	sorters: [],
	copiers: [],
	pickers: [],
	viewers: [],
	filters: [],
	

	
	Set: function(ToolId, category) {
		
		if(!category) return;
		if(!this[category]) return;
		
		this[category].push(ToolId);
		ToolUsageHistory.addTools();
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
		
		this[category] = [];
	}
	UnsetAll: function() {
		
		this.highlighters = [];
		this.sorters = [];
		this.copiers = [];
		this.pickers = [];
		this.viewers = [];
		this.filters = [];
	}
}


//tools
class CopyTextTool {

	//constants
	ToolId = "copier_text";
	ToolUrlId = "~copy-text";
	ToolName = "Copy Text";
	ToolIconGrey = "index/img/copy-bold-gray.png";
	ToolIconBlack = "index/img/copy-bold.png";
	TriggerId = "MenuCopiers_CopyText";
	TriggerClass = "MenuCopiers";
	
	//DOM items
	TriggerElement = null;
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			// let spans = element.querySelectorAll('span');
			// spans.forEach(span => 
			// {
				// if(text != "") text += " ";
				// text += span.innerText;
				
				// span.style.cursor = 'pointer';
				// span.onclick = (event) => 
				// {
					// event.stopPropagation();
					// this._copycb(text);
				// }
				// highlight_TextPop_WithEvents(span);
			// });
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
					this._copycb(text);
				}
				highlight_TextPop_WithEvents(anchor);
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId);
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
class CopyFirstLinkTool {

	//constants
	ToolId = "copier_flink";
	ToolUrlId = "~copy-flink";
	ToolName = "Copy First Link";
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
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			// let spans = element.querySelectorAll('span');
			// spans.forEach(span => 
			// {
				// if(text != "") text += " ";
				// text += span.innerText;
				
				// span.style.cursor = 'pointer';
				// span.onclick = (event) => 
				// {
					// event.stopPropagation();
					// this._copycb(text);
				// }
				// highlight_TextPop_WithEvents(span);
			// });
			let anchors = element.querySelectorAll('a');
			let text = "";
			anchors.forEach(anchor => 
			{
				text = anchor.href;
				
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

		this.ActiveToolsTracker.Unset(this.ToolId);
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
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			let spans = element.querySelectorAll('span');
			let anchor = element.querySelector('a');
			let text = "";
			spans.forEach(span => 
			{
				if(text != "") text += " ";
				text += span.innerText;
				
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					copycb(text);
				}
				highlight_TextPop_WithEvents(span);
				
			});
			
			let anchors = element.querySelectorAll('a');
			anchors.forEach(anchor => 
			{
				if(text != "") text += "\n" + anchor.href;
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId);
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
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			let spans = element.querySelectorAll('span');
			let anchor = element.querySelector('a');
			let text = "";
			spans.forEach(span => 
			{
				if(text != "") text += " ";
				text += span.innerText;
				
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					copycb(text);
				}
				highlight_TextPop_WithEvents(span);
				
			});
			
			let anchors = element.querySelectorAll('a');
			anchors.forEach(anchor => 
			{
				if(text == "") text += anchor.innerText;
				text += " [" + anchor.href + "]";
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId);
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
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		
		document.querySelectorAll('.treedata').forEach(element => 
		{
			// let spans = element.querySelectorAll('span');
			// spans.forEach(span => 
			// {
				// if(text != "") text += " ";
				// text += span.innerText;
				
				// span.style.cursor = 'pointer';
				// span.onclick = (event) => 
				// {
					// event.stopPropagation();
					// this._copycb(text);
				// }
				// highlight_TextPop_WithEvents(span);
			// });
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
					this._copycb(text);
				}
				highlight_TextPop_WithEvents(anchor);
			});
		});
		
		
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

		this.ActiveToolsTracker.Unset(this.ToolId);
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
	ToolIconGrey = "index/img/sort-ascending-bold-gray.png";
	ToolIconBlack = "index/img/sort-ascending-bold.png";
	TriggerId = "MenuSorters_SortAz";
	TriggerClass = "MenuSorters";
	
	
	//DOM items
	TriggerElement = null;
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;
	
	
	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Sort() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetAll();
		this.ActiveToolsTracker.Set(this.ToolId);
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

		this.ActiveToolsTracker.Unset(this.ToolId);
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
class SortZaTool {

	//constants
	ToolId = "sorter_za";
	ToolUrlId = "~sorted-za";
	ToolName = "Sort Z-A";
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


	constructor(instanceName) {
		
		this.InstanceName = instanceName;
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
	Sort() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetAll();
		this.ActiveToolsTracker.Set(this.ToolId);
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

		this.ActiveToolsTracker.Unset(this.ToolId);
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
		this.ToolUrlId = "~sorted_" + decoratorName.toLowerCase() + "_az";
		this.ToolName = "by " + toolName + " a-z";
		this.TriggerId = "MenuSorters_Sort" + menuName + "Az",
		this.DecoratorName = decoratorName;
		this.InstanceName = instanceName;
		this.IsSet = true;
	}
	Init(toolMap, historyMap, activeToolsTracker) {
		
		if(this.IsSet == false) return;
		
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
		this.ActiveToolsTracker.UnsetAll();
		this.ActiveToolsTracker.Set(this.ToolId);
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
		
		this.ActiveToolsTracker.Unset(this.ToolId);
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
		this.ToolName = "by " + toolName + " z-a";
		this.TriggerId = "MenuSorters_Sort" + menuName + "Za",
		this.DecoratorName = decoratorName;
		this.InstanceName = instanceName;
		this.IsSet = true;
	}
	Init(toolMap, historyMap, activeToolsTracker) {
		
		if(this.IsSet == false) return;
		
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
		this.ActiveToolsTracker.UnsetAll();
		this.ActiveToolsTracker.Set(this.ToolId);
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
		
		this.ActiveToolsTracker.Unset(this.ToolId);
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
		this.ToolName = "by " + toolName;
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
		this.ActiveToolsTracker.UnsetAll();
		this.ActiveToolsTracker.Set(this.ToolId);
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
		
		this.ActiveToolsTracker.Unset(this.ToolId);
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
	ToolIconGrey = "index/img/pencil-simple-line-bold-gray.png";
	ToolIconBlack = "index/img/pencil-simple-line-bold.png";
	TriggerId = "MenuCopiers_HighlightElements";
	TriggerClass = "MenuCopiers";
	HighlightColor = null;
	
	//DOM items
	TriggerElement = null;
	
	//vars
	ToolMap = null;
	HistoryMap = null;
	ActiveToolsTracker = null;
	InstanceName = null;



	constructor(instanceName, color, triggerClass = null) {
		
		this.InstanceName = instanceName;
		this.HighlightColor = color;
		var n = color.trim().toLowerCase();
		n = n[0].toUpperCase() + n.slice(1);
		this.ToolName = this.ToolName + " " + n;
		
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
	Select() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		//this.ActiveToolsTracker.UnsetAll();  Unset all copiers
		this.ActiveToolsTracker.Set(this.ToolId);
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
					highlight_Text(span, this.HighlightColor);
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
				}
			});
		});
	}
	UnSelect() {

		this.ActiveToolsTracker.Unset(this.ToolId);
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
}



//act

//highlight sub-menu
//https://htmlcolorcodes.com/color-names/
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


var HighlightHotPink = new HighlightTool("HighlightHotPink", "HotPink", "MenuHighlighters");
HighlightHotPink.ToolName = "HotPink";
HighlightHotPink.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightHotPink.AddAfter("MenuHighlightersMiddleAnchor");

var HighlightHotOrange = new HighlightTool("HighlightHotOrange", "Orange", "MenuHighlighters");
HighlightHotOrange.ToolName = "Orange";
HighlightHotOrange.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightHotOrange.AddAfter("MenuHighlightersMiddleAnchor");

var HighlightTurquoise = new HighlightTool("HighlightTurquoise", "Turquoise", "MenuHighlighters");
HighlightTurquoise.ToolName = "Turquoise";
HighlightTurquoise.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightTurquoise.AddAfter("MenuHighlightersMiddleAnchor");

var HighlightSilver = new HighlightTool("HighlightSilver", "Silver", "MenuHighlighters");
HighlightSilver.ToolName = "Silver";
HighlightSilver.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightSilver.AddAfter("MenuHighlightersMiddleAnchor");

var HighlightDimGray = new HighlightTool("HighlightDimGray", "DimGray", "MenuHighlighters");
HighlightDimGray.ToolName = "DimGray";
HighlightDimGray.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightDimGray.AddAfter("MenuHighlightersMiddleAnchor");

var HighlightBlack = new HighlightTool("HighlightBlack", "Black", "MenuHighlighters");
HighlightBlack.ToolName = "Black";
HighlightBlack.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightBlack.AddAfter("MenuHighlightersMiddleAnchor");



//copy sub-menu
var HighlightBlue = new HighlightTool("HighlightBlue", "Blue");
HighlightBlue.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightBlue.AddAfter("MenuCopiersMiddleAnchor");
HighlightBlue.HighlightColor = "LightSteelBlue";

var HighlightGreen = new HighlightTool("HighlightGreen", "Green");
HighlightGreen.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightGreen.AddAfter("MenuCopiersMiddleAnchor");
HighlightGreen.HighlightColor = "Chartreuse";

var HighlightYellow = new HighlightTool("HighlightYellow", "Yellow");
HighlightYellow.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
HighlightYellow.AddAfter("MenuCopiersMiddleAnchor");

var CopyDescribeEntry = new CopyDescribeEntryTool("CopyDescribeEntry");
CopyDescribeEntry.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
CopyDescribeEntry.AddAfter("MenuCopiersTopAnchor");

var CopyEntry = new CopyEntryTool("CopyEntry");
CopyEntry.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
CopyEntry.AddAfter("MenuCopiersTopAnchor");

var CopyElements = new CopyElementsTool("CopyElements");
CopyElements.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
CopyElements.AddAfter("MenuCopiersTopAnchor");

var CopyFirstLink = new CopyFirstLinkTool("CopyFirstLink");
CopyFirstLink.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
CopyFirstLink.AddAfter("MenuCopiersTopAnchor");

var CopyText = new CopyTextTool("CopyText");
CopyText.Init(menuItemIds, ToolUsageHistory, ActiveToolsTracker);
CopyText.AddAfter("MenuCopiersTopAnchor");



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

ToolUsageHistory.addTools();