CREATE TABLE accounts (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    is_bot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE staged_documents (
    id SERIAL PRIMARY KEY,
    account_id INT REFERENCES accounts(id) ON DELETE CASCADE,
	foldername VARCHAR(255),
    filename VARCHAR(255), -- optional: filename or source label
    content TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT NOW(),
    encoding VARCHAR(50) DEFAULT 'UTF-8'
);

CREATE TABLE compiled_documents (
    id SERIAL PRIMARY KEY,
    filename VARCHAR(1024) NOT NULL,
    content JSONB NOT NULL,
    submitted_at TIMESTAMP DEFAULT NOW()
);
CREATE INDEX idx_filename ON compiled_documents(filename);


CREATE INDEX idx_staged_account ON staged_documents(account_id);
CREATE INDEX idx_staged_time ON staged_documents(submitted_at);
