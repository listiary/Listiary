class LocalFileLoaderPlugin {

	Version = "0.9";
	IsEnabled = true;
	LocalFiles = {
		
		"Radiowatch" : 			"sources/payload(radiowatch).js",
		"My Taskade" : 			"sources/payload(my taskade).js",
		"MyPasswords" : 		"sources/payload(passwords).js"
	};
	_index = 0;
	
	
	
	Init() {
		
		//check if we should activate the plugin
		if(this._shouldActivate() == false) return;
		
		//add the UI elements
		// ...
	}
	_shouldActivate(){
		
		if(this.IsEnabled == false) return false;
		return true;
	}
	
	
	
	LoadPreviousFile() {
		
		const keys = Object.keys(this.LocalFiles);
		if(this._index > 0) this._index--;
		else this._index = keys.length - 1;
		
		var name = keys[this._index];
		this.LoadLocal(name);
	}
	LoadNextFile() {
		
		const keys = Object.keys(this.LocalFiles);
		if(this._index < keys.length - 1) this._index++;
		else this._index = 0;
		
		var name = keys[this._index];
		this.LoadLocal(name);
	}
	LoadLocal(name) {
		
		var url = this.LocalFiles[name];
		if(!url) return;
		
		this.LoadScript(url, () => {

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
	LoadScript(url, callback) {
		
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

// init the Class
var LocalFileLoader = new LocalFileLoaderPlugin();
