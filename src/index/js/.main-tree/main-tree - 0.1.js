const mainTree = {
	
	//fields
	Container: null,



	//public methods
	Init: function(treeContainer){
		
		mainTree.Container = treeContainer;
	},
	Draw: function(obj){

		var title = "<h2 style='margin-top: -25px; margin-bottom: 10px;'>" + obj.text + " <span style='text-decoration: none; vertical-align: super; position: relative; top: 0.2em; font-size: 70%; color: blue;'>[<a style='font-style: italic;' href='javascript:showEditor(\"" + obj.filename + "\",\"" + obj.id + "\");'>edit</a>]</span></h2>";
		
		var prodHtml = mainTree.translateBaseProductionShallow(obj);
		mainTree.Container.innerHTML = title + prodHtml;
		console.log("Tree drawn: " + obj.id);
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
			
				return "<li style='color: " +
				item.color +
				";'>" +
				"<a class=\"breadcrumb\" href=\"javascript:addLargeTree(\'" + item.id + "\');\">" + 
				item.text + " " +
				"</a>" +
				links +
				"</li>";
			}
			else {
		
				return "<li>" +
				"<a class=\"breadcrumb\" href=\"javascript:addLargeTree(\'" + item.id + "\');\">" +
				item.text + " " +
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
		
			//do links
			var links = "";
			for (var i = 0; i < item.links.length; i++) {
			
				links += "<a target='_blank' style='text-decoration: none' href='" +
				item.links[i].url +
				"'>" +
				item.links[i].text +
				"</a>";
			}
			
			var addToStyle = "";
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
			
			if (item.type == "comment") {
			
				return "<li style='color: green; font-style: italic;" + addToStyle + "'>" +
				item.text + " " +
				links +
				"</li>";
			}
			else if (item.type == "nlcomment") {
			
				return "<li>&nbsp;</li>" + 
				"<li style='color: green; font-style: italic;" + addToStyle + "'>" +
				item.text + " " +
				links +
				"</li>";
			}
			else if (item.color != undefined) {
			
				return "<li style='color: " +
				item.color +
				";" + addToStyle + "'>" +
				item.text + " " +
				links +
				"</li>";
			}
			else { //item.type == "item"
		
				return "<li style='" + addToStyle + "'>" +
				item.text + " " +
				links +
				"</li>";
			}
		}
	}
}