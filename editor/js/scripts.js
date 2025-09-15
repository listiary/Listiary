function hideEditor(returnUrl = null) {
	
	if(returnUrl == null)
	{
		window.history.back();
	}
	else
	{
		window.location.href = returnUrl;
	}
}
async function submitForm(editfileUrl) {

	var editorBox = document.getElementById("editArea");
	var summaryBox = document.getElementById("summaryBox");

	var contnt = editorBox.value;
	var summary = summaryBox.value;
	var isMinor = false;
	if(document.getElementById("minorEdit") && 
		document.getElementById("minorEdit").checked)
	{
		isMinor = document.getElementById("minorEdit").checked;
	}
	if(isMinor) editfileUrl += "&isminor=1";
	else editfileUrl += "&isminor=0";
	
	const data = new URLSearchParams();
	data.append('content', contnt);
	data.append('message', summary);
	const requestOptions = {

		method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: data
    };

	showLoadingModal();
	var response = await fetch(editfileUrl, requestOptions);
	var rtext = await response.text();
	hideLoadingModal();
	
	if(rtext.includes("SUCCEEDED!")) {
		
		//console.log("SUCCEEDED!");
		//alert("SUCCEEDED:<br/>" + rtext);
		//alert("SUCCEEDED:\n" + rtext);
		hideEditor("../index.php");
	}
	else {
		
		console.log("FAILED!");
		alert("FAILED:\n" + rtext);
	}
}
async function submitFormS(editfileUrl, contnt) {

	var summaryBox = document.getElementById("summaryBox");
	var summary = summaryBox.value;
	var isMinor = false;
	if(document.getElementById("minorEdit") && 
		document.getElementById("minorEdit").checked)
	{
		isMinor = document.getElementById("minorEdit").checked;
	}
	if(isMinor) editfileUrl += "&isminor=1";
	else editfileUrl += "&isminor=0";
	
	const data = new URLSearchParams();
	data.append('content', contnt);
	data.append('message', summary);
	const requestOptions = {

		method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: data
    };

	showLoadingModal();
	var response = await fetch(editfileUrl, requestOptions);
	var rtext = await response.text();
	hideLoadingModal();
	
	if(rtext.includes("SUCCEEDED!")) {
		
		//console.log("SUCCEEDED!");
		//alert("SUCCEEDED:<br/>" + rtext);
		//alert("SUCCEEDED:\n" + rtext);
		hideEditor(null); //hideEditor("../index.php");
	}
	else {
		
		console.log("FAILED!");
		alert("FAILED:\n" + rtext);
	}
}
function getNewFileUrl(domain) {

	var value = document.getElementById("newArticleName").value;
	value = "php/_newfile.php?article=" + value + "&domain=" + domain;
	return value;
}
function showLoadingModal() {
	
	var modal = document.getElementById("loadingModal");
	if(!modal) return;
	modal.style.display = "block";
}
function hideLoadingModal() {
	
	var modal = document.getElementById("loadingModal");
	if(!modal) return;
	modal.style.display = "none";
}

//editor options
var tabSymbol = "\t";
var tabLength = 1;
var newLine = "\n";

