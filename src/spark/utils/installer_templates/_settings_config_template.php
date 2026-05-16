<?php

define('ALLOW_SESSION_OVER_HTTP', *ALLOW_SESSION_OVER_HTTP_VALUE*);
define('IS_PRODUCTION', *IS_PRODUCTION_VALUE*);


/* RATE LIMITER - Logins */
const MAX_LOGIN_ATTEMPTS_PER_IP = *MAX_LOGIN_ATTEMPTS_PER_IP_VALUE*;				//10 failed attempts
const MAX_LOGIN_ATTEMPTS_PER_EMAIL = *MAX_LOGIN_ATTEMPTS_PER_EMAIL_VALUE*;			//5 failed attempts
const WAIT_TIME_LOGIN = *WAIT_TIME_LOGIN_VALUE*;									//wait for 15 minutes 
const KEEP_OLD_RECORDS = *KEEP_OLD_RECORDS_VALUE*;									//prune old records after 7 days

/* RATE LIMITER - Password reset */
const MAX_RESET_ATTEMPTS_PER_IP = *MAX_RESET_ATTEMPTS_PER_IP_VALUE*;				//10 failed attempts
const MAX_RESET_ATTEMPTS_PER_EMAIL = *MAX_RESET_ATTEMPTS_PER_EMAIL_VALUE*;			//5 failed attempts
const WAIT_TIME_RESET = *WAIT_TIME_RESET_VALUE*;									//wait for 16 minutes 
const KEEP_OLD_RECORDS_RESETS = *KEEP_OLD_RECORDS_RESETS_VALUE*;					//prune old records after 7 days

/* RATE LIMITER - Register */
const MAX_REGISTER_SUCCESSES_PER_IP = *MAX_REGISTER_SUCCESSES_PER_IP_VALUE*;		//2 successes - 2 accounts can be registered in 1 week with the same ip address
const WAIT_TIME_REGISTER = *WAIT_TIME_REGISTER_VALUE*;								//wait for 10080 minutes or 1 week
const KEEP_OLD_RECORDS_REGISTER = *KEEP_OLD_RECORDS_REGISTER_VALUE*;				//prune old records after 14 days