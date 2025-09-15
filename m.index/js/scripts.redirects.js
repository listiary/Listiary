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
async function showMobile(articleName = "") {

	if(articleName == null || articleName == "")
	{
		if(mode != undefined) window.location.href = "./m.index.php?domain=" + mode;
		else window.location.href = "./m.index.php?domain=public";
	}
	else
	{
		window.location.href = "./m.index.php?article="
			+ articleName + "&domain=" + mode;
	}
}
