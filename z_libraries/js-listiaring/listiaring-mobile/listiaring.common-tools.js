class CopyTextTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

		//do
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

		//finish
		addLargeTree(articleId);
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
}
class CopyFirstLinkTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

		//do
		document.querySelectorAll('.treedata').forEach(element =>
		{
			let id = element.id;
				if(!id) alert(id);
				if(!namespaces[id]) alert(id);
				if(!namespaces[id].links) alert(namespaces[id]);
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
}
class CopyEntryTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

		//do
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

		//finish
		addLargeTree(articleId);
	}
}
class CopyDescribeEntryTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

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

		//finish
		addLargeTree(articleId);
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
}
class CopyElementsTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

		document.querySelectorAll('.treedata').forEach(element =>
		{
			let spans = element.querySelectorAll('span');
			spans.forEach(span =>
			{
				span.style.cursor = 'pointer';
				span.onclick = (event) =>
				{
					event.stopPropagation();
					this._copycb(span.textContent);
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
					this._copycb(anchor.href);
				};
				highlight_TextPop_WithEvents(anchor);
			});
		});

		//finish
		addLargeTree(articleId);
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
}
class SortAzTool extends ListiaryBranchingTool {					//Sort by text A-Z

	Select() {

		//execute base logic
		super.Select();

		//get new tree version we created in prev step
		var newTree = namespaces[articleId];

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

		//finish
		addLargeTree(articleId);
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
class SortZaTool extends ListiaryBranchingTool {					//Sort by text Z-A

	Select() {

		//execute base logic
		super.Select();

		//get new tree version we created in prev step
		var newTree = namespaces[articleId];

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

		//finish
		addLargeTree(articleId);
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
class SortAzGenericTool extends ListiaryBranchingTool {				//Sort by a given decorator

	DecoratorName = null;

	constructor(decoratorName, instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		if(!decoratorName) return;
		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);
		this.DecoratorName = decoratorName;
	}
	Select() {

		//execute base logic
		super.Select();

		//get new tree version we created in prev step
		var newTree = namespaces[articleId];

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

		//finish
		addLargeTree(articleId);
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
class SortZaGenericTool extends ListiaryBranchingTool {				//Sort by a given decorator

	DecoratorName = null;

	constructor(decoratorName, instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		if(!decoratorName) return;
		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);
		this.DecoratorName = decoratorName;
	}
	Select() {

		//execute base logic
		super.Select();

		//get new tree version we created in prev step
		var newTree = namespaces[articleId];

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

		//finish
		addLargeTree(articleId);
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
class SortByGenericTool extends ListiaryBranchingTool {				//Sort by a given decorator

	DecoratorName = null;

	constructor(decoratorName, instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		if(!decoratorName) return;
		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);
		this.DecoratorName = decoratorName;
	}
	Select() {

		//execute base logic
		super.Select();

		//get new tree version we created in prev step
		var newTree = namespaces[articleId];

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

		//finish
		addLargeTree(articleId);
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
class HighlightTool extends ListiaryBranchingTool {					//Highlight selected entries

	HighlightColor = null;

	constructor(highlightColor, instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		if(!highlightColor) return;
		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);
		this.HighlightColor = highlightColor;
	}
	Select() {

		//execute base logic
		super.Select();

		//draw
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
}
class RemoveHighlightTool extends ListiaryBranchingTool {			//Remove highlight from selected entries

	Select() {

		//execute base logic
		super.Select();

		//draw
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
}
class MassRemoveHighlightTool extends ListiaryBranchingTool {		//Remove highlight from all entries

	Select() {

		//execute base logic
		super.Select();

		//remove highlights
		for(let i = 0; i < namespaces[articleId].items.length; i++)
		{
			var itemId = namespaces[articleId].items[i].id;
			var item = namespaces[itemId];

			if(item && item.decorators && item.decorators["highlight"])
			{
				delete item.decorators["highlight"];
			}
		}

		//draw
		addLargeTree(articleId);

		this.UnSelect();
	}
}
class PickRandomTool extends ListiaryBranchingTool {

	Select() {

		//execute base logic
		super.Select();

		//add
		addLargeTree(articleId);

		//do
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
}
