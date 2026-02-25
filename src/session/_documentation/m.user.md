This is the user profile page the logged in user sees when he clicks on his profile name.



1. Logic

	1.1 Presets
	Set mysqli reporting to strict. This forces mysqli to throw exceptions instead of failing silently.
	Include the config file. It contains options and our database connection credentials.
	Set exception handler for the script to our exception handler function - `catchEx`.
	
	1.2 More Presets
	Call `startSecureSession` to initialize a session for the script. 
	If we are logged in, this will be populated with some particular values, if not - it will be empty.
	Call `connectDb` to initiate a database connection for the script.

	1.3 Check if session empty and try persistent login if so
	Call `isSessionEmpty` to see if we are logged in. If we are, proceed to 1.4
	Given we are not logged in, we proceed to call `doRememberedLogin` - which will look at weather we have a 
	"long login cookie" AKA "remember me cookie", and will attempt to log in via that cookie if we have one.
	It this also fails, we have no place to be on this empty profile page, so we are redirected to `m.login.php`.
	Otherwise we are logged in, and we can proceed to the next step.

	1.4 Get data from session into variables that will be showed on our page
	We have a bunch of variables that populate the HTML on the page below our php script.
	Just assign session values to them - which in effect serves as an interface between the PHP layer and the HTML layer. 
	We are basically outputting those values on the page, to be displayed on our user profile page, by assigning them.





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





3. Functions - Logic

		3.1 doRememberedLogin
		`doRememberedLogin(mysqli $link): bool`
		Log in with a remember me cookie and the database.
	
	
	
	3.2 invalidateRememberToken
	`invalidateRememberToken(mysqli $link, string $selector): void`
	Delete token from DB

	Given a `$selector` - this is just the persistent token's name - a way to refer to,
	execute a DB query to delete the record for that token.
	Expire the `remember_token` cookie.
	
	
	
	3.3 restoreUserSession
	`restoreUserSession(mysqli $link, int $user_id): void`
	Fetch user info from accounts table and populates $_SESSION
	
	Call `session_regenerate_id` to prevent session fixation attack
	Set `loggedin` flag to true - `$_SESSION['loggedin'] = true;`.
	Set `id` flag to the user id in the database.
	Try to fetch user info from the database.
	If an error acurs - throw an exception.
	If 0 rows are returned - we assume such user don't exist, so throw an exception as well.
	Lastly, populate the session with the data we got from the DB.
	
	
	
	3.4 executePersistentLogin
	`executePersistentLogin(mysqli $link, int $user_id): void`
	Stores persistent login token to the database
	
	Generate a new random token and selector. There is nothing to it - they are just random strings of
	8 and 32 bit length created using a cryptographically sound function `random_bytes`.
	Insert this data into the database `persistent_logins` table, setting the `expires_at` field at one year from now.
	Build a cookie - that is "selector:token" format, and try to set it, using settings that harden the cookie.
	If we fail to set the cookie - throw an exception.
	
	
	
		3.5 executePersistentTokenRotation
		`executePersistentTokenRotation(mysqli $link, int $user_id, string $selector, string $token): void`
		Rotates stored persistent login token
	
	




4. System map

```

m.login			->			m.user
m.login			->			m.register
m.login			->			m.forgotpass

```