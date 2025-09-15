class ListiaryTool {

    //constants
    ToolId = "";                                    //trigger DOM element id and unique Element id
	ToolName = "";                                  //trigger DOM element text
	ToolIcon = "";                                  //trigger DOM element icon
	ToolIconBold = "";                              //trigger DOM element icon (selected)
	TriggerClass = "";                              //trigger DOM element class - or which submenu to put it in

	//DOM items
	TriggerElement = null;                          //main trigger DOM element
	NextTriggerElement = null;                      //trigger that is added in other menus
	AdditionalTriggerElements = [];                 //additional triggers DOM elements
	InstanceName = null;                            //this is the name of the var we keep this instance of the class in

	//ListiaryToolManager
	ToolManager = null;
    ToolCategory = "";								//category for the active tool tracker -
													//so that we know which category of tools to switch off
													//when this one is selected

	constructor(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

        this.InstanceName = instanceName;

		this.ToolName = toolName;
        this.ToolIcon = toolIcon;
        this.ToolIconBold = toolIconBold;
        this.ToolId = toolId;
		if(triggerClass != null) this.TriggerClass = triggerClass;
        this._createTriggerElement(this.ToolName, this.ToolIcon);
        this._createNextTriggerElement(this.ToolName, this.ToolIcon, "MenuNext");
	}
	Init(listiaryToolManager, toolCategory) {

		this.ToolManager = listiaryToolManager;
        this.ToolCategory = toolCategory;
        this.ToolManager.RegisterTool(this.ToolId, this);
		this.ToolManager.ActiveToolsTracker.RegisterTool(this.ToolId, this.UnSelect.bind(this));
	}


	//add / select / unselect triggers
	AddAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerElement);
	}
	AddNewTriggerAfter(anchorElementId, triggerClass) {

		var elem = this._createNewTriggerElement(this.ToolName, this.ToolIcon, this.AdditionalTriggerElements.length, triggerClass);
		_addAfter(anchorElementId, this.TriggerElement);
	}
	Select() {

        this.ToolManager.RecordUsage(this.ToolId);
        this.ToolManager.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ToolManager.ActiveToolsTracker.SetTool(this.ToolId, this.ToolCategory);

		//triggers
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
        this.NextTriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".UnSelect();";
		}
	}
	UnSelect() {

        this.ToolManager.ActiveToolsTracker.UnsetTool(this.ToolId, this.ToolCategory);

		//triggers
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
        this.NextTriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".Select();";
		}
	}


	//create triggers
	_createTriggerElement(triggerText, triggerIcon) {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();");
		var elem = _getElement(html);
		this.TriggerElement = elem;
		return elem;
	}
	_createNextTriggerElement(triggerText, triggerIcon, triggerClass) {

		var html = _createTriggerElement(this.ToolId, triggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();");
		var elem = _getElement(html);
		this.NextTriggerElement = elem;
		return elem;
	}
	_createNewTriggerElement(triggerText, triggerIcon, idnum, triggerClass) {

		var html = _createTriggerElement(this.ToolId + idnum, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();")
		var elem = _getElement(html);
		this.AdditionalTriggerElements.push(elem);
		return elem;
	}
	_enboldTriggerElement() {

		_enboldTriggerElement(this.TriggerElement, this.ToolIconBold);
        _enboldTriggerElement(this.NextTriggerElement, this.ToolIconBold);
		this._enboldAdditionalTriggerElements();
	}
	_deboldTriggerElement() {

		_deboldTriggerElement(this.TriggerElement, this.ToolIcon);
        _deboldTriggerElement(this.NextTriggerElement, this.ToolIcon);
		this._deboldAdditionalTriggerElements();
	}
	_enboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_enboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIconBold);
		}
	}
	_deboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_deboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIcon);
		}
	}
}
class ListiaryBranchingTool extends ListiaryTool {

	OldArticleId = null;
	NewArticleId = null;

	Select() {

		//execute base logic
		super.Select();

		//make new tree list
		var tree = namespaces[articleId];
		var newTree = { ...tree, items: [...tree.items], links: [...tree.links], decorators: [...tree.decorators] };

		//add it as version
		this.OldArticleId = articleId;
		this.NewArticleId = this._getRandomString(16);

		//add it as version
		namespaces[this.NewArticleId] = newTree;
		articleId = this.NewArticleId;
	}
	UnSelect() {

		//execute base logic
		super.UnSelect();

		//do
		articleId = this.OldArticleId;
		this.OldArticleId = null;
		this.NewArticleId = null;
		addLargeTree(articleId);
	}

	_getRandomString(length = 16) {

		const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}
}
class ListiaryToolDummy {

