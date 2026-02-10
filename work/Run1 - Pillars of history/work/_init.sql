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
    usercode VARCHAR(8) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    is_bot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);





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