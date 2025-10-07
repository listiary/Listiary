const mainTree = {


	//fields
	Color: "white",
	Titler: null,
	Container: null,
	availableWidth: 0,
	isMobile: false,
	overlapType: "links",				//"dots" | "multyline" | "basic" | "links"


	//public methods
	Init: function(treeTitleContainer, treeContainer){

		mainTree.Titler = treeTitleContainer;
		mainTree.Container = treeContainer;
		mainTree.availableWidth = mainTree.getAvailableWidth();
	},
	Draw: function(id){

		if (mainTree.isMobile) mainTree.drawMobile(id);
		else mainTree.drawMobile(id);
		
		//init plugins
		StreamPlayer.AddPlayer();
	},
	
	
	//private methods drawing
	draw: function(id){
		
		document.getElementById("BottomToolbar").style.display = "none";

		articleId = id;
		mainTree.isMobile = false;
		var obj = namespaces[id];
		mainTree.setUrl(id);
		

		var filename = namespaces[id].filename;
		//if(mode === "personal") filename = user + '.' + filename;
		//else if(mode === "private") filename = user + '.' + filename;

		var title = "<h2 style='margin-top: -25px; margin-bottom: 10px;'>" + obj.text + " <span style='text-decoration: none; vertical-align: super; position: relative; top: 0.2em; font-size: 70%; color: blue;'>[<a style='font-style: italic;' href='javascript:showEditor(\"" + filename + "\",\"" + obj.id + "\");'>edit</a>]</span></h2>";

		document.getElementById("BottomToolbarEditButton").onclick = function() { showEditor(filename, obj.id); };


		var prodHtml = mainTree.translateBaseProductionShallow(obj);
		mainTree.Container.innerHTML = title + prodHtml;
		//console.log("Tree drawn: " + obj.id);
		
		mainTree.drawBreadcrumb_Compacted(id);
		
		setTimeout(() => {
			if (document.documentElement.scrollHeight > document.documentElement.clientHeight)
			{
				document.getElementById("BottomToolbar").style.display = "table-cell";
				document.getElementById("BottomToolbarEditButton").onclick = function() { showEditor(filename, obj.id); };
			}}, 800);
	},
	drawMobile: function(id){

		document.getElementById("BottomToolbar").style.display = "none";

		articleId = id;
		mainTree.isMobile = true;
		var obj = namespaces[id];
		mainTree.setUrl(id);

		var filename = namespaces[id].filename;
		//if(mode === "personal") filename = user + '.' + filename;
		//else if(mode === "private") filename = user + '.' + filename;
		
		var title = "<h2 style='margin-top: -25px; margin-bottom: 10px;'>" + obj.text + " <span style='text-decoration: none; vertical-align: super; position: relative; top: 0.2em; font-size: 70%; color: blue;'>[<a style='font-style: italic;' href='javascript:showEditor(\"" + filename + "\",\"" + obj.id + "\");'>edit</a>]</span></h2>";
		
		var prodHtml = mainTree.translateBaseProductionShallow(obj);
		mainTree.Container.innerHTML = title + prodHtml;
		//console.log("Tree drawn: " + obj.id);
		
		mainTree.drawBreadcrumb_Compacted(id);
		//highlight_DataElements();
		
		setTimeout(() => {
			if (document.documentElement.scrollHeight > document.documentElement.clientHeight + 200)
			{
				document.getElementById("BottomToolbar").style.display = "table-cell";
				document.getElementById("BottomToolbarEditButton").onclick = function() { showEditor(filename, obj.id); };
			}}, 800);
	},
	drawBreadcrumb: function(id) {
		
		var cur = namespaces[id];
		var breadCrumb = "";
		while (cur.parentItem != undefined) {
			
			cur = cur.parentItem;
			if (breadCrumb.length > 1) 
			{
				breadCrumb = "<span class=\"breadcrumbmain\"> . </span>" + breadCrumb;
			}
			breadCrumb = 
				"<a class=\"breadcrumbmain\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + cur.id + "\');\">" + cur.text + "</a>" + breadCrumb;
		}
		if (breadCrumb.length == 0)
		{
			breadCrumb = "<a class=\"breadcrumbmain\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + cur.id + "\');\">" + cur.text + "</a>";
		}
		
		//add html
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
			html += "<div style='font-size: 25px; margin: 7px; margin-left: 10px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;'><div style='margin-right:35px;'>"
			html += breadCrumb;
			html += "</div><hr /></div>";
			html += "</div>";

		mainTree.Titler.innerHTML = html;
	},
	drawBreadcrumb_Compacted: function(id) {
		
		var cur = namespaces[id];
		var breadCrumb = "";
		var isfirst = 1;
		var hasDot = false;
		while (cur.parentItem != undefined) {
			
			cur = cur.parentItem;
			if(isfirst == 1)
			{
				breadCrumb = "<a class=\"breadcrumbmain\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + cur.id + "\');\">" + cur.text + "</a>" + breadCrumb;
				isfirst = 0;
			}
			else
			{
				breadCrumb = "<span class=\"breadcrumbmain\" style='display: none;'> . </span>" + breadCrumb;
				breadCrumb = "<a class=\"breadcrumbmain\" style='display: none;' href=\"javascript:reloadJson(\'" + mode + "\',\'" + cur.id + "\');\">" + cur.text + "</a>" + breadCrumb;
				hasDot = true;
			}
		}
		if (breadCrumb.length == 0)
		{
			breadCrumb = "<a class=\"breadcrumbmain\" href=\"javascript:mainTree.Draw(\'" +
				cur.id + "\');\">" + cur.text + "</a>";
		}
		else if (hasDot == true)
		{
			breadCrumb = "<span id='breadcrumbmain_expander_dot' class=\"breadcrumbmain\"> . </span>" + breadCrumb;
			breadCrumb = "<span id='breadcrumbmain_expander' style='vertical-align: 0px; font-size:0.5em; background: black; color: " + this.Color + "; border-radius: 25px; padding-left: 4px; padding-right: 4px; font-weight: 700; cursor: pointer;' onclick='mainTree.expandBreadcrumb();'>•••</span>" + breadCrumb;
		}
		
		//add html
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-size: 25px; margin: 7px; margin-left: 15px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;'><div style='margin-right:35px;'>"
		html += breadCrumb;
		html += "</div><hr /></div>";
		html += "</div>";

		mainTree.Titler.innerHTML = html;
	},
	expandBreadcrumb: function() {

		document.querySelectorAll('.breadcrumbmain').forEach(element => {
			element.style.display = 'inline';
		});
		document.querySelector("#breadcrumbmain_expander").style.display = 'none';
		document.querySelector("#breadcrumbmain_expander_dot").style.display = 'none';
	},
	getAvailableWidth: function() {
		
		var ul = mainTree.Container.querySelector('.mainlistree');
		var ulRect = ul.getBoundingClientRect();
		var style = mainTree.getStyle(ul);
		
		var availableWidth = document.body.clientWidth - ulRect.left;
		var ml = parseFloat(style.marginLeft);
		var mr = parseFloat(style.marginRight);
		var pl = parseFloat(style.paddingLeft);
		var pr = parseFloat(style.paddingRight);
		
		availableWidth = availableWidth - ml - mr;
		availableWidth = availableWidth - pl - pr;
		return availableWidth;
	},
	getStyle: function(el) {
		
		var style = el.currentStyle || window.getComputedStyle(el);
		return style;
	},
	setUrl: function(id) {
		
		var cur = namespaces[id];
		var args = "";
		
		if (mode != "public" && mode != "personal" && mode != "private" && mode != "normative") mode = "public"; 
		if (mode != "public") args = "?domain=" + mode + "&article=" + id;
		else args = "?article=" + id;
		
		var documentTitle = cur.text.charAt(0).toUpperCase() + cur.text.slice(1);
		if(documentTitle.toLowerCase() == "radiowatch") documentTitle = "Home";
		documentTitle += " | Radiowatch";
		if(documentTitle.length > 60) 
		{
			documentTitle = documentTitle.substring(0, 56);
			documentTitle += " ...";
		}
		document.title = documentTitle;
		//window.history.replaceState("", documentTitle, args);
		window.history.pushState("", documentTitle, args);
		
		
		//add data for the history deamon
		//historyDeamon.articleArguments = args;
		//historyDeamon.articleTitle = documentTitle;
		//historyDeamon.articleLastChangedTime = new Date();
	},


	//Translate main production
	translateBaseProductionShallow: function(item) {

		if (item.name == "branch") {
			return "<ul class='mainlistree'>" + mainTree.translateProductionShallow(item) + "</ul>";
		}
		else if (item.name == "leaf") {
			console.log('FATAL: "' + item.name + '" is not branch');
			return;
		}
		else {
			console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
			return;
		}
	},
	translateProductionShallow: function(item) {
		
		//translate items
		var items = "";
		for (var i = 0; i < item.items.length; i++) {
			if(options.CensoredNamespaces.includes(item.items[i].id)) continue;    //CENSOR NAMESPACES
			items += mainTree.translateItemShallow(item.items[i]);
		}

		//translate links
		var links = " ";
		for (var i = 0; i < item.links.length; i++) {
		
			links += mainTree._makeTitleLink(item.links[i].url, item.links[i].text);
		}
		
		//make the main production
		if (item.color != undefined) 
		{
			return mainTree._makeMainProductionBody_Colored(item.id, links, items, item.color);
		}
		else 
		{
			return mainTree._makeMainProductionBody(item.id, links, items);
		}
	},
	_makeTitleLink: function(url, text) {

		var iswikipedia = mainTree._isWikipediaLink(url);
		if (iswikipedia)
		{
			return "<a target='_blank' style='color:green; text-decoration: none' href='" +
			url + "'>🅦</a>";
		}
		else
		{
			return "<a target='_blank' style='text-decoration: none' href='" +
			url + "'>" + text + "</a>";
		}
	},
	_isWikipediaLink: function(url) {

		const wikiRegex = /^(https?:\/\/)?(www\.)?[a-z]{2}\.wikipedia\.org\/?/i;
		return wikiRegex.test(url);
	},
	_makeMainProductionBody: function(id, titleLinksHtml, itemsHtml) {
		
		return "<li><div class='mainlistree-submenu-heading' id='" + id + "'>" +
			titleLinksHtml +
			"</div><ul class='mainlistree-submenu-items'><br id='listTopSpace'/>" +
			itemsHtml +
			"<br /></ul></li>";
	},
	_makeMainProductionBody_Colored: function(id, titleLinksHtml, itemsHtml, color) {
		
		return "<li><div style='color: " +
			color +
			";'  class='mainlistree-submenu-heading' id='" + id + "'>" +
			titleLinksHtml +
			"</div><ul class='mainlistree-submenu-items'><br id='listTopSpace'/>" +
			itemsHtml +
			"<br /></ul></li>";
	},
	
	
	//Translate items in the main production
	translateItemShallow: function(item) {
	
		if(mainTree.overlapType == "basic") return mainTree.translateItemShallow_Basic(item);
		else if(mainTree.overlapType == "dots") return mainTree.translateItemShallow_Dots(item);
		else if(mainTree.overlapType == "links") return mainTree.translateItemShallow_Link(item);
		//else if(mainTree.overlapType == "multyline") return mainTree.translateItemShallow_Multyline(item);	//Not implemented yet
		else return mainTree.translateItem_Basic(item);
	},
	translateItemShallow_Basic: function(item) {
	
		if (item.name == "branch") {

			var links = mainTree.translateLinks(item);
			var itext = mainTree.choosever(item);
			if (item.color != undefined) 
			{
				return mainTree._makeBranchItem_Basic_Colored(item.id, itext, links, maintree.availablewidth, item.color);
			}
			else 
			{
				return mainTree._makeBranchItem_Basic(item.id, itext, links, mainTree.availableWidth);
			}
		}
		else if (item.name == "leaf") 
		{
			return mainTree.translateItem(item);
		}
		else
		{
			console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
			return;
		}
	},
	translateItemShallow_Dots: function(item) {
	
		// This is the overflowing item template
		// <li style="display: flex; align-items: center; max-width: max-content; overflow: hidden;">
		//	<span style="flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
		//		Blue - Sorry Seems To Be The Hardest Word (Radio Edit) (Feat. Elton John)
		//	</span>
		//	<a target="_blank" style="text-decoration: none; flex-shrink: 0; margin-left: 5px;" href="https://youtube.com/watch?v=ZaJ_ub9ps58">🅐</a>
		// </li>
	
		if (item.name == "branch") {

			var links = mainTree.translateLinks(item);
			var itext = mainTree.choosever(item);
			if (item.color != undefined) 
			{
				return mainTree._makeBranchItem_Dots_Colored(item.id, itext, links, item.color);
			}
			else 
			{
				return mainTree._makeBranchItem_Dots(item.id, itext, links);
			}
		}
		else if (item.name == "leaf") 
		{
			return mainTree.translateItem(item);
		}
		else
		{
			console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
			return;
		}
	},
	translateItemShallow_Link: function(item) {

		if (item.name == "branch") {

			var links = mainTree.translateLinks(item);
			var itext = mainTree.choosever(item);
			if (item.color != undefined) 
			{
				return mainTree._makeBranchItem_Link_Colored(item.id, itext, item.color);
			}
			else 
			{
				return mainTree._makeBranchItem_Link(item.id, itext);
			}
		}
		else if (item.name == "leaf") 
		{
			return mainTree.translateItem(item);
		}
		else
		{
			console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
			return;
		}
	},
	_makeBranchItem_Basic: function(id, text, linksHtml, availableWidth) {
		
		return "<li class='treedata' style='" +
				"max-width:" + availableWidth + 
				"px; overflow: hidden; text-overflow: ellipsis;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\">" +
				text + " " + "</a>" + linksHtml + "</li>";
	},
	_makeBranchItem_Basic_Colored: function(id, text, linksHtml, availablewidth, color) {
		
		return "<li class='treedata' style='color: " +
				color + "max-width:" + availablewidth + 
				"px; overflow: hidden; text-overflow: ellipsis;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\">" +
				text + " </a>" + linksHtml + "</li>";
	},
	_makeBranchItem_Dots: function(id, text, linksHtml) {
		
		return "<li class='treedata' style='display: flex; align-items: center; max-width: max-content; overflow: hidden;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\" " +
				"style=\"flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;\">" + 
				text + " </a>" + linksHtml + "</li>";
	},
	_makeBranchItem_Dots_Colored: function(id, text, linksHtml, color) {
		
		return "<li class='treedata' style='color: " +
				color + "display: flex; align-items: center; max-width: max-content; overflow: hidden;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\" " +
				"style=\"flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;\">" + 
				text + " </a>" + linksHtml + "</li>";
	},
	_makeBranchItem_Link: function(id, text) {
		
		return "<li class='treedata' style='display: flex; align-items: center; max-width: max-content; overflow: hidden;'>" +
			"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\" " +
			"style=\"flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;\">" + 
			text + " </a></li>";
	},
	_makeBranchItem_Link_Colored: function(id, text, color) {
		
		"<li class='treedata' style='color: " +
		color + "display: flex; align-items: center; max-width: max-content; overflow: hidden;'>" +
		"<a class=\"breadcrumb\" href=\"javascript:reloadJson(\'" + mode + "\',\'" + id + "\');\" " +
		"style=\"flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;\">" + 
		text + " </a></li>";
	},


	//Translate item
	translateItem: function(item) {

		if(mainTree.overlapType == "basic") return mainTree.translateItem_Basic(item);
		else if(mainTree.overlapType == "dots") return mainTree.translateItem_Dots(item);
		else if(mainTree.overlapType == "links") return mainTree.translateItem_Link(item);
		//else if(mainTree.overlapType == "multyline") return mainTree.translateItem_Multyline(item);	//Not implemented yet
		else return mainTree.translateItem_Basic(item);
	},
	
	
	//Translate item Basic style - overlapType: "basic"
	translateItem_Basic: function(item) {

		if(item.type == "empty") 
		{
			return mainTree._makeItem_Basic_Empty();
		}
		else 
		{
			var links = mainTree.translateLinks(item);
			var cssFromDecorators = mainTree._makeCssFromDecorators_Basic(item);
			var itext = mainTree.choosever(item); //choose short version if available
			
			//return item
			if (item.type == "comment") 
			{
				return mainTree._makeItem_Basic_Comment(cssFromDecorators, links, itext);
			}
			else if (item.type == "nlcomment") 
			{
				return mainTree._makeItem_Basic_NlComment(cssFromDecorators, links, itext);
			}
			else if (item.color != undefined) 
			{
				return mainTree._makeItem_Basic_Colored(cssFromDecorators, links, itext, item.color);
			}
			else // item.type == "item"
			{
				return mainTree._makeItem_Basic(cssFromDecorators, links, itext);
			}
		}
	},
	_makeItem_Basic_Empty() {
		
		return "<li>&nbsp;</li>";
	},
	_makeItem_Basic(cssFromDecorators, linksHtml, text) {

		var overflowStyle = "overflow: hidden; text-overflow: ellipsis;";
		var addToStyle = "";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;

		var itemtext = text;
		if(linksHtml != "") 
		{
			itemtext = "<span style='margin-bottom: -4px;" 
			+ overflowStyle + "'>" + itemtext + "</span>";
		}
		
		return "<li class='treedata' style='" + addToStyle + "'>" +
		itemtext + linksHtml + "</li>";
	},
	_makeItem_Basic_Colored(cssFromDecorators, linksHtml, text, color) {

		var overflowStyle = "overflow: hidden; text-overflow: ellipsis;";
		var addToStyle = "";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;

		var itemtext = text;
		if(linksHtml != "") 
		{
			itemtext = "<span style='margin-bottom: -4px;" 
			+ overflowStyle + "'>" + itemtext + "</span>";
		}
		
		return "<li class='treedata' style='color: " + color + ";" + addToStyle + "'>" +
			itemtext + linksHtml + "</li>";
	},
	_makeItem_Basic_Comment(cssFromDecorators, linksHtml, text) {

		var overflowStyle = "overflow: hidden; text-overflow: ellipsis;";
		var addToStyle = "";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;
		
		return "<li class='treedata' style='color: green; font-style: italic;" + 
			addToStyle + overflowStyle + "'>" + text + linksHtml + "</li>";
	},
	_makeItem_NlComment(cssFromDecorators, linksHtml, text) {

		var overflowStyle = "overflow: hidden; text-overflow: ellipsis;";
		var addToStyle = "";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;
		
		return mainTree._makeItem_Basic_Empty() +
			"<li class='treedata' style='color: green; font-style: italic;" + 
			addToStyle + overflowStyle + "'>" + text + linksHtml + "</li>";
	},
	_makeCssFromDecorators_Basic(item) {
		
		var css = "";
		for(var i = 0; i < item.decorators.length; i++) 
		{
			var name = Object.keys(item.decorators[i]);
			if (name == "bgcolor") 
			{
				css += "background-color: " + item.decorators[i][name] + ";";
			}
			else if (name == "bold") 
			{
				css += "font-weight: bold;";
			}
			else if (name == "italic") 
			{
				css += "font-style: italic;";
			}
			else if (name == "underline") 
			{
				css += "text-decoration: underline;";
			}
			else if (name == "striked") 
			{
				css += "text-decoration: line-through;";
			}
		}
	},


	//Translate item Dots style - overlapType: "dots"
	translateItem_Dots: function(item) {

		//<!-- THAT IS THE SELF-SHORTENING ENTRY -->
		//<li style="display: flex; align-items: center; max-width: max-content; overflow: hidden;">
		//	<span style="flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
		//		Blue - Sorry Seems To Be The Hardest Word (Radio Edit) (Feat. Elton John)
		//	</span>
		//	<a target="_blank" style="text-decoration: none; flex-shrink: 0; margin-left: 5px;" href="https://youtube.com/watch?v=ZaJ_ub9ps58">🅐</a>
		//</li>

		if(item.type == "empty") 
		{
			return mainTree._makeItem_Dots_Empty();
		}
		else 
		{
			var links = mainTree.translateLinks(item);
			var cssFromDecorators = mainTree._makeCssFromDecorators_Dots(item);
			
			//choose short version if available
			var itext = mainTree.choosever(item);
			
			//return item
			if (item.type == "comment") 
			{
				return mainTree._makeItem_Dots_Comment(cssFromDecorators, links, itext);
			}
			else if (item.type == "nlcomment") 
			{
				return mainTree._makeItem_Dots_NlComment(cssFromDecorators, links, itext);
			}
			else if (item.color != undefined) 
			{
				return mainTree._makeItem_Dots(cssFromDecorators, links, itext, item.color);
			}
			else //item.type == "item"
			{
				return mainTree._makeItem_Dots(cssFromDecorators, links, itext);
			}
		}
	},
	_makeItem_Dots_Empty() {
		
		return "<li>&nbsp;</li>";
	},
	_makeItem_Dots(cssFromDecorators, linksHtml, text) {
		
		var spanStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
		var addToStyle = "display: flex; align-items: center; max-width: max-content; overflow: hidden;";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;
		
		var itemtext = text;
		if(linksHtml != "") 
		{
			itemtext = "<span style='margin-bottom: -4px;" 
			+ spanStyle + "'>" + itemtext + "</span>";
		}
		else
		{
			itemtext = "<span style='" + spanStyle + "'>" + itemtext + "</span>";
		}
		
		return "<li class='treedata' style='" + addToStyle + "'>" +
		itemtext + linksHtml + "</li>";
	},
	_makeItem_Dots_Colored(cssFromDecorators, linksHtml, text, color) {

		var spanStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
		var addToStyle = "display: flex; align-items: center; max-width: max-content; overflow: hidden;";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;
		
		var itemtext = text;
		if(linksHtml != "") 
		{
			itemtext = "<span style='margin-bottom: -4px;" 
			+ spanStyle + "'>" + itemtext + "</span>";
		}
		else
		{
			itemtext = "<span style='" + spanStyle + "'>" + itemtext + "</span>";
		}
		
		return "<li class='treedata' style='color: " +
			color + ";" + addToStyle + "'>" +
			itemtext + linksHtml + "</li>";
	},
	_makeItem_Dots_Comment(cssFromDecorators, linksHtml, text) {
		
		var spanStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
		var addToStyle = "display: flex; align-items: center; max-width: max-content; overflow: hidden;";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;

		return "<li class='treedata' style='color: green; font-style: italic;" + addToStyle + "'>" +
			"<span style='" + spanStyle + "'>" + text + "</span>" +
			linksHtml + "</li>";
	},
	_makeItem_Dots_NlComment(cssFromDecorators, linksHtml, text) {
		
		var spanStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
		var addToStyle = "display: flex; align-items: center; max-width: max-content; overflow: hidden;";
		if (linksHtml != "") linksHtml = " " + linksHtml; 
		else addToStyle += "margin-top:3px; margin-bottom: 3px;";
		addToStyle += cssFromDecorators;

		return mainTree._makeItem_Dots_Empty() +
			"<li class='treedata' style='color: green; font-style: italic;" + addToStyle + "'>" +
			"<span style='" + spanStyle + "'>" + text + "</span>" +
			linksHtml + "</li>";
	},
	_makeCssFromDecorators_Dots(item) {
		
		var css = "";
		for(var i = 0; i < item.decorators.length; i++) 
		{
			var name = Object.keys(item.decorators[i]);
			if (name == "bgcolor") 
			{
				css += "background-color: " + item.decorators[i][name] + ";";
			}
			else if (name == "bold") 
			{
				css += "font-weight: bold;";
			}
			else if (name == "italic") 
			{
				css += "font-style: italic;";
			}
			else if (name == "underline") 
			{
				css += "text-decoration: underline;";
			}
			else if (name == "striked") 
			{
				css += "text-decoration: line-through;";
			}
		}
	},


	//Translate item Links style - overlapType: "links"
	translateItem_Link: function(item) {

		if(item.type == "empty") {
		
			return mainTree._makeItem_Link_Empty(item.id);
		}
		else {
		
			var links = mainTree.translateLinks(item);			
			var cssFromDecorators = mainTree._makeCssFromDecorators_Link(item);

			//choose short version if available
			var itext = mainTree.choosever(item);
			
			//return item
			if (item.type == "comment") 
			{
				return mainTree._makeItem_Link_Comment(item.id, cssFromDecorators, links, itext);
			}
			else if (item.type == "nlcomment") 
			{
				return mainTree._makeItem_Link_NlComment(item.id, cssFromDecorators, links, itext);
			}
			else if (item.color != undefined) 
			{
				return mainTree._makeItem_Link_Colored(item.id, cssFromDecorators, links, itext, item.color);
			}
			else //item.type == "item"
			{ 
				return mainTree._makeItem_Link(item.id, cssFromDecorators, links, itext);
			}
		}
	},
	_makeItem_Link_Empty(id) {
		
		return "<li id='" + id + "'>&nbsp;</li>";
	},
	_makeItem_Link(id, cssFromDecorators, linkHtml, text) {
		
		var addToStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;";		
		addToStyle += cssFromDecorators;
		
		return "<li id='" + id + "' class='treedata' style='" + addToStyle + "'>" +
			linkHtml.replace('~~~', text) + "</li>";
	},
	_makeItem_Link_Colored(id, cssFromDecorators, linkHtml, text, color) {
		
		var addToStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;";
		addToStyle += cssFromDecorators;
		
		return "<li id='" + id + "' class='treedata' style='color: " +
			color + ";" + addToStyle + "'>" +
			linkHtml.replace('~~~', text) + "</li>";
	},
	_makeItem_Link_Comment(id, cssFromDecorators, linkHtml, text) {

		var addToStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;";
		addToStyle += cssFromDecorators;
		
		return "<li id='" + id + "' class='treedata' style='color: green; font-style: italic;" + addToStyle + "'>" +
			linkHtml.replace('~~~', text) + "</li>";
	},
	_makeItem_Link_NlComment(id, cssFromDecorators, linkHtml, text) {

		var addToStyle = "flex-grow: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;";
		addToStyle += cssFromDecorators;
		
		return mainTree._makeItem_Link_Empty() + 
			"<li id='" + id + "' class='treedata' style='color: green; font-style: italic;" + addToStyle + "'>" +
			linkHtml.replace('~~~', text) + "</li>";
	},
	_makeCssFromDecorators_Link(item) {
		
		var css = "";
		for(var i = 0; i < item.decorators.length; i++) 
		{
			var name = Object.keys(item.decorators[i]);
			if (name == "bgcolor") 
			{
				css += "background-color: " + item.decorators[i][name] + ";";
			}
			else if (name == "bold") 
			{
				css += "font-weight: bold;";
			}
			else if (name == "italic") 
			{
				css += "font-style: italic;";
			}
			else if (name == "underline") 
			{
				css += "text-decoration: underline;";
			}
			else if (name == "striked") 
			{
				css += "text-decoration: line-through;";
			}
		}
	},
	

	//Translate links in the items in the main production
	translateLinks: function(item) {
		
		if(mainTree.overlapType == "links") return mainTree.translateLinks_One(item);
		else if(mainTree.overlapType == "basic") return mainTree.translateLinks_Basic(item);
		else if(mainTree.overlapType == "dots") return mainTree.translateLinks_Dots(item);
		//else if(mainTree.overlapType == "multyline") return mainTree.translateLinks_Multyline(item);	//Not implemented yet
		else return mainTree.translateLinks_Basic(item);
	},
	translateLinks_Basic: function(item) {
		
		var links = "";
		for (var i = 0; i < item.links.length; i++) {
		
			var wikicol = "";
			if(item.links[i].url.startsWith("https://en.wikipedia.org/"))
				wikicol = "color:blue;";
			
			links += "<a target='_blank' style='text-decoration: none;" + wikicol + "' href='" +
			item.links[i].url +
			"'>" +
			item.links[i].text +
			"</a>";
		}
		return links;
	},
	translateLinks_Dots: function(item) {
		
		var links = "";
		for (var i = 0; i < item.links.length; i++) {
		
			var wikicol = "";
			if(item.links[i].url.startsWith("https://en.wikipedia.org/"))
				wikicol = "color:blue;";
			
			links += "<a target='_blank' style='text-decoration: none; flex-shrink: 0; margin-left: 5px;" + wikicol + "' href='" +
			item.links[i].url +
			"'>" +
			item.links[i].text +
			"</a>";
		}
		return links;
	},
	translateLinks_One: function(item) {
		
		let addToStyle = "";
		if(namespaces[item.id] && namespaces[item.id].decorators["highlight"])
		{
			let color = namespaces[item.id].decorators["highlight"];
			addToStyle += " background-image: linear-gradient(to right, " + color + " 50%, " + color + " 50%);";
			addToStyle += " background-size: 100% 60%;";
			addToStyle += " background-position: 0 85%;";
			addToStyle += " background-repeat: no-repeat;";
		}
		
		var links = "";
		for (var i = 0; i < item.links.length; i++) {
			
			links += "<a target='_blank' style='all: unset; cursor: pointer;" + addToStyle + 
			"' href='" + item.links[i].url + "'>~~~</a>";
			break;
		}
		
		if(links == "")
		{
			links = "<a style='all: unset;" + addToStyle + "' href='javascript:void(0);'>~~~</a>";
		}
		return links;
	},
	
	
	//Choose from the different length versions of items text, if available
	choosever: function(item) {
		
		var itext = item.text;
		if(mainTree.isMobile == false) return itext;
		else
		{
			if(item.shortvers == undefined) return itext;
			if(item.shortvers == null) return itext;
			if(item.shortvers.length < 1) return itext;
			
			var approxWidth = mainTree.approxStringWidth(item.text);
			for(var i = item.shortvers.length - 1; i >= 0; i--)
			{
				var approx = mainTree.approxStringWidth(item.shortvers[i]);
				if(approx < approxWidth)
				{
					itext = item.shortvers[i];
					approxWidth = approx;
				}
			}
			
			return itext;
		}
	},
	approxStringWidth(text) {
		
		//https://flexiple.com/javascript/javascript-dictionary
		var approx = text.length * 6.75;
		return approx;
	}
}
