class StreamPlayerPlugin {
	
	version = "0.9";
	
	html = "<canvas id='canvas' style='margin-top: 20px; border: 1px solid #ccc; width: 100%; height: 100px; max-width: 12cm; border-radius: 10px; box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);'></canvas><br><audio crossorigin='anonymous' id='audio' controls='' style='width: 100%; margin-top: 10px; max-width: 12cm;'><source src='https://play.global.audio/nrj64' type='audio/mp3'>Not supported</audio><br><br>";
	
	_audio; _canvas; _ctx;
	_audioContext; _analyser; _bufferLength; _dataArray;

	Init() {
		
		//check if we should activate the plugin
		if(this._shouldActivate() == false) return;
		
		//add the UI elements
		const tempContainer = document.createElement("div");
		tempContainer.innerHTML = this.html;		
		var element = document.getElementById("listTopSpace");
		const parent = element.parentNode;
		if (element.nextSibling) 
		{
			parent.insertBefore(tempContainer, element);
		} 
		else 
		{
			parent.appendChild(tempContainer);
		}
		
		//add visualizer functionality
		this._audio = document.getElementById('audio');
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
		this._drawWaveform = this._drawWaveform.bind(this);
		
		// Choose whether to draw the waveform or the expanding bars
		let drawingFunction = this._drawBars; // Start with bars expanding both ways
		// let drawingFunction = this._drawWaveform; // Uncomment this to switch to waveform

		// Start drawing when audio starts
		this._audio.onplay = () => { // FIX: Arrow function to preserve `this`
			
			this._audioContext.resume().then(() => {
				drawingFunction(); // Start visualizing
			});
		};
	}
	_shouldActivate(){
		
		return true;
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

		requestAnimationFrame(this._drawBars);

		this._analyser.getByteFrequencyData(this._dataArray);

		// Clear canvas
		this._ctx.clearRect(0, 0, this._canvas.width, this._canvas.height);

		const barWidth = (this._canvas.width / this._bufferLength) * 2.5;
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
			
			const midY = this._canvas.height / 2;
			const expandedHeight = barHeight / 2;

			this._ctx.fillStyle = gradient;

			// Bars expand both up and down from the center
			this._ctx.fillRect(x, midY, barWidth, barHeight); // Top side
			this._ctx.fillRect(x, midY, barWidth, -barHeight); // Bottom side

			x += barWidth + 1;
		}
	}
}

var StreamPlayer = new StreamPlayerPlugin();