class ListiaryPlugin {

	Version = "";
	IsEnabled = true;
	IsOn = true;

    //constants
    ToolId = "";                                    //trigger DOM element id and unique Element id
	ToolName = "";                                  //trigger DOM element text
	ToolIcon = "";                                  //trigger DOM element icon
	ToolIconBold = "";                              //trigger DOM element icon (selected)
	TriggerClass = "MenuPlugins";                   //trigger DOM element class - or which submenu to put it in

	//DOM items
	TriggerElement = null;                          //main trigger DOM element
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
	}
	Init(listiaryToolManager, toolCategory) {

		//check if we should activate the plugin
		if(this._shouldActivate() == false) return;

		//add to manager
		this.ToolManager = listiaryToolManager;
        this.ToolCategory = toolCategory;
        this.ToolManager.RegisterTool(this.ToolId, this);
		this.ToolManager.ActiveToolsTracker.RegisterTool(this.ToolId, this.UnSelect.bind(this));
	}
	_shouldActivate(){

		//we can add additional logic here, if we must
		//like for example check for any incompatible plugins
		if(this.IsEnabled == false) return false;
		return true;
	}


	//add / select / unselect triggers
	AddAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerElement);
	}
	Select() {

        //this.ToolManager.RecordUsage(this.ToolId);
        this.ToolManager.ActiveToolsTracker.UnsetCategory(this.ToolCategory);
		this.ToolManager.ActiveToolsTracker.SetTool(this.ToolId, this.ToolCategory);

		//triggers
		this._enboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".UnSelect();";
	}
	UnSelect() {

        this.ToolManager.ActiveToolsTracker.UnsetTool(this.ToolId, this.ToolCategory);

		//triggers
		this._deboldTriggerElement();
		this.TriggerElement.href = "javascript:" + this.InstanceName + ".Select();";
	}


	//create triggers
	_createTriggerElement(triggerText, triggerIcon) {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass, triggerText, triggerIcon, this.InstanceName + ".Select();");
		var elem = _getElement(html);
		this.TriggerElement = elem;
		return elem;
	}
	_enboldTriggerElement() {

		_enboldTriggerElement(this.TriggerElement, this.ToolIconBold);
	}
	_deboldTriggerElement() {

		_deboldTriggerElement(this.TriggerElement, this.ToolIcon);
	}
}
