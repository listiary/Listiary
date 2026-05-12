//step 2
let step2ModalName = "setDbTablesModal";
let step2Console = document.getElementById("step2Console");
let step2Button = document.getElementById("step2Button");
let failed = false;

function step2_Init() {
	
	if(step2Console == null) step2Console = document.getElementById("step2Console");
	if(step2Button == null) step2Button = document.getElementById("step2Button");
}
function step2_Write(text) {
	
	step2Console.textContent += text;
}
function step2_ShowModal() {
	
	step2_Init();
	showListiaryModal(step2ModalName);
	if(!failed)
	{
		step2Console.textContent = "";
		step2Button.onclick = execute_step21;
		step2_outputWelcomeMessage();
	}
}
function step2_CloseModal() {
	
	hideListiaryModal(step2ModalName);
	if(!failed)
	{
		step2Console.textContent = "";
		step2Button.onclick = execute_step21;
	}
}

function step2_outputWelcomeMessage() {

	step2_Write("This is Listiary Console Modal\nClick 'Run' to continue ...");
}
async function step2_executePhp_TestConfig() {
	
	step2_Write('\n\nRunning main config test');
	
	try
	{
		const response = await fetch('_installer/test_config.php');
		if (!response.ok) 
		{
			step2_Write('\nHttp error: ' + response.status);
			step2_Write('\nINSTALLATION FAILED');
			return false;
		}
		
		var data = await response.text();
		data = data.replace(/<br\s*\/?>/gi, '\n');
		step2_Write('\n' + data);
		
		const trimmed = data.trim();
		const words = trimmed.split(/\s+/);
		const lastWord = words[words.length - 1];
		if (lastWord === 'SUCCEEDED') return true;
		
		step2_Write('\nINSTALLATION FAILED');
		return false;
	}
	catch (err)
	{
		step2_Write('\n\nError: ' + err);
		step2_Write('\nINSTALLATION FAILED');
		return false;
    }
}
async function step2_executePhp_CreateTables() {
	
	step2_Write('\n\nRunning SQL table creation');
	
	try
	{
		const response = await fetch('_installer/make_tables.php');
		if (!response.ok) 
		{
			step2_Write('\nHttp error: ' + response.status);
			step2_Write('\nINSTALLATION FAILED');
			return false;
		}
		
		var data = await response.text();
		data = data.replace(/<br\s*\/?>/gi, '\n');
		step2_Write('\n' + data);
		
		const trimmed = data.trim();
		const words = trimmed.split(/\s+/);
		const lastWord = words[words.length - 1];
		if (lastWord === 'SUCCEEDED') return true;
		
		step2_Write('\nINSTALLATION FAILED');
		return false;
	}
	catch (err)
	{
		step2_Write('\n\nError: ' + err);
		step2_Write('\nINSTALLATION FAILED');
		return false;
    }
}

async function execute_step21() {
	
	step2Button.disabled = true;
    var result = await step2_executePhp_TestConfig();
	if(!result)
	{
		step2Button.innerHTML = 'Exit';
		step2Button.onclick = step2_CloseModal;
		failed = true;
	}
	else
	{
		step2_Write("\nClick 'Run' to continue ...");
		step2Button.onclick = execute_step22;
	}
    step2Button.disabled = false;
}
async function execute_step22() {
	
	step2Button.disabled = true;
    var result = await step2_executePhp_CreateTables();
	if(!result)
	{
		step2Button.innerHTML = 'Exit';
		step2Button.onclick = step2_CloseModal;
		failed = true;
	}
	else
	{
		step2_Write("\n\nClick 'Ok' to close this window and continue with the installation");
		step2Button.innerHTML = 'Ok';
		step2Button.onclick = step2_CloseModal;
	}
    step2Button.disabled = false;
}



//step 3
let step3ModalName = "settingsConfigModal";

function step3_ShowModal() {
	
	showListiaryModal(step3ModalName);
}
function step3_CloseModal() {
	
	hideListiaryModal(step3ModalName);
}



//step 0 file browser
function triggerFileBrowser() {

    // Triggers the system file dialog when the dots button is clicked
    document.getElementById('hiddenFileInput').click();
}
function updateTextInput(targetInputId) {

    const fileInput = document.getElementById('hiddenFileInput');
    const textInput = document.getElementById(targetInputId);
    
    // Check if a file was selected
    if (fileInput && fileInput.files.length > 0 && textInput) 
	{
        // Extract the filename (e.g., "my-logo.png") and put it in the text box
        textInput.value = fileInput.files[0].name;
    }
}
