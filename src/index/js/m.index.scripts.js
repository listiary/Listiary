var media, mode, articleId, userName;
var json, namespaces;						// original JSON and 'Namespace.Full.Name - item' map



//get media and redirect if needed
media = mobileDetector.getMedia();
//if(media != "mobile" && media != "small") showPC();

//get url
var url_string = window.location.href; 
var url = new URL(url_string);
	
//get mode from url parameter if available
var domain = url.searchParams.get("domain");
if (domain != null) mode = domain;
else mode = "public";

//get article from url parameter if available
articleId = url.searchParams.get("article");

//set userName to null as we don't know it yet
var userName = document.getElementById("userlink").innerText;
if (userName && userName == "") userName = null;
if (userName.toLowerCase().includes("log in")) userName = null;

//load lists
if(mode == "public") 
{
	loadJson(mode);
	if(options.UseHeaderStripColors)
	{
		colorHeaderPlus2(options.HeaderStripColors.Public);
		mainTree.Color = options.HeaderStripColors.Public;
	}
}
else if(mode == "normative")
{
	loadJson(mode);
	if(options.UseHeaderStripColors)
	{
		colorHeaderPlus2(options.HeaderStripColors.Public);
		mainTree.Color = options.HeaderStripColors.Public;
	}
}
else if(mode == "personal") 
{
	if(!userName || userName == null)
	{
		var param = "?domain=personal";
		if(articleId && articleId != null && articleId.length > 0) param += "&article=" + articleId;
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
	else
	{
		loadJson(mode);
	}
	if(options.UseHeaderStripColors)
	{
		colorHeaderPlus2(options.HeaderStripColors.Public);
		mainTree.Color = options.HeaderStripColors.Public;
	}
}
else if(mode == "private") 
{
	if(!userName || userName == null)
	{
		var param = "?domain=private";
		if(articleId && articleId != null && articleId.length > 0) param += "&article=" + articleId;
		window.location.href = "https://development.worldinlists.net/session/m.login.php" + param;
	}
	else
	{
		loadJson(mode);
	}
	if(options.UseHeaderStripColors)
	{
		colorHeaderPlus2(options.HeaderStripColors.Public);
		mainTree.Color = options.HeaderStripColors.Public;
	}
}
