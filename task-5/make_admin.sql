USE blogs3;

-- Register first, then replace Anshul with your actual username.

UPDATE users
SET role = 'admin'
WHERE username = 'Anshul';

SELECT id, username, role, created_at
FROM users
ORDER BY id;
