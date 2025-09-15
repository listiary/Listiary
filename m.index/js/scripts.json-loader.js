const public_root_filename = ".radiowatch";		//test.root
const personal_root_filename = "";
const private_root_filename = "";
const normative_root_filename = "";

async function loadJson(mode, article = null) {

	var rjson = await fetchJson(mode, article);
//console.log(rjson);
	if (!articleId) setDefaultArticleId(rjson);
	populateNamespaces(rjson);
//console.log(namespaces);
	if (!namespaces[articleId]) setDefaultArticleId(rjson);
	if (rjson.name == "files") fixJson(rjson);

	addLargeTree(articleId);
	//addOpsWidget(articleId);
}
async function reloadJson(mode, article) {

	var rjson = await fetchJson(mode, article);
//console.log(rjson);
	populateNamespaces(rjson);
//console.log(namespaces);
	if (rjson.name == "files") fixJson(rjson);
	articleId = article;

	addLargeTree(articleId);
	//addOpsWidget(articleId);
}
async function fetchJson(mode, article = null) {

	var rjson = "";
	if(mode === "public")
	{
		if(options.FetchLocal) rjson = JSON_PAYLOAD;
		else
		{
			var response = await _fetchPublic(article);
			rjson = await response.json();
			//var text = await response.text(); console.log(text);
		}
	}
	else if(mode === "personal")
	{
		var response = await fetch("./index/_getdatapack_Personal.php?username=" + userName);
		//rjson = await response.json();
		//console.log(JSON.stringify(json, null, 4));
	}
	else if(mode === "private")
	{
		var response = await fetch("./index/_getdatapack_Private.php?usercode=" + userName);
		var cyphertext = await response.text();
		console.log(cyphertext);

		var pass = window.prompt("Enter password:");
		if(pass == null || pass == "")
		{
			rjson = null;
			return;
		}
		//unlock password should be derived from the user name and "pass"
		//instead, we will skip the step and enter a ready password instead, for now.

		var jstring = await decrypt(cyphertext, pass);
		rjson = JSON.parse(jstring);
	}
	else if(mode === "normative")
	{
		var response = await fetch("./index/_getdatapack_Documentation.php");
		rjson = await response.json();
	}
	return rjson;
}
async function _fetchPublic(article = null) {

	if(article == null)
	{
		var filename = public_root_filename;
		var response = await fetch("./m.index/php/_getdatapack_Single.php",
		{
			method: "POST",
			headers:
			{
				"Content-Type": "application/json"
			},
			body: JSON.stringify({ filename })
		});

		//var text = await response.text();
		//console.log(text);
		//return "";
		return response;
	}
	else
	{
		var response = await fetch("./m.index/php/_getdatapack_Single.php",
		{
			method: "POST",
			headers:
			{
				"Content-Type": "application/json"
			},
			body: JSON.stringify({ article })
		});

		//var text = await response.text();
		//console.log(text);
		//return "";
		return response;
	}
}
function setDefaultArticleId(rjson) {

	//this is something like 'rjson.item[0].id'
	if(!rjson.name && rjson.items && rjson.items.length > 0 && rjson.items[0].name == "branch")
	{
		articleId = rjson.items[0].id;
	}
	//this is something like 'rjson.file[0].item[0].id'
	else if (rjson.name == "files")
	{
		articleId = rjson.items[0].items[0].id;
	}
	else
	{
		console.log("ERROR : Could not set articleId");
	}
}
function populateNamespaces(rjson) {

	if(rjson.items.length < 1) {
		console.log('FATAL: No files in JSON');
		return;
	}

	if(!rjson.name && rjson.items && rjson.items.length > 0 && rjson.items[0].name == "branch")
	{
		for(let j = 0; j < file.items.length; j++)
		{
			var root = rjson.items[j];
			if (root.name == "branch") _populateNamespaces_Production(root);
			else if (root.name == "leaf") _populateNamespaces_Item(root);
			else console.log('FATAL: Unknown item.name! What is "' + root.name + '"?');
		}
	}
	else if(rjson.name == "files")
	{
		for(let i = 0; i < rjson.items.length; i++)
		{
			var file = rjson.items[i];
			for(let j = 0; j < file.items.length; j++)
			{
				var root = file.items[j];
				if (root.name == "branch") _populateNamespaces_Production(root);
				else if (root.name == "leaf") _populateNamespaces_Item(root);
				else console.log('FATAL: Unknown item.name! What is "' + root.name + '"?');
			}
		}
	}
	else
	{
		console.log('FATAL: Unknown rjson.name! What is "' + rjson.name + '"?');
	}
}
function _populateNamespaces_Production(item, parentItem = null) {

	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;

	for (var i = 0; i < item.items.length; i++) {

		if (item.items[i].name == "branch") _populateNamespaces_Production(item.items[i], item);
		else if (item.items[i].name == "leaf") _populateNamespaces_Item(item.items[i], item);
		else console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
	}
}
function _populateNamespaces_Item(item, parentItem = null) {

	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;
}
function fixJson(rjson) {

	if (!rjson.name || rjson.name != "files") return;

	//iterate to all file roots - those are the ones without parentItem
	for (const [key, item] of Object.entries(namespaces)) {

		if (item.parentItem) continue;
		var root = item;
		for(let i = 0; i < root.items.length; i++)
		{
			var id = root.items[i].id;
			if(!namespaces[id]) continue;
			namespaces[id].parentItem = root;

			if(namespaces[id].name != "branch") continue;
			root.items[i] = _fixjson(namespaces[id]);
		}
	}

console.log(namespaces);

	//set json object - only one left with no parent item.
	json = null;
	for (const [key, item] of Object.entries(namespaces)) {

		if (item.parentItem) continue;
		json = item;
	}
}
function _fixjson(item) {

	for(let i = 0; i < item.items.length; i++)
	{
		var id = item.items[i].id;
		if(!namespaces[id]) continue;
		namespaces[id].parentItem = item;

		if(namespaces[id].name != "branch") continue;
		item.items[i] = _fixjson(namespaces[id], item);
	}
	return item;
}








