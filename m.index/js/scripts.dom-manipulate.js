// Page
function cleanPage() {										// remove all content from containers

	var largeTopContainer = document.getElementById("LargeTopContainer");
	if(largeTopContainer != null) largeTopContainer.innerHTML = "";

	var largeContainer = document.getElementById("LargeContainer");
	if(largeContainer != null) largeContainer.innerHTML = "";
}
function colorElements() {									// debug method - color skeleton elements in different colors

	document.getElementById("TopContainer").style.backgroundColor = "red";
	document.getElementById("SmallContainer").style.backgroundColor = "green";
	document.getElementById("LargeContainer").style.backgroundColor = "blue";
}
function colorHeader(color) {								// color header bar at 60%

	document.getElementById("LargeTopContainer").style.background =
		"linear-gradient(to bottom, " + color + " 60%, transparent 60%)";
}
function colorHeaderPlus2(color) {							// color header bar at 60% + 2 px

	document.getElementById("LargeTopContainer").style.background =
		"linear-gradient(to bottom, " + color + " 0%, " + color + " calc(60% + 1px), transparent calc(60% + 2px))";
}
function scrollToTop() {

  const scrollDuration = 600; // duration of the animation in milliseconds
  const scrollInterval = 16; // approximate frame interval (60 FPS)
  const totalScrollDistance = window.scrollY;
  const scrollStep = totalScrollDistance / (scrollDuration / scrollInterval);

  const interval = setInterval(() => {
    if (window.scrollY > 0) {
      window.scrollBy(0, -scrollStep); // move up by scroll step
    } else {
      clearInterval(interval); // stop when the scroll reaches the top
    }
  }, scrollInterval);
}