    //constants
    ToolId = "";                                    //trigger DOM element id and unique Element id
	ToolName = "";                                  //trigger DOM element text
	ToolIcon = "";                                  //trigger DOM element icon
	ToolIconBold = "";                              //trigger DOM element icon (selected)
	TriggerClass = "";                              //trigger DOM element class - or which submenu to put it in

	//DOM items
	TriggerElement = null;                          //main trigger DOM element
	AdditionalTriggerElements = [];                 //additional triggers DOM elements
	InstanceName = null;                            //this is the name of the var we keep this instance of the class in


	constructor(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

        this.InstanceName = instanceName;

		this.ToolName = toolName;
        this.ToolIcon = toolIcon;
        this.ToolIconBold = toolIconBold;
        this.ToolId = toolId;
		if(triggerClass != null) this.TriggerClass = triggerClass;
        this._createTriggerElement(this.ToolName, this.ToolIcon);
	}


	//add / select / unselect triggers
	AddAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerElement);
	}
	AddNewTriggerAfter(anchorElementId, triggerClass) {

		var elem = _createNewTriggerElement(this.ToolName, this.ToolIconGrey, this.AdditionalTriggers.length, triggerClass);
		_addAfter(anchorElementId, this.TriggerElement);
	}
	Select() {

		//triggers
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".UnSelect();";
		}
	}
	UnSelect() {

		//triggers
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".Select();";
		}
	}


	//create triggers
	_createTriggerElement(triggerText, triggerIcon) {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();");
		var elem = _getElement(html);
		this.TriggerElement = elem;
		return elem;
	}
	_createNewTriggerElement(triggerText, triggerIcon, idnum, triggerClass) {

		var html = _createTriggerElement(this.ToolId + idnum, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();")
		var elem = _getElement(html);
		this.AdditionalTriggerElements.push(elem);
		return elem;
	}
	_enboldTriggerElement() {

		_enboldTriggerElement(this.TriggerElement, this.ToolIconBold);
		this._enboldAdditionalTriggerElements();
	}
	_deboldTriggerElement() {

		_deboldTriggerElement(this.TriggerElement, this.ToolIcon);
		this._deboldAdditionalTriggerElements();
	}
	_enboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_enboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIconBold);
		}
	}
	_deboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_deboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIcon);
		}
	}
}
class ListiaryToolUnselectableDummy {

    //constants
    ToolId = "";                                    //trigger DOM element id and unique Element id
	ToolName = "";                                  //trigger DOM element text
	ToolIcon = "";                                  //trigger DOM element icon
	ToolIconBold = "";                              //trigger DOM element icon (selected)
	TriggerClass = "";                              //trigger DOM element class - or which submenu to put it in

	//DOM items
	TriggerElement = null;                          //main trigger DOM element
	AdditionalTriggerElements = [];                 //additional triggers DOM elements
	InstanceName = null;                            //this is the name of the var we keep this instance of the class in


	constructor(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

        this.InstanceName = instanceName;

		this.ToolName = toolName;
        this.ToolIcon = toolIcon;
        this.ToolIconBold = toolIconBold;
        this.ToolId = toolId;
		if(triggerClass != null) this.TriggerClass = triggerClass;
        this._createTriggerElement(this.ToolName, this.ToolIcon);
	}


	//add / select / unselect triggers
	AddAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerElement);
	}
	AddNewTriggerAfter(anchorElementId, triggerClass) {

		var elem = _createNewTriggerElement(this.ToolName, this.ToolIconGrey, this.AdditionalTriggers.length, triggerClass);
		_addAfter(anchorElementId, this.TriggerElement);
	}
	Select() {

		//triggers
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".UnSelect();";
		}

		this.UnSelect();
	}
	UnSelect() {

		//triggers
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			this.AdditionalTriggerElements[i].href = "javascript:" + this.InstanceName + ".Select();";
		}
	}


	//create triggers
	_createTriggerElement(triggerText, triggerIcon) {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();");
		var elem = _getElement(html);
		this.TriggerElement = elem;
		return elem;
	}
	_createNewTriggerElement(triggerText, triggerIcon, idnum, triggerClass) {

		var html = _createTriggerElement(this.ToolId + idnum, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();")
		var elem = _getElement(html);
		this.AdditionalTriggerElements.push(elem);
		return elem;
	}
	_enboldTriggerElement() {

		_enboldTriggerElement(this.TriggerElement, this.ToolIconBold);
		this._enboldAdditionalTriggerElements();
	}
	_deboldTriggerElement() {

		_deboldTriggerElement(this.TriggerElement, this.ToolIcon);
		this._deboldAdditionalTriggerElements();
	}
	_enboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_enboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIconBold);
		}
	}
	_deboldAdditionalTriggerElements() {

		for(let i = 0; i < this.AdditionalTriggerElements.length; i++)
		{
			_deboldTriggerElement(this.AdditionalTriggerElements[i], this.ToolIcon);
		}
	}
}

