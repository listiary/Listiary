function addLargeTree(id) {					// add tree

	document.getElementById("LargeContainer").innerHTML = "<ul class='mainlistree'></ul>";
	var topelement = document.getElementById("LargeTopContainer");
	var contelement = document.getElementById("LargeContainer");
	mainTree.Init(topelement, contelement);
	mainTree.Draw(articleId);
	//addFooter(json);
}
function addFooter(json) {					// add compilation timestamp message

	//https://www.w3schools.com/howto/howto_css_fixed_footer.asp
	if(media == "mobile" || media == "small")
	{
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-style: italic; margin: 7px; padding: 10px; padding-top: 0px; color: gray;'><hr />"
		html += json.short_timestamp;
		html += "<br />";
		html += json.short_version;
		html += "</div>";
		html += "</div>";
		document.getElementById("LargeFooter").innerHTML = html;
	}
	else
	{
		var html = "<div style='left: 0; bottom: 0; width: 100%;'>"
		html += "<div style='font-style: italic; margin: 7px; padding: 10px; padding-top: 0px; margin-top: 0px; color: gray;'><hr />"
		html += json.timestamp;
		html += "<br />";
		html += json.version;
		html += "</div>";
		html += "</div>";
		document.getElementById("LargeFooter").innerHTML = html;
	}
}
