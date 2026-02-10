class LocalLoaderPlugin extends ListiaryPlugin {

	//local vars
	LocalFiles = {

		"Radiowatch" : 			"sources/payload(radiowatch).js",
		"My Taskade" : 			"sources/payload(my taskade).js",
		"My Porn" : 			"sources/payload(porn).js",
		"MyPasswords" : 		"sources/payload(passwords).js"
	};
	_index = 0;

	//DOM items
	TriggerOnOff = null;
	TriggerLoadNext = null;
	TriggerLoadPrev = null;


	constructor(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);

		// Settings and such
		this.Version = "0.9";
		this.IsEnabled = true;
		this.IsOn = true;

		//add control triggers
		this.TriggerOnOff = this._createTriggerOnOff();
		this.TriggerLoadNext = this._createTriggerNext();
		this.TriggerLoadPrev = this._createTriggerPrev();
	}
	AddControlsAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerLoadPrev);
		_addAfter(anchorElementId, this.TriggerLoadNext);
		_addAfter(anchorElementId, this.TriggerOnOff);
	}
	Select() {

		//execute base logic
		super.Select();

		//show controls
		this.TriggerOnOff.style.display = "block";
		this.TriggerLoadNext.style.display = "block";
		this.TriggerLoadPrev.style.display = "block";
	}
	UnSelect() {

		//execute base logic
		super.UnSelect();

		//hide controls
		this.TriggerOnOff.style.display = "none";
		this.TriggerLoadNext.style.display = "none";
		this.TriggerLoadPrev.style.display = "none";
	}



	//create triggers
	_createTriggerNext() {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass + "-" + this.ToolId, "Load Next",
				"m.index/img/folder-open-bold-gray.png", this.InstanceName + ".LoadNextFile();");
			var elem = _getElement(html);
			elem.style.display = "none";
			return elem;
	}
	_createTriggerPrev() {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass + "-" + this.ToolId, "Load Prev",
				"m.index/img/folder-open-bold-gray.png", this.InstanceName + ".LoadPreviousFile();");
			var elem = _getElement(html);
			elem.style.display = "none";
			return elem;
	}
	_createTriggerOnOff() {

		if(this.IsOn)
		{
			var html = _createTriggerElement(this.ToolId, this.TriggerClass + "-" + this.ToolId, "Plugin: On",
				"m.index/img/check-circle-bold-gray.png", this.InstanceName + ".UnSelectOnOff();");
			var elem = _getElement(html);
			elem.style.display = "none";
			return elem;
		}
		else
		{
			var html = _createTriggerElement(this.ToolId, this.TriggerClass, "Plugin: Off",
				"m.index/img/x-circle-bold-gray.png", this.InstanceName + ".SelectOnOff();");
			var elem = _getElement(html);
			elem.style.display = "none";
			return elem;
		}
	}
	SelectOnOff() {

		this.IsOn = true;
		this.TriggerOnOff.href = "javascript:" + this.InstanceName + ".UnSelectOnOff();";
		this.TriggerOnOff.querySelector("img").src = "m.index/img/check-circle-bold-gray.png";
		this.TriggerOnOff.querySelector("span").innerHTML = "Plugin: On";
	}
	UnSelectOnOff() {

		this.IsOn = false;
		this.TriggerOnOff.href = "javascript:" + this.InstanceName + ".SelectOnOff();";
		this.TriggerOnOff.querySelector("img").src = "m.index/img/x-circle-bold-gray.png";
		this.TriggerOnOff.querySelector("span").innerHTML = "Plugin: Off";
	}



	//functional
	LoadPreviousFile() {

		if(!this.IsOn) return;

		const keys = Object.keys(this.LocalFiles);
		if(this._index > 0) this._index--;
		else this._index = keys.length - 1;

		var name = keys[this._index];
		this._loadLocal(name);
	}
	LoadNextFile() {

		if(!this.IsOn) return;

		const keys = Object.keys(this.LocalFiles);
		if(this._index < keys.length - 1) this._index++;
		else this._index = 0;

		var name = keys[this._index];
		this._loadLocal(name);
	}
	_loadLocal(name) {

		var url = this.LocalFiles[name];
		if(!url) return;

		this._loadScript(url, () => {

			namespaces = {};
			json = JSON_PAYLOAD;
			if (articleId == null) articleId = json.items[0].id;
			populateNamespaces(json);

			//if our articleId is non-existant, load home article instead
			if (namespaces[articleId] == null) articleId = json.items[0].id;

			addLargeTree(articleId);
			//addOpsWidget(articleId);
		});
	}
	_loadScript(url, callback) {

		const script = document.createElement("script");
		script.src = url;

		script.onload = () => {
			console.log("Script loaded:", url);
			callback();
		};

		script.onerror = () => {
			console.error("Failed to load script:", url);
		};

		document.head.appendChild(script);
	}
}
