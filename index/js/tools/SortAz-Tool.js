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
