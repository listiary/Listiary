function _getBr(brClass) {											//create <br> DOM element with a given class

	var html = "<br class='" + brClass + "'>";
	const tempContainer = document.createElement("div");
	tempContainer.innerHTML = html;
	var elem = tempContainer.firstElementChild;
	tempContainer.remove();
	return elem;
}
function _getElement(html) {										//create DOM element from a given HTML

	if (!html || html.length < 3) return null;

	try
	{
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		var elem = tempContainer.firstElementChild;
		tempContainer.remove();
		return elem;
	}
	catch (err)
	{
		console.warn("Invalid HTML passed to _getElement:", html, err);
		return null;
	}
}
function _addAfter(anchorElementId, domElement) {					//insert DOM element after a given DOM element

	if(!domElement) return;

	const anchorElement = document.getElementById(anchorElementId);  			//console.log(anchorElement.outerHTML);
	const parent = anchorElement.parentNode; 									//console.log(parent.outerHTML);
	if (anchorElement.nextSibling)
	{
		parent.insertBefore(domElement, anchorElement.nextSibling);				//console.log(domElement);
	}
	else
	{
		parent.appendChild(domElement);
	}																			//console.log(parent.outerHTML);
}


function _getTriggerElement(elemId, elemClass, 						//create trigger DOM element for a menu
	triggerText, triggerIconSrc, activationJavaScript) {

	var html = _createTriggerElement(elemId, elemClass, triggerText, triggerIconSrc, activationJavaScript);
	const tempContainer = document.createElement("div");
	tempContainer.innerHTML = html;
	var elem  = tempContainer.firstElementChild;
	tempContainer.remove();

	return elem;
}
function _createTriggerElement(elemId, elemClass, 					//create trigger element HTML for a menu
	triggerText, triggerIconSrc, activationJavaScript) {

	//activationJavaScript = this.InstanceName + ".Select();'

	var html = "<a id='" + elemId + "' style='display: none; margin-top: -10px; margin-bottom: -10px;' href='javascript:" + activationJavaScript + "' class='" + elemClass + "'><img src='" + triggerIconSrc + "' style='margin-top:6px; margin-bottom: 10px; width:18px; height:18px; vertical-align: text-top;' /><span style='padding-left:10px; font-size: 0.8em; margin-left: 7px;'>" + triggerText + "</span></a>";

	return html;
}
function _enboldTriggerElement(triggerElement, boldIconSrc) {		//select trigger element

	let menuitemSpan = triggerElement.querySelector('span');
	menuitemSpan.style.color = 'black';
	menuitemSpan.style.fontStyle = 'italic';
	menuitemSpan.style.fontWeight = 'bold';
	let menuitemImg = triggerElement.querySelector('img');
	menuitemImg.src = boldIconSrc;
}
function _deboldTriggerElement(triggerElement, normalIconSrc) {		//unselect trigger element

	let menuitemSpan = triggerElement.querySelector('span');
	menuitemSpan.style.color = '#818181';
	menuitemSpan.style.fontStyle = 'normal';
	menuitemSpan.style.fontWeight = 'normal';
	let menuitemImg = triggerElement.querySelector('img');
	menuitemImg.src = normalIconSrc;
}
