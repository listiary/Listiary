var menuItemIds = { 

	"copier_elements": 		"MenuCopiers_CopyElements",
	"copier_flink": 		"MenuCopiers_CopyFLink",
	"copier_entries": 		"MenuCopiers_CopyEntries",
	"copier_entries_ds": 	"MenuCopiers_CopyEntriesDs",
	
	"picker_random":		"MenuPickers_PickRandom",
};
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
	
	UnsetAll: function() {
		
		for (let i = 0; i < sorters.length; i++) 
		{
			var key = sorters[i];
			if(key == "sorter_az") unsetSorterer_Az();
			if(key == "sorter_za") unsetSorterer_Za();
			if(key == "sorter_sl") unsetSorterer_ShortLong();
			if(key == "sorter_ls") unsetSorterer_LongShort();
			if(sorters.includes(key)) removeSorterKey(key);
		}
	},
	Set: function(ToolId) {
		
		sorters.push(ToolId);
		ToolUsageHistory.addTools();
	},
	Unset: function(toolId) {
		
		if (sorters.includes(toolId)) 
		{
			sorters = sorters.filter(item => item !== toolId);
		}
	}
}



//set highlighting
function highlight_DataElements(color = 'gray') {
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		element.style.backgroundColor = color;
	});
}
function highlight_Text(element, color = 'yellow') {
	
	element.style.backgroundImage = "linear-gradient(to right, " + color + " 50%, " + color + " 50%)";
	element.style.backgroundSize = "100% 60%"; // Controls thickness
	element.style.backgroundPosition = "0 85%"; // Moves it below text
	element.style.backgroundRepeat = "no-repeat";
}
function highlight_TextPop(element, color = 'yellow') {

    // Mouse enter: apply highlight
    element.addEventListener("mouseenter", () => {
        element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
        element.style.backgroundSize = "100% 60%"; // Highlight thickness
        element.style.backgroundPosition = "0 85%"; // Position below text
        element.style.backgroundRepeat = "no-repeat";
    });

    // Mouse leave: remove highlight
    element.addEventListener("mouseleave", () => {
        element.style.backgroundImage = "none"; // Remove highlight
    });

    // Mouse click: blink the highlight
    element.addEventListener("click", () => {
        
		// Remove the highlight briefly
        element.style.backgroundImage = "none";

        // Add the highlight back after 500ms (you can adjust this time)
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay (adjustable)
    });
}
function highlight_TextPop_WithEvents(element, color = 'yellow') {

    // Create event handler functions
    const mouseEnterHandler = () => {
        element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
        element.style.backgroundSize = "100% 60%"; // Highlight thickness
        element.style.backgroundPosition = "0 85%"; // Position below text
        element.style.backgroundRepeat = "no-repeat";
    };

    const mouseLeaveHandler = () => {
        element.style.backgroundImage = "none"; // Remove highlight
    };

    const clickHandler = () => {
        element.style.backgroundImage = "none";  // Remove the highlight briefly
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay
    };

    // Add event listeners
    element.addEventListener("mouseenter", mouseEnterHandler);
    element.addEventListener("mouseleave", mouseLeaveHandler);
    element.addEventListener("click", clickHandler);

    // To remove the event listeners when necessary:
    element.removeEventListeners = () => {
        element.removeEventListener("mouseenter", mouseEnterHandler);
        element.removeEventListener("mouseleave", mouseLeaveHandler);
        element.removeEventListener("click", clickHandler);
    };
}
function highlight_TextFadeTopBottom(element, color = 'yellow') {

	element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundSize = "100% 0%"; // Start with no highlight
    element.style.backgroundPosition = "0 85%"; // Below text
    element.style.backgroundRepeat = "no-repeat";
    element.style.transition = "background-size 0.3s ease-in-out"; // Smooth animation

    element.addEventListener("mouseenter", () => {
        element.style.backgroundSize = "100% 60%"; // Expand highlight
    });

    element.addEventListener("mouseleave", () => {
        element.style.backgroundSize = "100% 0%"; // Shrink highlight
    });
}
function highlight_TextFadeMiddle(element, color = 'yellow') {

	// Initial setup
    element.style.position = "relative";
    element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundRepeat = "no-repeat";
    element.style.transition = "background-size 0.3s ease-in-out, background-position 0.3s ease-in-out";
    element.style.backgroundSize = "0% 60%";   // Start with no width
    element.style.backgroundPosition = "0 85%";  // Anchored on left

    element.addEventListener("mouseenter", () => {
        // Grow the highlight from left to right
        element.style.backgroundPosition = "0 85%";  // Anchor on left
        element.style.backgroundSize = "100% 60%";     // Expand to full width
    });

    element.addEventListener("mouseleave", () => {
        // First, shift the highlight so its right edge is anchored
        element.style.backgroundPosition = "100% 85%";
        // Then, shrink its width back to 0%
        // (A short delay can help ensure the position change is registered before the size transition starts.)
        setTimeout(() => {
            element.style.backgroundSize = "0% 60%";
        }, 10);
    });
}
function highlightPermanent_TextPop_WithEvents(element, color = 'yellow') {

    // Create event handler functions
    element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundSize = "100% 60%"; // Highlight thickness
    element.style.backgroundPosition = "0 85%"; // Position below text
    element.style.backgroundRepeat = "no-repeat";

    const mouseLeaveHandler = () => {
        element.style.backgroundImage = "none"; // Remove highlight
    };

    const clickHandler = () => {
        element.style.backgroundImage = "none";  // Remove the highlight briefly
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay
    };

    // Add event listeners
    element.addEventListener("click", clickHandler);

    // To remove the event listeners when necessary:
    element.removeEventListeners = () => {
        element.removeEventListener("click", clickHandler);
    };
}