//editor text box
function editAreaKeyDown(e) {

	if (e.key == 'Tab') 
	{
		e.preventDefault();
		var start = this.selectionStart;
		var end = this.selectionEnd;
		
		//calculate indent
		var indent = "";
		for(var i = 0; i < tabLength; i++) indent += tabSymbol;
		
		//fix
		while(this.value[end] === "\n" && end > start) end--;

		// check if we should indent or replace
		//do the shift+tab
		if(start !== end)
		{
			var slice = this.value.slice(start, end);
			if(slice.includes("\n") && slice.trim().length !== 0)
			{
				var startPlace = start;
				while(startPlace > 0)
				{
					if(this.value[startPlace - 1] === "\n") break;
					startPlace--;
				}
				
				var endPlace = start;
				while(endPlace <= end)
				{
					if(this.value[endPlace + 1] === "\n") break;
					endPlace++;
				}
				
				slice = slice.replaceAll("\n", "\n" + indent);
				slice = indent + slice;
				
				// set textarea value to: text before caret + tab + text after caret
				this.value = this.value.substring(0, start) + slice + this.value.substring(end);

				// put caret at right position again
				this.selectionStart = startPlace; 
				this.selectionEnd = startPlace + slice.length + 1;
				
				return;
			}
		}


		// set textarea value to: text before caret + tab + text after caret
		this.value = this.value.substring(0, start) + indent + this.value.substring(end);

		// put caret at right position again
		this.selectionStart = this.selectionEnd = start + tabLength;
	}
	else if (e.key == 'Enter')
	{
		e.preventDefault();
		var start = this.selectionStart;
		var end = this.selectionEnd;
		
		var text = this.value.substring(0, start);
		var array = text.split('\n');
		
		var x = array.length - 1;
		var line = array[x];
		while(line.trim().length === 0 && x > 0)
		{
			x--;
			line = array[x];
		}
		
		var indent = "";
		var indentLength = 0;
		if(line.trim().length !== 0)
		{
			var trimmed = line.trimStart();
			indentLength = line.length - trimmed.length;
			indent = line.substring(0, indentLength);
		}
		
		// set textarea value to: text before caret + NewLine + text after caret
		this.value = this.value.substring(0, start) + newLine + indent + this.value.substring(end);

		// put caret at right position again
		this.selectionStart = this.selectionEnd = start + (newLine.length + indentLength);
	}
}


document.getElementById('editArea').addEventListener('keydown', editAreaKeyDown);


//sidenav
var inSubMenu = false;
function openNav() {				// open sidenav

	document.getElementById("sidenav").style.display = "block";
	document.getElementById("sidenav-trigger").style.display = "none";
	document.getElementById("sidenav").style.width = "230px";
	showMainMenu();
}
function closeNav() {				// close sidenav

	// if(inSubMenu) showMainMenu();
	// else
	// {
		// document.getElementById("sidenav").style.display = "none";
		// document.getElementById("sidenav-trigger").style.display = "block";
		// document.getElementById("sidenav").style.width = "0";
	// }
	document.getElementById("backbtn").style.display = "none";
	document.getElementById("sidenav").style.display = "none";
	document.getElementById("sidenav-trigger").style.display = "block";
	document.getElementById("sidenav").style.width = "0";
}
function backNav() {
	
	showMainMenu();
	document.getElementById("backbtn").style.display = "none";
}
function showMainMenu() {
	
	var editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++)
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = false;
}
function showFileMenu() {
	
	document.getElementById("backbtn").style.display = "block";
	var editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) 
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = true;
}
function showEditMenu() {
	
	document.getElementById("backbtn").style.display = "block";
	var editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) 
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = true;
}
function showViewMenu() {
	
	document.getElementById("backbtn").style.display = "block";
	var editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) 
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = true;
}
function showInsertMenu() {
	
	document.getElementById("backbtn").style.display = "block";
	var editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++)
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = true;
}
function showFormatMenu() {
	
	document.getElementById("backbtn").style.display = "block";
	var editLinks = document.getElementsByClassName("MenuFormat");
	for(var i = 0; i < editLinks.length; i++) 
	{	
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}
	
	editLinks = document.getElementsByClassName("Menu");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuFile");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuEdit");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuView");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	
	editLinks = document.getElementsByClassName("MenuInsert");
	for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	inSubMenu = true;
}


//menus
function zoomIn() {
	
	var el = document.getElementById('editArea');
	var style = window.getComputedStyle(el, null).getPropertyValue('font-size');
	var fontSize = parseFloat(style); 
	// now you have a proper float for the font size (yes, it can be a float, not just an integer)
	el.style.fontSize = (fontSize + 1) + 'px';
}
function zoomOut() {
	
	var el = document.getElementById('editArea');
	var style = window.getComputedStyle(el, null).getPropertyValue('font-size');
	var fontSize = parseFloat(style); 
	// now you have a proper float for the font size (yes, it can be a float, not just an integer)
	if(fontSize > 6) el.style.fontSize = (fontSize - 1) + 'px';
}
function toggleHighlighting() {
	
	var el = document.getElementById('editArea');
	var attribute = el.getAttribute('spellcheck');
	
	if (attribute == "false") el.setAttribute("spellcheck", "true");
	else el.setAttribute("spellcheck", "false");
}
