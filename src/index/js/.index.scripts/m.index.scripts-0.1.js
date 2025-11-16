//DOM
function cleanPage() {						// remove all content from containers
	
	var largeTopContainer = document.getElementById("LargeTopContainer");
	if(largeTopContainer != null) largeTopContainer.innerHTML = "";

	var largeContainer = document.getElementById("LargeContainer");
	if(largeContainer != null) largeContainer.innerHTML = "";
}
function openNav() {						// open sidenav

	document.getElementById("sidenav").style.display = "block";
	document.getElementById("sidenav-trigger").style.display = "none";
	document.getElementById("sidenav").style.width = "250px";
}
function closeNav() {						// close sidenav

	document.getElementById("sidenav").style.display = "none";
	document.getElementById("sidenav-trigger").style.display = "block";
	document.getElementById("sidenav").style.width = "0";
}
function setNavbar_PublicMode() {			// set Navbar in menu for public
	
	document.getElementById("navbar_public").style.display = "none"; 
	document.getElementById("navbar_personal").style.display = "block"; 
	document.getElementById("navbar_private").style.display = "block"; 
	document.getElementById("navbar_normative").style.display = "block"; 
}
function setNavbar_NormativeMode() {		// set Navbar in menu for public
	
	document.getElementById("navbar_public").style.display = "block"; 
	document.getElementById("navbar_personal").style.display = "block";
	document.getElementById("navbar_private").style.display = "block"; 
	document.getElementById("navbar_normative").style.display = "none";
}
function setNavbar_PersonalMode() {			// set Navbar in menu for public
	
	document.getElementById("navbar_public").style.display = "block"; 
	document.getElementById("navbar_personal").style.display = "none";
	document.getElementById("navbar_private").style.display = "block"; 
	document.getElementById("navbar_normative").style.display = "block"; 
}
function setNavbar_PrivateMode() {			// set Navbar in menu for public
	
	document.getElementById("navbar_public").style.display = "block"; 
	document.getElementById("navbar_personal").style.display = "block";
	document.getElementById("navbar_private").style.display = "none"; 
	document.getElementById("navbar_normative").style.display = "block"; 
}
function hideUnhide_ByClass(className) {	// hide or unhide elements with a given class name
	
	var els = document.getElementsByClassName(className);
	for(var i=0; i < els.length; i++)
	{
		if (els[i].style.display == "none") 
		{
			els[i].style.display = "block";
		} 
		else 
		{
			els[i].style.display = "none";
		}
	}
}
function colorElements() {					// debug method - color skeleton elements in different colors
	
	document.getElementById("TopContainer").style.backgroundColor = "red";
	document.getElementById("SmallContainer").style.backgroundColor = "green";
	document.getElementById("LargeContainer").style.backgroundColor = "blue";
}

//show
function showMainMenu() {
	
	var arr = document.getElementById("menuArrow");
	arr.setAttribute('href', 'javascript: showNextMenu();');
	arr.innerHTML = "🡠";
	
	var editLinks = document.getElementsByClassName("MenuMain");
	for(var i = 0; i < editLinks.length; i++)
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("MenuNext");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
}
function showNextMenu() {
	
	var arr = document.getElementById("menuArrow");
	arr.setAttribute('href', 'javascript: showMainMenu();');
	arr.innerHTML = "🡢";
	
	var editLinks = document.getElementsByClassName("MenuNext");
	for(var i = 0; i < editLinks.length; i++) 
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("MenuMain");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
}


