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