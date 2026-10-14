const audio = document.getElementById('audio');
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');

// Audio context and analyser setup
const audioContext = new (window.AudioContext || window.webkitAudioContext)();
const analyser = audioContext.createAnalyser();
analyser.fftSize = 512; // Better resolution
const bufferLength = analyser.frequencyBinCount;
const dataArray = new Uint8Array(bufferLength);

// Connect the audio element to the analyser node
const source = audioContext.createMediaElementSource(audio);
source.connect(analyser);
analyser.connect(audioContext.destination);

// Function to draw the waveform
function drawWaveform() {
	requestAnimationFrame(drawWaveform);

	analyser.getByteFrequencyData(dataArray);

	// Clear canvas
	ctx.clearRect(0, 0, canvas.width, canvas.height);

	const barWidth = (canvas.width / bufferLength) * 2.5;
	let x = 0;

	// Gradient for the waveform bars
	const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
	gradient.addColorStop(0, '#eee');
	gradient.addColorStop(0.5, '#eee');
	gradient.addColorStop(1, '#eee');
	ctx.fillStyle = gradient;

	// Draw waveform with smooth curves (using lines)
	ctx.beginPath();
	for (let i = 0; i < bufferLength; i++) {
		const value = dataArray[i];
		const y = (value / 255) * canvas.height;

		// Waveform style (smoother line)
		if (i === 0) {
			ctx.moveTo(x, canvas.height / 2 - y);  // Start line at middle of the canvas
		} else {
			ctx.lineTo(x, canvas.height / 2 - y);
		}
		x += barWidth + 1;
	}
	ctx.lineTo(x, canvas.height / 2); // Close path at the end
	ctx.strokeStyle = gradient;
	ctx.stroke();
}

// Function to draw bars expanding up and down
function drawBars() {
	requestAnimationFrame(drawBars);

	analyser.getByteFrequencyData(dataArray);

	// Clear canvas
	ctx.clearRect(0, 0, canvas.width, canvas.height);

	const barWidth = (canvas.width / bufferLength) * 2.5;
	let x = 0;

	// Gradient for the bars
	const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
	gradient.addColorStop(0, '#000');
	gradient.addColorStop(0.5, '#000');
	gradient.addColorStop(1, '#000');

	// Draw bars expanding both ways
	for (let i = 0; i < bufferLength; i++) {
		var barHeight = dataArray[i] - 120;
		
		if(i < 5) barHeight -= 80;
		else if(i < 10) barHeight -= 40;
		else if(i < 15) barHeight -= 20;
		
		const midY = canvas.height / 2;
		const expandedHeight = barHeight / 2;

		ctx.fillStyle = gradient;

		// Bars expand both up and down from the center
		ctx.fillRect(x, midY, barWidth, barHeight); // Top side
		ctx.fillRect(x, midY, barWidth, -barHeight); // Bottom side

		x += barWidth + 1;
	}
}

// Choose whether to draw the waveform or the expanding bars
let drawingFunction = drawBars; // Start with bars expanding both ways
// let drawingFunction = drawWaveform; // Uncomment this to switch to waveform

// Start drawing when audio starts
audio.onplay = function() {
	audioContext.resume().then(() => {
		drawingFunction(); // Start visualizing
	});
};