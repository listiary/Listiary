-- 1. Main data
-- 333 is the max number of characters for our collation indexing - `utf8mb3_unicode_ci` - which
-- uses up to 3 bytes per character. MariaDB needs this to be under 1000 bytes to index the column properly.
CREATE TABLE compiled_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(333) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE describe_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(333) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- indexes to speed up lookups by filename
CREATE INDEX idx_filename USING BTREE ON describe_documents (filename(333));
CREATE INDEX idx_filename USING BTREE ON compiled_documents (filename(333));







-- 2. Edit history
-- History
CREATE TABLE document_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	text LONGTEXT NULL DEFAULT NULL,
	json JSON NULL DEFAULT NULL,
	delta LONGBLOB NULL DEFAULT NULL,
	edit_comment VARCHAR(1000),
	is_minor TINYINT(1) NOT NULL DEFAULT 0,
	document_id BIGINT UNSIGNED NOT NULL,
	usercode VARCHAR(8) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);







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








-- 4. Housekeeping
-- This will link Describe item ids to filenames they are defined in.
-- N said this compound primary key creates an index, but we need an
-- additional index - 'idx_item_id' - to efficiently backward search.
CREATE TABLE housekeeping_itemid_filename (
    filename VARCHAR(333) NOT NULL,
    item_id VARCHAR(333) NOT NULL,
    PRIMARY KEY (filename(150), item_id(150))
);

-- index to speed up lookups by item_id
CREATE INDEX idx_item_id ON housekeeping_itemid_filename (item_id);

-- This will link files to files needed when loading them.
-- N said this compound primary key creates an index, but we need an
-- additional index - 'idx_item_id' - to efficiently backward search.
CREATE TABLE housekeeping_filename_related (
    filename VARCHAR(333) NOT NULL,
    related_filename VARCHAR(333) NOT NULL,
    PRIMARY KEY (filename(150), related_filename(150))
);

-- index to speed up lookups by related_filename
CREATE INDEX idx_related_filename ON housekeeping_filename_related (related_filename);








-- 5. Permissions
-- Maps users to one or more roles
CREATE TABLE permissions_account_roles (

    account_id BIGINT UNSIGNED NOT NULL,
	account_role ENUM('USER_VIEWER', 'USER_EDITOR', 'USER_SPONSOR', 'USER_MODERATOR', 'USER_ADMIN', 'USER_GOD') NOT NULL,

    -- A user can have multiple roles, but can't have the SAME role twice
    PRIMARY KEY (account_id, account_role),
    -- If the account is deleted, delete their roles
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE permissions_resource_accounts (

    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    
	-- The Resource (What is being accessed?)
    resource_type ENUM('ARTICLE') NOT NULL,
    resource_id BIGINT UNSIGNED NOT NULL, 
    
	-- the account that gets access
    account_id BIGINT UNSIGNED NOT NULL,
	
    -- Which specific action is allowed?
    permission_level ENUM('READ', 'WRITE', 'MANAGE') NOT NULL DEFAULT 'READ',

    -- Prevent duplicate permission entries for the same user/resource
    UNIQUE KEY uq_resource_account (resource_type, resource_id, account_id, permission_level),
    
    -- Indexes for fast lookup
    INDEX idx_account_lookup (account_id),
    INDEX idx_resource_lookup (resource_type, resource_id),
    
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE permissions_resource_roles (

    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    
	-- The Resource (What is being accessed?)
    resource_type ENUM('ARTICLE') NOT NULL,
    resource_id BIGINT UNSIGNED NOT NULL, 
    
	-- the account role that gets access
    account_role ENUM('USER_VIEWER', 'USER_EDITOR', 'USER_SPONSOR', 'USER_MODERATOR', 'USER_ADMIN', 'USER_GOD') NOT NULL,
	
	-- Which specific action is allowed?
    permission_level ENUM('READ', 'WRITE', 'MANAGE') NOT NULL DEFAULT 'READ',

    -- Prevent duplicate role permissions for the same resource
    UNIQUE KEY uq_resource_role (resource_type, resource_id, account_role, permission_level),
    
    -- Index for fast lookup
    INDEX idx_role_resource (account_role, resource_type, resource_id)
);