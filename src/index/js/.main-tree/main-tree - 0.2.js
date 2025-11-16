const mainTree = {
	
	//fields
	Titler: null,
	Container: null,
	availableWidth: 0,
	isMobile: false,

	//public methods
	Init: function(treeTitleContainer, treeContainer){

		mainTree.Titler = treeTitleContainer;
		mainTree.Container = treeContainer;
		mainTree.availableWidth = mainTree.getAvailableWidth();
	},
	Draw: function(id){
		
		mainTree.isMobile = false;
		var obj = namespaces[id];
		mainTree.setUrl(id);
		

		var filename = namespaces[id].filename;
		//if(mode === "personal") filename = user + '.' + filename;
		//else if(mode === "private") filename = user + '.' + filename;

		var title = "<h2 style='margin-top: -25px; margin-bottom: 10px;'>" + obj.text + " <span style='text-decoration: none; vertical-align: super; position: relative; top: 0.2em; font-size: 70%; color: blue;'>[<a style='font-style: italic;' href='javascript:showEditor(\"" + filename + "\",\"" + obj.id + "\");'>edit</a>]</span></h2>";
		
		var prodHtml = mainTree.translateBaseProductionShallow(obj);
		mainTree.Container.innerHTML = title + prodHtml;
		//console.log("Tree drawn: " + obj.id);
		
		mainTree.drawBreadcrumb(id);
	},
	DrawMobile: function(id){
		
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
		
		mainTree.drawBreadcrumb(id);
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
				"<a class=\"breadcrumbmain\" href=\"javascript:mainTree.Draw(\'" + 
				cur.id + "\');\">" + cur.text + "</a>" + breadCrumb;
		}
		if (breadCrumb.length == 0)
		{
			breadCrumb = "<a class=\"breadcrumbmain\" href=\"javascript:mainTree.Draw(\'" + 
				cur.id + "\');\">" + cur.text + "</a>";
		}		
		
		//add html
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-size: 25px; margin: 7px; margin-left: 30px; padding: 10px; margin-top: 19px; padding-left: 0px; padding-top: 0px;'><div style='margin-right:35px;'>"
		html += breadCrumb;
		html += "</div><hr /></div>";
		html += "</div>";

		mainTree.Titler.innerHTML = html;
	},
	setUrl: function(id) {
		
		var cur = namespaces[id];
		var args = "?domain=" + mode + "&article=" + id;
		
		var documentTitle = cur.text.charAt(0).toUpperCase() + cur.text.slice(1);
		documentTitle += " | Proto World";
		if(documentTitle.length > 60) 
		{
			documentTitle = documentTitle.substring(0, 56);
			documentTitle += " ...";
		}
		document.title = documentTitle;
		window.history.replaceState("", documentTitle, args);
		//window.history.pushState("", documentTitle, args);
		
		
		//add data for the history deamon
		historyDeamon.articleArguments = args;
		historyDeamon.articleTitle = documentTitle;
		historyDeamon.articleLastChangedTime = new Date();
	},

	//private methods drawing
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

	//private methods
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
			items += mainTree.translateItemShallow(item.items[i]);
		}
		
		 // //translate source items
		 // var sourceClass = "_HU_S_" + item.id.split('.').join('_');
		 // opsWidget_HideUnhideSource = sourceClass;
		 // var sourceItems = "";
		 // for (var i = 0; i < item.items.length; i++) {
			 // sourceItems += translateSourceItemShallow(item.items[i], sourceClass);
		 // }
		 // if(sourceItems != "") 
		 // {
			 // sourceItems += "<li class='" + sourceClass + "' style='display: none;'>&nbsp;</li>";
			 // sourceItems += "<li class='" + sourceClass + "' style='display: none;'>&nbsp;</li>";
		 // }

		// //translate colorlegend items
		// var legendClass = "_HU_L_" + item.id.split('.').join('_');
		// opsWidget_HideUnhideLegend = legendClass;
		// var legendItems = "";
		// for (var i = 0; i < item.items.length; i++) {
			 // legendItems += translateLegendItemShallow(item.items[i], legendClass);
		 // }
		 // if(legendItems != "") 
		 // {
			 // legendItems += "<li class='" + legendClass + "' style='display: none;'>&nbsp;</li>";
			 // legendItems += "<li class='" + legendClass + "' style='display: none;'>&nbsp;</li>";
		 // }

		//translate links
		var links = " ";
		for (var i = 0; i < item.links.length; i++) {
		
			links += "<a target='_blank' style='text-decoration: none' href='" +
			item.links[i].url +
			"'>" +
			item.links[i].text +
			"</a>";
		}
		
		if (item.color != undefined) {
			
			return "<li><div style='color: " +
			item.color +
			";'  class='mainlistree-submenu-heading' id='" + item.id + "'>" +
			links +
			"</div><ul class='mainlistree-submenu-items'><br />" +
			//sourceItems + legendItems + 
			items +
			"<br /></ul></li>";
		}
		else {
		
			return "<li><div class='mainlistree-submenu-heading' id='" + item.id + "'>" +
			links +
			"</div><ul class='mainlistree-submenu-items'><br />" +
			//sourceItems + legendItems + 
			items +
			"<br /></ul></li>";
		}
	},
	translateItemShallow: function(item) {
	
		if (item.name == "branch") {

			var links = mainTree.translateLinks(item);
			var itext = mainTree.choosever(item);
			if (item.color != undefined) {
			
				return "<li style='color: " +
				item.color + "max-width:" + mainTree.availableWidth + 
				"px; overflow: hidden; text-overflow: ellipsis;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:mainTree.Draw(\'" + item.id + "\');\">" + 
				itext + " " +
				"</a>" +
				links +
				"</li>";
			}
			else {
		
				return "<li style='" +
				"max-width:" + mainTree.availableWidth + 
				"px; overflow: hidden; text-overflow: ellipsis;'>" +
				"<a class=\"breadcrumb\" href=\"javascript:mainTree.Draw(\'" + item.id + "\');\">" +
				itext + " " +
				"</a>" +
				links +
				"</li>";
			}
		}
		else if (item.name == "leaf") 
		{
			if (item.decorators && item.decorators.length > 0)
			{
				for (var i = 0; i < item.decorators.length; i++)
				{
					var dkey = Object.keys(item.decorators[i])[0];
					if (dkey == "source") return "";
					else if (dkey == "colorlegend") return "";
				}
			}
		
			return mainTree.translateItem(item);
		}
		else
		{
			console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
			return;
		}
	},
	translateItem: function(item) {

		if(item.type == "empty") {
		
			return "<li>&nbsp;</li>";
		}
		else {
		
			var links = mainTree.translateLinks(item);
			var overflowStyle = "max-width:" + mainTree.availableWidth + 
				"px; overflow: hidden; text-overflow: ellipsis;";
			
			var addToStyle = "";
			if (links != "") links = " " + links;
			else addToStyle += "margin-top:3px; margin-bottom: 3px;";

			for(var i = 0; i < item.decorators.length; i++) {
				
				var name = Object.keys(item.decorators[i]);
				if (name == "bgcolor") 
				{
					addToStyle += "background-color: " + item.decorators[i][name] + ";";
				}
				else if (name == "bold") 
				{
					addToStyle += "font-weight: bold;";
				}
				else if (name == "italic") 
				{
					addToStyle += "font-style: italic;";
				}
				else if (name == "underline") 
				{
					addToStyle += "text-decoration: underline;";
				}
				else if (name == "striked") 
				{
					addToStyle += "text-decoration: line-through;";
				}
			}
			
			//choose short version if available
			var itext = mainTree.choosever(item);
			
			//return item
			if (item.type == "comment") {
			
				return "<li style='color: green; font-style: italic;" + addToStyle + overflowStyle + "'>" +
				itext +
				links +
				"</li>";
			}
			else if (item.type == "nlcomment") {
			
				return "<li>&nbsp;</li>" + 
				"<li style='color: green; font-style: italic;" + addToStyle + overflowStyle + "'>" +
				itext +
				links +
				"</li>";
			}
			else if (item.color != undefined) {
			
				var itemtext = itext;
					if(links != "") 
						itemtext = "<span style='margin-bottom: -4px;" 
						+ overflowStyle + "'>" + itemtext + "</span>";
						
				return "<li style='color: " +
				item.color + ";" + addToStyle + "'>" +
				itemtext + links + "</li>";
			}
			else { //item.type == "item"
		
				var itemtext = itext;
				if(links != "") 
					itemtext = "<span style='margin-bottom: -4px;" 
					+ overflowStyle + "'>" + itemtext + "</span>";
				
				return "<li style='" + addToStyle + "'>" +
				itemtext + links + "</li>";
			}
		}
	},
	translateLinks: function(item) {
		
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
		
		// if(item.shortvers == undefined) return itext;
		// if(item.shortvers == null) return itext;
		// if(item.shortvers.length < 2) return itext;
		
		// var approxWidth = 0;
		// if(item.type != "comment" 
			// && item.type != "nlcomment"
			// && item.links.length > 0)
		// {
			// approxWidth = 5 + (item.links.length * 14);
		// }
		// approxWidth += mainTree.approxStringWidth(item.text);
		// if(approxWidth < mainTree.availableWidth - 30) return itext;
		// for(var i = 0; i < item.shortvers.length; i++)
		// {
			// var approx = mainTree.approxStringWidth(item.shortvers[i]);
			// if(approx < mainTree.availableWidth - 30) return item.shortvers[i];
			// if(approx < approxWidth)
			// {
				// itext = item.shortvers[i];
				// approxWidth = approx;
			// }
		// }
		// return itext;
	},
	approxStringWidth(text) {
		
		//https://flexiple.com/javascript/javascript-dictionary
		var approx = text.length * 6.75;
		return approx;
	}
}