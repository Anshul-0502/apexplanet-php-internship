USE blogs2;

-- Step 1:
-- Register normally from the website.

-- Step 2:
-- Replace Anshul with your registered username.

UPDATE users
SET role = 'admin'
WHERE username = 'Anshul';

-- Verify the roles.

SELECT
    id,
    username,
    role,
    created_at
FROM users
ORDER BY id;