// Generic Hide / Unhide
function hideUnhide_ByClass(className) {					// hide or unhide elements with a given class name

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
function hideUnhide_ById(id) {								// hide or unhide element with a given ID

	var el = document.getElementById(id);
	if (el.style.display == "none")
	{
		el.style.display = "block";
	}
	else
	{
		el.style.display = "none";
	}
}
function hide_ById(id) {									// hide element with a given ID

	var el = document.getElementById(id);
	if (el.style.display != "none")
	{
		el.style.display = "none";
	}
}
function unhide_ById(id) {									// unhide element with a given ID

	var el = document.getElementById(id);
	if (el.style.display == "none")
	{
		el.style.display = "block";
	}
}



// SVG Icons
const CloseSvgText = "<svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' style='margin-top:14px;' viewBox='0 0 25 25'><path d='M0,0 L16,16' stroke='#818181' stroke-width='2' fill='none'/><path d='M0,16 L16,0' stroke='#818181' stroke-width='2' fill='none'/></svg>"; //That is SVG 'X'

const RightArrowSvgText = "<svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' style='margin-top:9px;' viewBox='0 0 25 25'><path d='M18,9 L9,0' stroke='#818181' stroke-width='2' fill='none'/><path d='M18,9 L0,9' stroke='#818181' stroke-width='2' fill='none'/><path d='M18,9 L19,9' stroke='#818181' stroke-width='1' fill='none'/><path d='M18,9 L9,18' stroke='#818181' stroke-width='2' fill='none'/></svg>"; //That is SVG '🡢'

const LeftArrowSvgText = "<svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' style='margin-top:9px;' viewBox='0 0 25 25'><path d='M1,9 L10,0' stroke='#818181' stroke-width='2' fill='none'/><path d='M1,9 L19,9' stroke='#818181' stroke-width='2' fill='none'/><path d='M0,9 L1,9' stroke='#818181' stroke-width='1' fill='none'/><path d='M1,9 L10,18' stroke='#818181' stroke-width='2' fill='none'/></svg>"; //That SVG is '🡠'

function setMenuArrowLeft(showFunction) {
	const arr = document.getElementById("menuArrow");
	if (!arr) return;

	const fName = showFunction.name;
	arr.setAttribute('href', `javascript:${fName}();`);
	arr.innerHTML = LeftArrowSvgText;
}
function setMenuArrowRight(showFunction) {
	const arr = document.getElementById("menuArrow");
	if (!arr) return;

	const fName = showFunction.name;
	arr.setAttribute('href', `javascript:${fName}();`);
	arr.innerHTML = RightArrowSvgText;
}


// Navs
function openNav() {										// open sidenav

	document.getElementById("sidenav").style.display = "block";
	document.getElementById("sidenav-trigger").style.display = "none";
	document.getElementById("sidenav").style.width = "250px";
}
function closeNav() {										// close sidenav

	document.getElementById("sidenav").style.display = "none";
	document.getElementById("sidenav-trigger").style.display = "block";
	document.getElementById("sidenav").style.width = "0";
}
function showMainMenu() {

	setMenuArrowLeft(showNextMenu);
	showMenu("MenuMain");

	//ToolUsageHistory.removeTools();

	if (mode == "public")
	{
		hide_ById("modelink_private");
		hide_ById("modelink_normative");
		hide_ById("modelink_public");
	}
	else if (mode == "personal")
	{
		hide_ById("modelink_personal");
		hide_ById("modelink_normative");
		hide_ById("modelink_public");
	}
	else if (mode == "private")
	{
		hide_ById("modelink_personal");
		hide_ById("modelink_private");
		hide_ById("modelink_public");
	}
	else if (mode == "normative")
	{
		hide_ById("modelink_personal");
		hide_ById("modelink_private");
		hide_ById("modelink_normative");
	}
}
function showNextMenu() {

	setMenuArrowRight(showMainMenu);
	ToolManager.DrawTools("MenuNextMiddleAnchor", "MenuNext");
	showMenu("MenuNext");

	hide_ById("MenuNextTopAnchor");
	hide_ById("MenuNextBottomAnchor");
}
function showCopiersMenu() {

	setMenuArrowRight(showNextMenu);
	ToolManager.DrawTools("MenuCopiersMiddleAnchor", "MenuCopiers");
	showMenu("MenuCopiers");

	hide_ById("MenuCopiersBottomAnchor");
}
function showViewersMenu() {

	setMenuArrowRight(showNextMenu);
	ToolManager.DrawTools("MenuViewersBottomAnchor", "MenuViewers");
	showMenu("MenuViewers");

	//ToolUsageHistory.addTools();
}
function showSortersMenu() {

	setMenuArrowRight(showNextMenu);
	ToolManager.DrawTools("MenuSortersBottomAnchor", "MenuSorters");
	showMenu("MenuSorters");
}
function showFiltersMenu() {

	setMenuArrowRight(showNextMenu);
	ToolManager.DrawTools("MenuFiltersMiddleAnchor", "MenuFilters");
	showMenu("MenuFilters");

	hide_ById("MenuFiltersBottomAnchor");
}
function showPickersMenu() {

	setMenuArrowRight(showNextMenu);
	ToolManager.DrawTools("MenuPickersMiddleAnchor", "MenuPickers");
	showMenu("MenuPickers");

	hide_ById("MenuPickersBottomAnchor");
}
function showHighlightersMenu() {

	setMenuArrowRight(showNextMenu);
	showMenu("MenuHighlighters");

	//ToolUsageHistory.addTools();
	hide_ById("MenuHighlightersBottomAnchor");
}
function showPluginsMenu() {

	setMenuArrowRight(showNextMenu);
	showMenu("MenuPlugins");

	//ToolUsageHistory.addTools();
	hide_ById("MenuPluginsBottomAnchor");
}

// Show menus
const menuNames = [
	"MenuMain", "MenuNext", "MenuCopiers",
	"MenuViewers", "MenuSorters", "MenuFilters",
	"MenuPickers", "MenuHighlighters", "MenuPlugins"];

function showMenu(menuName) {

	if(menuNames.includes(menuName) == false) return;

	var editLinks = document.getElementsByClassName(menuName);
	for(var i = 0; i < editLinks.length; i++)
	{
		if(editLinks[i].tagName == "BR") editLinks[i].style.display = "inline";
		else editLinks[i].style.display = "block";
	}

	menuNames.forEach(name => {

		if(name == menuName) return;
		editLinks = document.getElementsByClassName(name);
		for(var i = 0; i < editLinks.length; i++) editLinks[i].style.display = "none";
	});

	//hack
	LocalFileLoader.UnSelect();
	StreamPlayer.UnSelect();
}
