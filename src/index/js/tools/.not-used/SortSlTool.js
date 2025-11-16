const SortSlTool = {

	//constants
	ToolId: "sorter_sl",
	ToolName: "sort short-long",
	ToolCategory = "sorters";
	ToolIconGrey: "index/img/sort-ascending-bold-gray.png",
	ToolIconBlack: "index/img/sort-ascending-bold.png",
	TriggerId: "MenuSorters_SortSl",
	TriggerClass: "MenuSorters",
	
	
	//DOM items
	TriggerElement: null,
	
	//vars
	ToolMap: null,
	HistoryMap: null,
	ActiveToolsTracker: null,


	Init: function(toolMap, historyMap, activeToolsTracker) {
		
		this.ToolMap = toolMap;
		this.HistoryMap = historyMap;
		this.ActiveToolsTracker = activeToolsTracker;

		this.ToolMap[this.ToolId] = this.TriggerId;
		var html = this._createTriggerElement(this.ToolName, this.ToolIconGrey);
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		this.TriggerElement = tempContainer.firstElementChild;
		tempContainer.remove();
	},
	AddAfter: function(anchorElementId) {
		
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
	},
	Sort: function() {
		
		this.HistoryMap.RecordUsage(this.ToolId);
		this.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ActiveToolsTracker.Set(this.ToolId, this.ToolCategory);
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:SortSlTool.UnSort();";
		
		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };
		
		//do actual change
		//Remove items with type "comment" or "nlcomment" or "empty"
		//Remove undefined items, also
		newTree.items = newTree.items
			.filter(item => item && 
				item.type !== "comment" && 
				item.type !== "nlcomment" && 
				item.type !== "empty");

		//Sort items by length
		newTree.items = newTree.items
			.sort((a, b) => a.text.length - b.text.length);
		
		//Sort into groups
		let shortestList = [];
		let shorterList = [];
		let shortList = [];
		let regularList = [];
		let longList = [];
		let longerList = [];
		let longestList = [];
		let lists = 
		[
			shortestList,  // lists[0]
			shorterList,   // lists[1]
			shortList,      // lists[2]
			regularList,    // lists[3]
			longList,       // lists[4]
			longerList,     // lists[5]
			longestList     // lists[6]
		];

		let idealGroupSize = Math.ceil(newTree.items.length / 7);
		let i = 0; let curlen = 0; 
		let itemsPushedBeforeReachingLimit = 0; 
		let itemsPushedAfterReachingLimit = 0; 
		for(let j = 0; j < 7; j++)
		{
			for(i; i < newTree.items.length && i < idealGroupSize * (j + 1); i++)
			{
				lists[j].push(newTree.items[i]);
				if(newTree.items[i].text.length != curlen)
				{
					curlen = newTree.items[i].text.length;
					itemsPushedBeforeReachingLimit = 1;
				}
				else itemsPushedBeforeReachingLimit++;
			}
			while(i < newTree.items.length && newTree.items[i].text.length == curlen)
			{
				lists[j].push(newTree.items[i]);
				itemsPushedAfterReachingLimit++;
				i++;
			}
			if(j < 6 && itemsPushedAfterReachingLimit > itemsPushedBeforeReachingLimit)
			{
				let moveElements = lists[j].splice(-itemsPushedBeforeReachingLimit);
				lists[j+1].push(...moveElements);
			}
		}

		//Add comments
		let newList = [];
		for(let j = 0; j < 7; j++)
		{
			var firstComment = null;
			if(j == 0) firstComment = this._createCommentElement('Shortest');
			else if(j == 1) firstComment = this._createNlCommentElement('Shorter');
			else if(j == 2) firstComment = this._createNlCommentElement('Short');
			else if(j == 3) firstComment = this._createNlCommentElement('Regular');
			else if(j == 4) firstComment = this._createNlCommentElement('Long');
			else if(j == 5) firstComment = this._createNlCommentElement('Longer');
			else if(j == 6) firstComment = this._createNlCommentElement('Longest');
			if(firstComment != null) newList.push(firstComment);
			for(let i = 0; i < lists[j].length; i++)
			{
				newList.push(lists[j][i]);
			}
		}

		//assign result
		newTree.items = newList;
		
		//add it as version
		var newArticleId = articleId + "~sorted-sl";
		namespaces[newArticleId] = newTree;
		articleId = newArticleId;
		addLargeTree(articleId);
	},
	UnSort: function() {

		this.ActiveToolsTracker.Unset(this.ToolId, this.ToolCategory);
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:SortSlTool.Sort();";
		
		articleId = articleId.split('~')[0];
		addLargeTree(articleId);
	},
	
	
	_createTriggerElement: function(triggerText, triggerIcon) {
		
		var html = "<a id='" + this.TriggerId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:SortSlTool.Sort();' class='" + this.TriggerClass + "'><img src='" + triggerIcon + "' style='margin-top:9px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

		return html;
	},
	_enboldTriggerElement: function() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = 'black';
		menuitemSpan.style.fontStyle = 'italic';
		menuitemSpan.style.fontWeight = 'bold';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconBlack;
	},
	_deboldTriggerElement: function() {
		
		let menuitemSpan = this.TriggerElement.querySelector('span');
		menuitemSpan.style.color = '#818181';
		menuitemSpan.style.fontStyle = 'normal';
		menuitemSpan.style.fontWeight = 'normal';
		let menuitemImg = this.TriggerElement.querySelector('img');
		menuitemImg.src = this.ToolIconGrey;
	},
	_createCommentElement: function(commentText) {
		
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
	},
	_createNlCommentElement: function(commentText) {
		
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
	},
	_generateRandomId: function(length) {

		return Math.random().toString(36).substring(2, 2 + length);
	}
};