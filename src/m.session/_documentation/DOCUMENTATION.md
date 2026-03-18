Accounts in Listiary are handled by the session module of the app, residing in the `session` folder.
I have been following this tutorial from TutorialRepublic to create the session system in php - [PHP MySQL Login System](https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php).


1. File listing
The `_config.php` contains the constants needed to connect to the SQL (MariaDB) database.
The `_logout.php` has logic for destroying a session.
The `register.php` and `m.register.php` - account registration forms.
The `login.php` and `m.login.php` - login forms.
The `resetpass.php` and `m.resetpass.php` - password reset forms.
The `user.php` and `m.user.php` - user profile pages.


2. Registration process
The `register.php` and `m.register.php` are the registration forms in Listiary. They check to see if data is correctly entered and to make sure a user with the same username does not exist, before adding an account and redirecting the user to the login page.




Work done:

Organized files in folders - back end scripts in `/php`, styles in `/css`.

Added a more robust require_once pattern - `require_once __DIR__ . "/php/_config.php";`
This guards against potential future issues in the include chain where PHP resolves relative paths from the caller’s working directory rather than the included file’s location. While not strictly necessary at the moment, this approach is more resilient and safer for future refactors.

Used Google Gemini to redesign the mobile login and register pages.


// not working token - do not steal off github to hack
https://development.listiary.org/session/m.verify.php?token=212ec4f758d84adc070c76d5d3083aae1eecf5ada39ad0f3895919ab7bab09a5&email=vchernev91%40abv.bg
https://development.listiary.org/session/m.user.php?id=7
https://development.listiary.org/session/m.regsuccess.php
https://development.listiary.org/session/m.verify.php


	// session_regenerate_id(true)
	// CSRF protection on forms
	// Rate limiting login attempts
	// HTTPS-only session cookies
	// Logout script that destroys session properly
	
	5. Options to logout all devices, change password
	6. Have a page in the profile page that shows all user's contributions
	7. Create documentation about the 'session' module