//copiers
var copiers = [];
function removeCopierKey(target) {
	
    if (copiers.includes(target)) 
	{
        copiers = copiers.filter(item => item !== target);
    }
}
function unsetAllCopiers() {
	
	for (let i = 0; i < copiers.length; i++) 
	{
		var key = copiers[i];
		if(key == "copier_elements") unsetCopier_Elements();
		if(key == "copier_flink") unsetCopier_Flink();
		if(key == "copier_entries") unsetCopier_Entries();
		if(key == "copier_entries_ds") unsetCopier_EntriesDs();
		if(copiers.includes(key)) removeCopierKey(key);
	}
}
function setCopier_Elements() {
	
	//this function's id
	let fn = "copier_elements"; recordUsage(fn);
	unsetAllCopiers();
	copiers.push(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	//highlight_Text(menuitemSpan);
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold.png';
	menuitem.href = "javascript:unsetCopier_Elements();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
        spans.forEach(span => 
		{
            span.style.cursor = 'pointer';
            span.onclick = (event) => 
			{
				copycb(span.textContent);
			}
			highlight_TextPop_WithEvents(span);
		});
		
		let anchors = element.querySelectorAll('a');
		anchors.forEach(anchor => 
		{
			anchor.onclick = (event) => 
			{
                copycb(anchor.href); 
                event.preventDefault();
            };
			highlight_TextPop_WithEvents(anchor);
        });
	});
}
function unsetCopier_Elements() {
	
	//this function's id
	let fn = "copier_elements";
	removeCopierKey(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold-gray.png';
	menuitem.href = "javascript:setCopier_Elements();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
        spans.forEach(span => 
		{
            span.style.cursor = 'auto';
            span.onclick = null;
			span.removeEventListeners();
		});
		
		let anchors = element.querySelectorAll('a');
		anchors.forEach(anchor => 
		{
			anchor.onclick = null;
			anchor.removeEventListeners();
        });
	});
}
function setCopier_Flink() {
	
	//this function's id
	let fn = "copier_flink"; recordUsage(fn);
	unsetAllCopiers();
	copiers.push(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	//highlight_Text(menuitemSpan);
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold.png';
	menuitem.href = "javascript:unsetCopier_Flink();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
		let anchor = element.querySelector('a');
        spans.forEach(span => 
		{
            span.style.cursor = 'pointer';
            span.onclick = (event) => 
			{
				copycb(anchor.href);
			}
			highlight_TextPop_WithEvents(span);
		});
	});
}
function unsetCopier_Flink() {
	
	//this function's id
	let fn = "copier_flink";
	removeCopierKey(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold-gray.png';
	menuitem.href = "javascript:setCopier_Flink();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
        spans.forEach(span => 
		{
            span.style.cursor = 'auto';
            span.onclick = null;
			span.removeEventListeners();
		});
	});
}
function setCopier_Entries() {
	
	//this function's id
	let fn = "copier_entries"; recordUsage(fn);
	unsetAllCopiers();
	copiers.push(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	//highlight_Text(menuitemSpan);
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold.png';
	menuitem.href = "javascript:unsetCopier_Entries();";
	
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
			text += "\n" + anchor.href;
        });
	});
}
function unsetCopier_Entries() {
	
	//this function's id
	let fn = "copier_entries";
	removeCopierKey(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold-gray.png';
	menuitem.href = "javascript:setCopier_Entries();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
        spans.forEach(span => 
		{
            span.style.cursor = 'auto';
            span.onclick = null;
			span.removeEventListeners();
		});
	});
}
function setCopier_EntriesDs() {
	
	//this function's id
	let fn = "copier_entries_ds"; recordUsage(fn);
	unsetAllCopiers();
	copiers.push(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	//highlight_Text(menuitemSpan);
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold.png';
	menuitem.href = "javascript:unsetCopier_EntriesDs();";
	
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
			text += " [" + anchor.href + "]";
        });
	});
}
function unsetCopier_EntriesDs() {
	
	//this function's id
	let fn = "copier_entries_ds";
	removeCopierKey(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/copy-bold-gray.png';
	menuitem.href = "javascript:setCopier_EntriesDs();";
	
	document.querySelectorAll('.treedata').forEach(element => 
	{
		let spans = element.querySelectorAll('span');
        spans.forEach(span => 
		{
            span.style.cursor = 'auto';
            span.onclick = null;
			span.removeEventListeners();
		});
	});
}


//sorters
var sorters = [];


//filters
var filters = [];
function removeFilterKey(target) {
	
    if (pickers.includes(target)) 
	{
        pickers = pickers.filter(item => item !== target);
    }
}


//pickers
var pickers = [];
function removePickerKey(target) {
	
    if (pickers.includes(target)) 
	{
        pickers = pickers.filter(item => item !== target);
    }
}
function unsetAllPickers() {
	
	for (let i = 0; i < pickers.length; i++) 
	{
		var key = pickers[i];
		if(key == "picker_random") unsetPicker_Random();
		if(pickers.includes(key)) removeSorterKey(key);
	}
}
function setPicker_Random() {
	
	//this function's id
	let fn = "picker_random"; recordUsage(fn);
	unsetAllPickers();
	copiers.push(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/arrow-fat-down-bold.png';
	menuitem.href = "javascript:unsetPicker_Random();";
	
	const elements = Array.from(document.querySelectorAll('.treedata'));
	const filteredElements = elements.filter(element => // remove comments and empty ones
	{
		const style = window.getComputedStyle(element);
		const isEmpty = element.querySelector('span') === null;
		const isComment = (style.color === 'green' && style.fontStyle === 'italic');
  
		return !(isEmpty || isComment);
	});
	const randomElement = filteredElements[Math.floor(Math.random() * filteredElements.length)];
	randomElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
	let spans = randomElement.querySelectorAll('span');
    spans.forEach(span => 
	{
		span.onclick = (event) => 
		{
			copycb(span.textContent);
		}
        span.style.cursor = 'pointer';
		highlightPermanent_TextPop_WithEvents(span);
	});
}
function unsetPicker_Random() {
	
	//this function's id
	let fn = "picker_random";
	removePickerKey(fn);
	
	let menuitem = document.querySelector('#' + menuItemIds[fn]);
	let menuitemSpan = menuitem.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = menuitem.querySelector('img');
	menuitemImg.src = 'index/img/sort-ascending-bold-gray.png';
	menuitem.href = "javascript:setPicker_Random();";
	
	addLargeTree(articleId);
}



//methods
function copycb(text) { //Copy text to clipboard
	
    navigator.clipboard.writeText(text).then(() => 
	{
        //console.log('Copied:', text);
    })
	.catch(err => 
	{
        console.error('Failed to copy:', err);
    });
}
function isAlphaNumeric_Regex(letter) {
	
	return /^[a-zA-Z0-9]$/.test(letter);
}
function isAlphaNumeric_Ranges(letter) {
	
	const code = letter.charCodeAt(0);
    return (
        (code >= 48 && code <= 57) || // 0-9
        (code >= 65 && code <= 90) || // A-Z
        (code >= 97 && code <= 122)   // a-z
    );
}


//tools
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


//act
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