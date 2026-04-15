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