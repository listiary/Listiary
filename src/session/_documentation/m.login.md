




2. Functions - Service

	2.1 startSecureSession
	`startSecureSession(): void`
	Start a session with a hardened cookie. Must call before any output.
	
	Check if we already have a session started in the script. If so, we don't need to do anything but exit. Otherwise, proceed.
	Detect if we run on HTTPS. If yes, proceed, but if not - look at the `ALLOW_SESSION_OVER_HTTP` flag from our config, and if it is false - throw an exception. 
	Set parameters for the session cookie that php will use in order to harden it.
	Try to start the session, and throw an exception if it fails.
	


	2.2 isSessionEmpty
	`isSessionEmpty(): bool`
	Do we have a logged in session or a useless empty one
	
	Check a particular value of the session superglobal - `$_SESSION['loggedin']`.
	Return true if it is a boolean true, otherwise assume it is not set and return false.



	2.3 connectDb
	`connectDb(): mysqli`
	Open a connection to the DB
	
	Have a static connection object inside the function.
	We will be returning that object, and also accessing it in possible subsequent calls. 
	This guards us from accidentally opening more than one connection by mistake.
	Get the connection credentials and url from the config and create a connection (`mysqli`) object.
	Check the object for an error, and if found, throw an exception.
	
	
	
	2.4 catchEx
	`catchEx(Throwable $ex): void`
	The Default Exception handler
	
	Log an error with the inbuilt in PHP `error_log`.
	Set the response code to `Error 500`
	Check the config constant `IS_PRODUCTION` to see if we are in a production or in a testing environment.
	If we are on a production environment, output a generic error message, like `An internal error occurred.`.
	If we are on a dev environment - output a detailed error message.




4. System map

```

m.login			->			m.user
m.login			->			m.register
m.login			->			m.forgotpass

```