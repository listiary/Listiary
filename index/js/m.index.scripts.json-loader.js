async function loadJson(mode) {

	namespaces = {};
	await fetchJson(mode);
	if (articleId == null) articleId = json.items[0].id;

	populateNamespaces(json);

	//if our articleId is non-existant, load home article instead
	if (namespaces[articleId] == null) articleId = json.items[0].id;

	addLargeTree(articleId);
	//addOpsWidget(articleId);
}
async function fetchJson(mode) {

	if(mode === "public")
	{
		if(options.FetchLocal) json = JSON_PAYLOAD;
		else
		{
			var response = await fetch("./index/_getdatapack_Public.php");
			json = await response.json();
		}
	}
	else if(mode === "personal")
	{
		var response = await fetch("./index/_getdatapack_Personal.php?username=" + userName);
		json = await response.json();
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
			json = null;
			return;
		}
		//unlock password should be derived from the user name and "pass"
		//instead, we will skip the step and enter a ready password instead, for now.

		var jstring = await decrypt(cyphertext, pass);
		json = JSON.parse(jstring);
	}
	else if(mode === "normative")
	{
		var response = await fetch("./index/_getdatapack_Documentation.php");
		json = await response.json();
	}
	//console.log("Json fetched:");
	//console.log(json);
}
function populateNamespaces(json) {

	if(json.items.length != 1) {
		console.log('FATAL: Unknown template!');
		return;
	}

	var root = json.items[0];
	//translateBaseProductionOrItem
	if (root.name == "branch") _populateNamespaces_Production(root);
	else if (root.name == "leaf") _populateNamespaces_Item(root);
	else console.log('FATAL: Unknown item.name! What is "' + root.name + '"?');
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
