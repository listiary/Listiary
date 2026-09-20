INSERT INTO permissions_resource_roles (resource_type, resource_id, account_role, permission_level)
SELECT 'ARTICLE', id, 'USER_VIEWER', 'READ'
FROM describe_documents;

INSERT INTO permissions_resource_roles (resource_type, resource_id, account_role, permission_level)
SELECT 'ARTICLE', id, 'USER_EDITOR', 'WRITE'
FROM describe_documents;

INSERT INTO permissions_resource_roles (resource_type, resource_id, account_role, permission_level)
SELECT 'ARTICLE', id, 'USER_MODERATOR', 'MANAGE'
FROM describe_documents;