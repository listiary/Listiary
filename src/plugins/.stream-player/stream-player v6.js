class StreamPlayerPlugin extends ListiaryPlugin {

	//We have 3 modes - TestMode, DecoratorMode and ExtentionMode
	//in TestMode, the plugin is activated in specific lists
	//in decorator mode, the plugin is activated for lists decorated with "radio-stream" decorator. The last link is used as a source.
	//in extension mode, the plugin is activated for lists that have links that end in .aac .ogg or .opus
	
	//DOM items
	TriggerOnOff = null;
	TriggerColor = null;

	constructor(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass = null) {

		super(instanceName, toolName, toolIcon, toolIconBold, toolId, triggerClass);

		// Settings and such
		this.Version = "0.9";
		this.IsEnabled = true;
		this.IsOn = true;

		//add control triggers
		this.TriggerOnOff = this._createTriggerOnOff();
		this.TriggerColor = this._createTriggerColor();
	}
	AddControlsAfter(anchorElementId) {

		_addAfter(anchorElementId, this.TriggerColor);
		_addAfter(anchorElementId, this.TriggerOnOff);
	}
	Select() {

		//execute base logic
		super.Select();

		//show controls
		this.TriggerOnOff.style.display = "block";
		this.TriggerColor.style.display = "block";
	}
	UnSelect() {

		//execute base logic
		super.UnSelect();

		//hide controls
		this.TriggerOnOff.style.display = "none";
		this.TriggerColor.style.display = "none";
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

	_createTriggerColor() {

		var html = _createTriggerElement(this.ToolId, this.TriggerClass + "-" + this.ToolId,
			 "Color: " + this.colorThemes[this.colorThemeIndex].Name, "m.index/img/palette-bold-gray.png",
			 this.InstanceName + ".SelectColor();");
		var elem = _getElement(html);
		elem.style.display = "none";
		return elem;
	}
	SelectColor() {

		if(this.colorThemeIndex < this.colorThemes.length - 1) this.colorThemeIndex++;
		else this.colorThemeIndex = 0;
		this.TriggerColor.querySelector("span").innerHTML = "Color: " + this.colorThemes[this.colorThemeIndex].Name;

		//redraw
		const element = document.getElementById("radio-visualizer");
		element.parentNode.removeChild(element);
		this.AddPlayer();
		this.coloredAnchors.forEach(anchor =>
		{
			anchor.style.cursor = 'pointer';
			//unhighlight_Text(anchor);
			highlight_Text(anchor, this.colorThemes[this.colorThemeIndex].Hex);
		});
	}


	//local vars
	UseColors = true;
	TestMode = false;
	AddByUrlExtensionMode = true;

	//UI colors
	colorThemeIndex = 0;
	colorThemes =
	[
		{ Name: "Mint", Hex: "#b7e4c7" },      // Pastel Mint
		{ Name: "Peach", Hex: "#ffd8c2" },     // Pastel Peach
		{ Name: "Lavender", Hex: "#cdb4db" },  // Pastel Lavender
		{ Name: "Blue", Hex: "#a2d2ff" },      // Pastel Blue
		{ Name: "Yellow", Hex: "#fff9b1" },    // Pastel Yellow
		{ Name: "Coral", Hex: "#ffb3ab" }      // Pastel Coral
	];
	coloredAnchors = [];

	//with source element and type='audio/mp3'
	//html = "<canvas id='canvas' style='margin-top: 20px; border: 1px solid #ccc; width: 100%; height: 100px; max-width: 12cm; border-radius: 10px; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);'></canvas><br><audio crossorigin='anonymous' id='audio' controls='' style='width: 100%; margin-top: 10px; max-width: 12cm;'><source id='streamPlayerPlugin_audioSource' src='https://play.global.audio/nrj64' type='audio/mp3'>Not supported</audio><br><br>";

	//no title, no source element
	//html = "<canvas id='canvas' style='margin-top: 20px; border: 1px solid #ccc; width: 100%; height: 100px; max-width: 12cm; border-radius: 10px; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);'></canvas><br><audio crossorigin='anonymous' id='audio' controls='' style='width: 100%; margin-top: 10px; max-width: 12cm;'>Not supported</audio><br><br>";

	//title at top
	//html = "<div id='radio-visualizer' style='max-width: 12cm;'><div id='stream-player-track-title' style='background-color: #b7e4c7; padding: 8px 12px; margin-right:-2px; border-radius: 10px 10px 0 0; font-weight: bold; font-family: sans-serif; text-align: center; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);'>Now Playing: Dummy Radio Station</div><canvas id='canvas' style='margin-top: 0; border: 1px solid #ccc; width: 100%; height: 100px; border-radius: 0 0 10px 10px; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3); display: block;'></canvas><audio crossorigin='anonymous' id='audio' controls style='width: 100%; margin-top: 10px; display: block;'>Not supported</audio></div>";

	//title at bottom
	//html = "<div id='radio-visualizer' style='max-width: 12cm;'><canvas id='canvas' style='border: 1px solid #ccc; width: 100%; height: 100px; border-radius: 10px 10px 0 0; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3); display: block;'></canvas><div id='stream-player-track-title' style='background-color: " + this.colorThemes[this.colorThemeIndex].Hex + "; padding: 8px 12px; margin-right:-2px; border-radius: 0 0 10px 10px; font-weight: bold; font-family: sans-serif; text-align: center; box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);'>Now Playing: Dummy Radio Station</div><audio crossorigin='anonymous' id='audio' controls style='width: 100%; margin-top: 10px; display: block;'>Not supported</audio></div>";
	
	testModeListIds = [
		
		"radiowatch.raw.bg_radio1rock",
		"radiowatch.raw.bg_energy"
	];
	
	_audio; _canvas; _ctx;
	_audioContext; _analyser; _bufferLength; _dataArray;
	startingUrl = null;
	startingTitle = null;

	AddPlayer() {

		var html = "<div id='radio-visualizer' style='max-width: 12cm;'><canvas id='canvas' style='border: 1px solid #ccc; width: 100%; height: 100px; border-radius: 10px 10px 0 0; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3); display: block;'></canvas><div id='stream-player-track-title' style='background-color: " + this.colorThemes[this.colorThemeIndex].Hex + "; padding: 8px 12px; margin-right:-2px; border-radius: 0 0 10px 10px; font-weight: bold; font-family: sans-serif; text-align: center; box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);'>Now Playing: Dummy Radio Station</div><audio crossorigin='anonymous' id='audio' controls style='width: 100%; margin-top: 10px; display: block;'>Not supported</audio></div>";

		//check if we should activate the plugin
		if(this._shouldAdd() == false) return;
		
		//add the UI elements
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = html;
		var element = document.getElementById("listTopSpace");
		const parent = element.parentNode;
		if (element.nextSibling) parent.insertBefore(tempContainer, element);
		else parent.appendChild(tempContainer);
		
		//load starting source
		this._audio = document.getElementById('audio');
		this._audio.src = this.startingUrl;
		document.getElementById("stream-player-track-title").innerHTML = this.startingTitle;
		this._audio.load();

		//add visualizer functionality
		this._canvas = document.getElementById('canvas');
		this._ctx = canvas.getContext('2d');
		
		// Audio context and analyser setup
		this._audioContext = new (window.AudioContext || window.webkitAudioContext)();
		this._analyser = this._audioContext.createAnalyser();
		this._analyser.fftSize = 512; // Better resolution
		this._bufferLength = this._analyser.frequencyBinCount;
		this._dataArray = new Uint8Array(this._bufferLength);

		// Connect the audio element to the analyser node
		this._source = this._audioContext.createMediaElementSource(this._audio);
		this._source.connect(this._analyser);
		this._analyser.connect(this._audioContext.destination);
		
		// Bind methods to preserve `this`
		this._drawBars = this._drawBars.bind(this);
		this._drawBars_Gradient = this._drawBars_Gradient.bind(this);
		this._drawWaveform = this._drawWaveform.bind(this);
		
		// Choose whether to draw the waveform or the expanding bars
		let drawingFunction = this._drawBars; // Start with bars expanding both ways
		// let drawingFunction = this._drawWaveform; // Uncomment this to switch to waveform
		if(this.UseColors)
		{
			drawingFunction = this._drawBars_Gradient; // Start with bars expanding both ways
			// drawingFunction = this._drawWaveform_Gradient; // Uncomment this to switch to waveform
		}

		// Start drawing when audio starts
		this._audio.onplay = () => {
			
			this._audioContext.resume().then(() => {
				drawingFunction(); // Start visualizing
			});
		};
		
		// Add triggers
		document.querySelectorAll('.treedata').forEach(element => 
		{	
			//check if element contains a stream
			let spans = element.querySelectorAll('span');
			spans.forEach(span => 
			{
				span.style.cursor = 'pointer';
				span.onclick = (event) => 
				{
					event.stopPropagation();
					let source = document.getElementById('streamPlayerPlugin_audioSource');
					source.src = url;
					audio.load();
					audio.play();
				}
			});
			let anchors = element.querySelectorAll('a');
			
			//omit 'tomato' colored links
			const color = getComputedStyle(element).color;
			if (color === "rgb(255, 99, 71)") return;
			
			let url = "";
			anchors.forEach(anchor => 
			{
				url = anchor.href;
				if(this.AddByUrlExtensionMode && this._isPlayableByExtension(url))
				{
					anchor.style.cursor = 'pointer';
					//unhighlight_Text(anchor);
					highlight_Text(anchor, this.colorThemes[this.colorThemeIndex].Hex);
					anchor.href = "javascript:void(0);";
					anchor.onclick = (event) =>
					{
						event.preventDefault();
						event.stopPropagation();
						document.getElementById("stream-player-track-title").innerHTML = element.innerText;
						//let source = document.getElementById('streamPlayerPlugin_audioSource'); source.src = url;
						this._audio.src = url;
						audio.load();
						audio.play();
					}
					this.coloredAnchors.push(anchor);
				}
			});
		});
	}
	_shouldAdd(){
		
		if(this.IsEnabled == false) return false;
		if(this.TestMode)
		{
			if(this.testModeListIds.includes(articleId) == false) return false;
			else return true;
		}
		else if (this.AddByUrlExtensionMode == false)
		{
			return this._shouldAddByDecorator();
		}
		else //this.AddByUrlExtensionMode == true
		{
			return this._shouldAddByUrlExtensionType();
		}
	}
	_shouldAddByDecorator(){

		var titleItem = namespaces[articleId];
		if(titleItem.decorators.includes("radio-stream") && titleItem.links.length > 0)
		{
			this.startingUrl = titleItem.links[titleItem.links.length - 1].url;
			return true;
		}
		if(titleItem.items && titleItem.items.length > 0)
		{
			titleItem.items.forEach(function(item)
			{
				if(item.decorators.includes("radio-stream") && item.links.length > 0)
				{
					this.startingUrl = item.links[item.links.length - 1].url;
					return true;
				}
			});
		}
		return false;
	}
	_shouldAddByUrlExtensionType() {

		const suffixes = [".aac", ".aac/", ".ogg", ".ogg/", ".opus", ".opus/", ".mp3", ".mp3/"];
		const titleItem = namespaces[articleId];

		if (titleItem.links && titleItem.links.length > 0)
		{
			const match = titleItem.links.find(link =>
				typeof link.url === "string" &&
				suffixes.some(suffix => link.url.toLowerCase().endsWith(suffix))
			);
			if (match)
			{
				this.startingUrl = match.url;
				this.startingTitle = titleItem.text;
				return true;
			}
		}

		if (titleItem.items && titleItem.items.length > 0)
		{
			for (const item of titleItem.items)
			{
				if (item.links && item.links.length > 0)
				{
					const match = item.links.find(link =>
						typeof link.url === "string" &&
						suffixes.some(suffix => link.url.toLowerCase().endsWith(suffix))
					);
					if (match)
					{
						this.startingUrl = match.url;
						this.startingTitle = item.text;
						return true;
					}
				}
			}
		}

		return false;
	}
	_isPlayableByExtension(url) {

		const suffixes = [".aac", ".aac/", ".ogg", ".ogg/", ".opus", ".opus/", ".mp3", ".mp3/"];
		if(this._isValidString(url) == false) return false;
		const unsafeSchemes = ["javascript:", "data:", "vbscript:"];
		const normalized = url.trim().toLowerCase();
		if (unsafeSchemes.some(s => normalized.startsWith(s))) return false;

		const lowerUrl = url.toLowerCase();
		const match = suffixes.some(suffix => lowerUrl.endsWith(suffix));
		//if (match) return true;
		//else return false;
		return true;
	}
	_isPlayableByDecorator(url) {

		//TODO: Implement
		return false;
	}
	_isValidString(str) {

		return typeof str === 'string' && str.trim().length > 0;
	}


	_drawWaveform() {			// Function to draw the waveform
	
		requestAnimationFrame(this._drawWaveform);

		this._analyser.getByteFrequencyData(this._dataArray);

		// Clear canvas
		this._ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);

		const barWidth = (this._canvas.width / this._bufferLength) * 2.5;
		let x = 0;

		// Gradient for the waveform bars
		const gradient = this._ctx.createLinearGradient(0, 0, this._canvas.width, 0);
		gradient.addColorStop(0, '#ff0066');
		gradient.addColorStop(0.5, '#00b3b3');
		gradient.addColorStop(1, '#9933ff');
		this._ctx.fillStyle = gradient;

		// Draw waveform with smooth curves (using lines)
		this._ctx.beginPath();
		for (let i = 0; i < this._bufferLength; i++) {
			const value = this._dataArray[i];
			const y = (value / 255) * this._canvas.height;

			// Waveform style (smoother line)
			if (i === 0) {
				this._ctx.moveTo(x, this._canvas.height / 2 - y);  // Start line at middle of the canvas
			} else {
				this._ctx.lineTo(x, this._canvas.height / 2 - y);
			}
			x += barWidth + 1;
		}
		this._ctx.lineTo(x, this._canvas.height / 2); // Close path at the end
		this._ctx.strokeStyle = gradient;
		this._ctx.stroke();
	}
	_drawBars() {				// Function to draw bars expanding up and down

		setTimeout(() => 
		{
			requestAnimationFrame(this._drawBars);
		}, 20);
		if (this._audio.paused) 
		{
			return; // Stop drawing if the audio is paused (don't clear the canvas)
		}


		this._analyser.getByteFrequencyData(this._dataArray);

		// Clear canvas
		this._ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);

		const barWidth = Math.round((this._canvas.width / this._bufferLength) * 2.5);
		let x = 0;

		// Draw bars expanding both ways
		for (let i = 0; i < this._bufferLength; i++) {
			var barHeight = this._dataArray[i] - 120;
			
			if(i < 5) barHeight -= 80;
			else if(i < 10) barHeight -= 40;
			else if(i < 15) barHeight -= 20;
			
			const midY = Math.round(this._canvas.height / 2);
			this._ctx.fillStyle = '#000';

			// Bars expand both up and down from the center
			this._ctx.fillRect(x, midY, barWidth, barHeight); // Top side
			this._ctx.fillRect(x, midY, barWidth, -barHeight); // Bottom side

			x += barWidth + 1;
		}
	}
	_drawBars_Gradient() {		// Function to draw bars expanding up and down

		setTimeout(() => {

			requestAnimationFrame(this._drawBars_Gradient);
		}, 20);
		if (this._audio.paused) 
		{
			return; // Stop drawing if the audio is paused (don't clear the canvas)
		}


		this._analyser.getByteFrequencyData(this._dataArray);

		// Clear canvas
		this._ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);

		const barWidth = Math.round((this._canvas.width / this._bufferLength) * 2.5);
		let x = 0;

		// Gradient for the bars
		const gradient = this._ctx.createLinearGradient(0, 0, this._canvas.width, 0);
		gradient.addColorStop(0, '#ff0066');
		gradient.addColorStop(0.5, '#00b3b3');
		gradient.addColorStop(1, '#9933ff');

		// Draw bars expanding both ways
		for (let i = 0; i < this._bufferLength; i++) {
			var barHeight = this._dataArray[i] - 120;
			
			if(i < 5) barHeight -= 80;
			else if(i < 10) barHeight -= 40;
			else if(i < 15) barHeight -= 20;
			
			const midY = Math.round(this._canvas.height / 2);
			this._ctx.fillStyle = gradient;

			// Bars expand both up and down from the center
			this._ctx.fillRect(x, midY, barWidth, barHeight); // Top side
			this._ctx.fillRect(x, midY, barWidth, -barHeight); // Bottom side

			x += barWidth + 1;
		}
	}
}
//TODO: Implement by decorator mode fully
