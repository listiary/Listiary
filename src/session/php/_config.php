<?php

define('ALLOW_SESSION_OVER_HTTP', false);
define('IS_PRODUCTION', false);

/* URLs */
define('BASE_URL', 'https://development.listiary.org/');
define('INDEX_URL', 'https://development.listiary.org/m.index.php');



	/* RATE LIMITER - Logins */
	const MAX_LOGIN_ATTEMPTS_PER_IP = 10;			//10 failed attempts
	const MAX_LOGIN_ATTEMPTS_PER_EMAIL = 5;			//5 failed attempts
	const WAIT_TIME_LOGIN = 15;						//wait for 15 minutes 
	const KEEP_OLD_RECORDS = 7;						//prune old records after 7 days

	/* RATE LIMITER - Password reset */
	const MAX_RESET_ATTEMPTS_PER_IP = 10;			//10 failed attempts
	const MAX_RESET_ATTEMPTS_PER_EMAIL = 5;			//5 failed attempts
	const WAIT_TIME_RESET = 16;						//wait for 16 minutes 
	const KEEP_OLD_RECORDS_RESETS = 7;				//prune old records after 7 days
	
	/* RATE LIMITER - Register */
	const MAX_REGISTER_SUCCESSES_PER_IP = 2;		//2 successes - 2 accounts can be registered in 1 week with the same ip address
	const WAIT_TIME_REGISTER = 10080;				//wait for 10080 minutes or 1 week
	const KEEP_OLD_RECORDS_REGISTER = 14;			//prune old records after 14 days

?>