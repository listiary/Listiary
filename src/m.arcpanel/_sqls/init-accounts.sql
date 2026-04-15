-- 3. Accounts
-- Accounts
CREATE TABLE accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE,
    usercode VARCHAR(16) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_bot BOOLEAN DEFAULT FALSE,
	is_active BOOLEAN DEFAULT FALSE,
	is_premium BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	verification_token CHAR(64) UNIQUE
);

-- indexes to speed up lookups by email
-- when you define a column as UNIQUE, the database engine automatically creates a Unique Index for you
-- CREATE UNIQUE INDEX idx_email ON accounts (email);



CREATE TABLE account_details (

    account_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    
    -- Account fields
    avatar_path VARCHAR(255) DEFAULT NULL,          -- Path or URL to avatar image
	avatar_shape VARCHAR(50) DEFAULT "square",		-- square, circle, hexagon, triangle, inverted_triangle
	avatar_shape_radius INT DEFAULT 50,
	avatar_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

	-- Profile fields
	bio TEXT DEFAULT NULL,
	city VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
	timezone VARCHAR(50) DEFAULT 'UTC',
	
	-- Social links fields
	link_personal_website TEXT DEFAULT NULL,
	link_personal_facebook TEXT DEFAULT NULL,
	link_personal_xcom TEXT DEFAULT NULL,
	link_personal_linkedin TEXT DEFAULT NULL,
	link_personal_other TEXT DEFAULT NULL,
	
	-- Optional contact phone
    phone1 VARCHAR(20) DEFAULT NULL,
	phone1_verified TINYINT(1) DEFAULT 0,
	phone2 VARCHAR(20) DEFAULT NULL,
	phone2_verified TINYINT(1) DEFAULT 0,

    -- Foreign key to accounts table
	FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (email)
);

CREATE TABLE persistent_logins (

	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_id BIGINT UNSIGNED NOT NULL,
	selector CHAR(16) NOT NULL,
	token_hash CHAR(64) NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	expires_at DATETIME NOT NULL,
	
	INDEX idx_user_id (user_id),
	UNIQUE INDEX idx_selector (selector),
	FOREIGN KEY (user_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE login_attempts (

    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 1. For looking up attempts by email within a time window
CREATE INDEX idx_email_time ON login_attempts(email, attempt_time);
-- 2. For looking up attempts by IP within a time window
CREATE INDEX idx_ip_time ON login_attempts(ip_address, attempt_time);
-- 3. For cleaning up old records quickly
CREATE INDEX idx_attempt_time ON login_attempts(attempt_time);


CREATE TABLE register_success (

    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 1. For looking up attempts by email within a time window
CREATE INDEX idx_email_time ON register_success(email, attempt_time);
-- 2. For looking up attempts by IP within a time window
CREATE INDEX idx_ip_time ON register_success(ip_address, attempt_time);
-- 3. For cleaning up old records quickly
CREATE INDEX idx_attempt_time ON register_success(attempt_time);


CREATE TABLE password_reset_resends (

	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    send_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 1. For looking up attempts by email within a time window
CREATE INDEX idx_email_time ON password_reset_resends(email, send_time);
-- 2. For looking up attempts by IP within a time window
CREATE INDEX idx_ip_time ON password_reset_resends(ip_address, send_time);
-- 3. For cleaning up old records quickly
CREATE INDEX idx_send_time ON password_reset_resends(send_time);