//Event handlers
function publicClick() {					// Navbar->Public click
	
	cleanPage();
	mode = "public";
	articleId = null;
	
	loadJson(mode);
	setNavbar_PublicMode();
}
function personalClick(username) {			// Navbar->Personal click

	cleanPage();
	mode = "personal";
	
	if(username.length > 0) 
	{
		//alert(username);
		user = username;
		articleId = null;
		loadJson(mode, user);
		setNavbar_PersonalMode();
	}
	else 
	{
		var param = "?domain=personal";
		//alert("Not logged in");
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
}
function privateClick(usercode) {			// Navbar->Private click
	
	cleanPage();
	mode = "private";
	
	if(usercode.length > 0) 
	{
		//alert(usercode);
		user = usercode;
		articleId = null;
		loadJson(mode, user);
		setNavbar_PrivateMode();
	}
	else 
	{
		var param = "?domain=private";
		//alert("Not logged in");
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
}
function normativeClick() {					// Navbar->Normative click
	
	cleanPage();
	mode = "normative";
	articleId = null;
	
	loadJson(mode);
	setNavbar_NormativeMode();
}

//Redirects
async function showEditor(articleName, itemId) {

	if(mode == "public" || mode == "normative")
	{
		window.location.href = "./editor/editor.php?article=" 
			+ articleName + "&domain=" + mode;
	}
	else if(mode == "personal")
	{
		window.location.href = "./editor/editor.php?article=" 
			+ articleName + "&domain=" + mode + "&username=" + user;
	}
	else if(mode == "private")
	{
		console.log("Editing not implemented for private mode");
	}
	else
	{
		console.log("Editing not implemented for this mode");
	}
}
async function showPC(articleName = "") {

	if(articleName == null || articleName == "")
	{
		if(mode != undefined) window.location.href = "./index.php?domain=" + mode;
		else window.location.href = "./index.php?domain=public";
	}
	else
	{
		window.location.href = "./index.php?article=" 
			+ articleName + "&domain=" + mode;
	}		
}

//add elements
function addFooter(json) {					// add compilation timestamp message

	//https://www.w3schools.com/howto/howto_css_fixed_footer.asp
	if(media == "mobile" || media == "small")
	{
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-style: italic; margin: 7px; margin-right: 0px; padding: 10px; padding-right: 0px;'>"
		html += json.short_timestamp;
		html += "<br />";
		html += json.short_version;
		html += "<hr /></div>";
		html += "</div>";
		document.getElementById("SmallTopContainer").innerHTML = html;
	}
	else
	{
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-style: italic; margin: 7px; margin-right: 0px; padding: 10px; padding-right: 0px;'>"
		html += json.timestamp;
		html += "<br />";
		html += json.version;
		html += "<hr /></div>";
		html += "</div>";
		document.getElementById("SmallTopContainer").innerHTML = html;
	}
}
function addLargeTree(id) {					// add tree
	
	document.getElementById("LargeContainer").innerHTML = "<ul class='mainlistree'></ul>";
	var topelement = document.getElementById("LargeTopContainer");
	var contelement = document.getElementById("LargeContainer");
	mainTree.Init(topelement, contelement);
	mainTree.DrawMobile(articleId);
}
function addOpsWidget(id) {					// add ops widget

	// <img src='index/img/warning-circle-bold-gray.png' style='width:1.3em; height:1.3em; vertical-align: text-top; transform: rotate(180deg); margin-right: 10px; margin-top: -2px;'>[SOURCES]</a>
	// <img src='index/img/book-open-text-bold-gray.png' style='width:1.3em; height:1.3em; vertical-align: text-top; margin-right: 10px; margin-top: -2px;'>LEGEND</a>
	
	var html = "<div style='margin-top: -20px;'><a style='cursor: pointer; text-decoration: none; font-weight: bold; font-style: italic; color: blue; margin-left: 20px; font-size: 20px;'>[widget]</a> ∙ <a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 0px; font-size: 20px;' href='javascript:addNavTree(null);'>[nav-tree]</a> ∙ <a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 0px; font-size: 20px;'>[favorites]</a>";
	
	html += "<br><br><br><a id='OpsWidget_Sources' style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;' href='javascript:hideUnhide_ByClass(opsWidget_HideUnhideSource); OpsWidgetSelectUnselect(\"OpsWidget_Sources\");'>□ sources</a>";
	
	html += "<br><a id='OpsWidget_Legend' style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;' href='javascript:hideUnhide_ByClass(opsWidget_HideUnhideLegend); OpsWidgetSelectUnselect(\"OpsWidget_Legend\");'>□ legend</a>";
	
	html += "<br><br><a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;'>□ copy</a>";
	
	html += "<br><a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;'>□ expand</a>";
	
	html += "<br><a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;'>□ sort</a>";
	
	html += "<br><a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;'>□ filter</a>";
	
	html += "<br><a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;'>□ pick random one</a>";
	
	document.getElementById("SmallContainer").innerHTML = html;
}
function addNavTree(id) {					// add nav-tree
	
	var html = "<div style='margin-top: -20px;'><a style='cursor: pointer; text-decoration: none; font-style: italic; color: gray; margin-left: 20px; font-size: 20px;' href='javascript:addOpsWidget(null);'>[widget]</a> ∙ <a style='font-weight: bold; cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: blue; margin-left: 0px; font-size: 20px;'>[nav-tree]</a> ∙ <a style='cursor: pointer; margin-top: -25px; text-decoration: none; font-style: italic; color: gray; margin-left: 0px; font-size: 20px;'>[favorites]</a><br><br><br>";
	
	document.getElementById("SmallContainer").innerHTML = html;
	
	// Add Nav-tree
	if(json.items.length != 1) {
		console.log('FATAL: Unknown template!');
		return;
	}
	// if(json.items[0].text != 'PUBLIC') {
		// console.log('FATAL: Unknown template!');
		// return;
	// }
	
	var root = json.items[0];
	var html = translateBaseProductionOrItem(root);
	document.getElementById("SmallContainer").innerHTML += html;
	listreeMake();
	
	// click to expand first element
	var el = document.getElementById(json.items[0].id);
	if (el != undefined && el != null) el.click();
	var expander = el.firstChild;
	if (expander != undefined && expander != null) expander.click();
}





//OpsWidget
function OpsWidgetSelectUnselect(id) {
	
	var elem = document.getElementById(id);
	if (elem == null) 
	{
		console.log('FATAL: Element with id="' + id + '" is NULL!');
		return;
	}
	
	if (elem.style.fontWeight > 400)
	{
		elem.style.fontWeight = "400";
		elem.style.color = "gray";
	}
	else
	{
		elem.style.fontWeight = "900";
		elem.style.color = "blue";
	}
}

//nav-tree translations
function translateBaseProductionOrItem(item) {

	if (item.name == "branch") {
		return "<ul class='listree'>" + translateProduction(item) + "</ul>";
	}
	else if (item.name == "leaf") {
		return "<ul class='listree'>" + translateItem(item) + "</ul>";
	}
	else {
		console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
		return;
	}
}
function translateProductionOrItem(item, parentItem = null) {

	if (item.name == "branch") return translateProduction(item, parentItem);
	else if (item.name == "leaf") return null; //return translateItem(item, parentItem);
	else
	{
		console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
		return null;
	}
}
function translateProduction(item, parentItem = null) {

	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;

	//translate items
	var items = "";
	for (var i = 0; i < item.items.length; i++) {
		
		var translation = translateProductionOrItem(item.items[i], item);
		if(translation != null) items += translation;
	}
	
	//translate links
	// var links = " ";
	// for (var i = 0; i < item.links.length; i++) {
	
		// links += "<a target='_blank' style='text-decoration: none' href='" +
		// item.links[i].url +
		// "'>" +
		// item.links[i].text +
		// "</a>";
	// }
	
	//if no items, we will translate it as an item
	if (items == "") {
		
		if (item.color != undefined) {
		
			return "<li style='color: " +
			item.color + ";'>" +
			item.text +
			"</li>";
		}
		else { //item.type == "item"
	
			return "<li style='color: " + item.color + ";'>" +
			item.text +
			"</li>";
		}
	}
	else {
		
		if (item.color != undefined) {
	
			return "<li><div style='color: " +
			item.color +
			";'  class='listree-submenu-heading' id='" + item.id + "'>" +
			item.text +
			"</div><ul class='listree-submenu-items'><br />" +
			items +
			"<br /></ul></li>";
		}
		else {
		
			return "<li><div class='listree-submenu-heading' id='" + item.id + "'>" +
			item.text +
			"</div><ul class='listree-submenu-items'><br />" +
			items +
			"<br /></ul></li>";
		}
	}
}
function translateItem(item, parentItem = null) {

	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;

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

//nav-tree listree
function listreeMake() {

	const e = document.getElementsByClassName("listree-submenu-heading");
	const elementsArray = Array.from(e);
	elementsArray.forEach(listreeAdd);
}
function listreeAdd(element) {

	element.classList.add("collapsed");
	
	var index = Math.floor(element.innerText.length / 2);
	var fist = element.innerText.slice(0, index);

	element.innerHTML = "<span style='cursor:default;' class='plusminus'>+ </span>" + 
	"<span style='cursor:default;' class='fist'>" + fist + "</span>" +
	element.innerHTML.slice(index);
	
	element.innerHTML;
	element.nextElementSibling.style.display = "none";
	
	element.addEventListener("click", listreeClick);
	var pm = element.firstElementChild;
	pm.addEventListener("click", plusminusClick);
	var fi = element.getElementsByClassName('fist')[0];
	fi.addEventListener("click", plusminusClick);
}
function listreeClick(event) {

	// const nextSibling = event.target.nextElementSibling;

	// if(nextSibling.style.display == "none") {

		// event.target.classList.remove("collapsed");
		// event.target.classList.add("expanded");
		// event.target.firstElementChild.innerHTML = "- ";
		// nextSibling.style.display = "block";
	// }
	// else {
	
		// event.target.classList.remove("expanded");
		// event.target.classList.add("collapsed");
		// event.target.firstElementChild.innerHTML = "+ ";
		// nextSibling.style.display = "none";
	// }
	
	addLargeTree(event.target.id);
	event.stopPropagation();
}
function plusminusClick(event) {

	var target = event.target.parentElement;
	const nextSibling = target.nextElementSibling;

	if(nextSibling.style.display == "none") {

		target.classList.remove("collapsed");
		target.classList.add("expanded");
		target.firstElementChild.innerHTML = "- ";
		nextSibling.style.display = "block";
	}
	else {
	
		target.classList.remove("expanded");
		target.classList.add("collapsed");
		target.firstElementChild.innerHTML = "+ ";
		nextSibling.style.display = "none";
	}
	
	//addLargeTree(target.id);
	event.stopPropagation();
}





//main
var json;
function getMedia() {

	if(mobileDetector.IsMobile())
	{
		//alert("mobile");
		return "mobile";
	}
	else if(window.innerWidth < 850) 
	{
		//alert("small");
		return "small";
	}
	else 
	{
		//alert("pc");
		return "pc";
	}
}
async function loadJson(mode) {

	namespaces = {};
	await fetchJson(mode);
	if (articleId == null) articleId = json.items[0].id;

	populateNamespaces(json);
	addLargeTree(articleId);
	//addOpsWidget(articleId);
}
async function fetchJson(mode) {
	
	if(mode === "public")
	{
		var response = await fetch("./index/_getdatapack_Public.php");
		json = await response.json();
	}
	else if(mode === "personal")
	{
		var response = await fetch("./index/_getdatapack_Personal.php?username=" + user);
		json = await response.json();
		//console.log(JSON.stringify(json, null, 4));
	}
	else if(mode === "private")
	{
		var response = await fetch("./index/_getdatapack_Private.php?usercode=" + user);
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
async function decrypt(cyphertext, pass) {
	
	var keyBytes = CryptoJS.PBKDF2(pass, 'worldinlists', { keySize: 48 / 4, iterations: 1000 });
	
	//take first 32 bytes as key (like in C# code)
	var key = new CryptoJS.lib.WordArray.init(keyBytes.words, 32);
	var seekey = wordArrayToByteArray(key, 32);
	console.log(seekey);
	
	//skip first 32 bytes and take next 16 bytes as IV
	var iv = new CryptoJS.lib.WordArray.init(keyBytes.words.splice(32 / 4), 16);
	var seeiv = wordArrayToByteArray(iv, 16);
	console.log(seeiv);

	var plaintextArray = CryptoJS.AES.decrypt(
	{
		ciphertext: CryptoJS.enc.Base64.parse(cyphertext),
		salt: "worldinlists"
	},
		key,
		{ iv: iv }
	);

	var result = hex2a(plaintextArray.toString());
	//console.log(result);
	return result;
}

//populate namespaces
var namespaces;
function populateNamespaces(json) {
	
	if(json.items.length != 1) {
		console.log('FATAL: Unknown template!');
		return;
	}
	
	var root = json.items[0];	
	//translateBaseProductionOrItem
	if (root.name == "branch") populateNamespaces_Production(root);
	else if (root.name == "leaf") populateNamespaces_Item(root);
	else console.log('FATAL: Unknown item.name! What is "' + root.name + '"?');
}
function populateNamespaces_Production(item, parentItem = null) {
	
	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;
	
	for (var i = 0; i < item.items.length; i++) {
		
		if (item.items[i].name == "branch") populateNamespaces_Production(item.items[i], item);
		else if (item.items[i].name == "leaf") populateNamespaces_Item(item.items[i], item);
		else console.log('FATAL: Unknown item.name! What is "' + item.name + '"?');
	}
}
function populateNamespaces_Item(item, parentItem = null) {
	
	namespaces[item.id] = item;
	if(parentItem != null) item.parentItem = parentItem;
}

//main helpers
function wordArrayToByteArray(wordArray, length) {
	
	if (wordArray.hasOwnProperty("sigBytes") && wordArray.hasOwnProperty("words")) {
		length = wordArray.sigBytes;
		wordArray = wordArray.words;
	}

	var result = [],
		bytes,
		i = 0;
	while (length > 0) {
		bytes = wordToByteArray(wordArray[i], Math.min(4, length));
		length -= bytes.length;
		result.push(bytes);
		i++;
	}
	return [].concat.apply([], result);
}
function wordToByteArray(word, length) {
	var ba = [],
		i,
		xFF = 0xFF;
	if (length > 0)
		ba.push(word >>> 24);
	if (length > 1)
		ba.push((word >>> 16) & xFF);
	if (length > 2)
		ba.push((word >>> 8) & xFF);
	if (length > 3)
		ba.push(word & xFF);

	return ba;
}
function hex2a(hex) {
	
	// Convert hex string to ASCII.
	// See https://stackoverflow.com/questions/11889329/word-array-to-string
	var str = '';
	var skip = false;
	for (var i = 0; i < hex.length; i += 2)
	{
		if(skip)
		{
			skip = false;
			continue;
		}
		else
		{
			skip = true;
			str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
		}
	}
	return str;
}
function hexToBytes(hex) {

	//https://stackoverflow.com/questions/14603205/how-to-convert-hex-string-into-a-bytes-array-and-a-bytes-array-in-the-hex-strin
	let bytes = [];
    for (let c = 0; c < hex.length; c += 2)
	{
        bytes.push(parseInt(hex.substr(c, 2), 16));
	}
    return bytes;
}
function bytesToHex(bytes) {
    
	let hex = [];
    for (let i = 0; i < bytes.length; i++) {
        let current = bytes[i] < 0 ? bytes[i] + 256 : bytes[i];
        hex.push((current >>> 4).toString(16));
        hex.push((current & 0xF).toString(16));
    }
    return hex.join("");
}



//action
var media;
var mode;
var articleId;
var user;

//get media and redirect if needed
media = getMedia();
if(media != "mobile" && media != "small") showPC();

//get url
var url_string = window.location.href; 
var url = new URL(url_string);
	
//get mode from url parameter if available
var domain = url.searchParams.get("domain");
if (domain != null) mode = domain;
else mode = "public";

//get article from url parameter if available
articleId = url.searchParams.get("article");

//set user to null as we don't know it yet
var user = document.getElementById("userlink").innerText;
if (user && user == "") user = null;
if (user.toLowerCase().includes("log in")) user = null;

//load lists
if(mode == "public") 
{
	loadJson(mode);
	setNavbar_PublicMode();
}
else if(mode == "normative")
{
	loadJson(mode);
	setNavbar_NormativeMode();
}
else if(mode == "personal") 
{
	if(!user || user == null) 
	{
		var param = "?domain=personal";
		if(articleId && articleId != null && articleId.length > 0) param += "&article=" + articleId;
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
	else
	{
		loadJson(mode);
		setNavbar_PersonalMode();
	}
}
else if(mode == "private") 
{
	if(!user || user == null) 
	{
		var param = "?domain=private";
		if(articleId && articleId != null && articleId.length > 0) param += "&article=" + articleId;
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
	else
	{
		loadJson(mode);
		setNavbar_PrivateMode();
	}
}

//start history deamon
historyDeamon.startHistoryDeamon();