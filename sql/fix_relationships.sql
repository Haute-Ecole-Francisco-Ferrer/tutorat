-- Fix relationships with empty status values
UPDATE tutoring_relationships 
SET status = 'pending' 
WHERE status = '' OR status IS NULL;

-- Fix relationships with empty ID values
-- First, identify relationships with empty IDs
SELECT * FROM tutoring_relationships 
WHERE id = '' OR id IS NULL;

-- Then, update them with a new UUID
-- Note: You may need to adjust this query based on the results of the previous query
UPDATE tutoring_relationships 
SET id = UUID() 
WHERE id = '' OR id IS NULL;

-- Fix archived relationships with empty status values
UPDATE tutoring_relationships_archive 
SET status = 'archived' 
WHERE status = '' OR status IS NULL;

-- Fix archived relationships with empty ID values
UPDATE tutoring_relationships_archive 
SET id = UUID() 
WHERE id = '' OR id IS NULL;

-- Alternative approach for fixing date issues
-- This uses a more direct method that should work in most MySQL configurations

-- 1. First, identify rows with problematic dates in tutoring_relationships
SELECT id, archived_at FROM tutoring_relationships 
WHERE archived_at = '0000-00-00 00:00:00' OR YEAR(archived_at) = 0;

-- 2. Update those rows with NULL (run this after checking the results above)
UPDATE tutoring_relationships 
SET archived_at = NULL 
WHERE CAST(archived_at AS CHAR) LIKE '0000-00-00%';

-- 3. Identify rows with problematic dates in tutoring_relationships_archive
SELECT id, archived_at FROM tutoring_relationships_archive 
WHERE archived_at = '0000-00-00 00:00:00' OR YEAR(archived_at) = 0;

-- 4. Update those rows with current timestamp (run this after checking the results above)
UPDATE tutoring_relationships_archive 
SET archived_at = NOW() 
WHERE CAST(archived_at AS CHAR) LIKE '0000-00-00%';

-- If the above still doesn't work, you may need to modify the database structure
-- to allow NULL values for the archived_at column:

-- ALTER TABLE tutoring_relationships 
-- MODIFY COLUMN archived_at DATETIME NULL DEFAULT NULL;

-- ALTER TABLE tutoring_relationships_archive 
-- MODIFY COLUMN archived_at DATETIME NULL DEFAULT NULL;

-- Then try the updates again.

-- Note about SQL mode:
-- If you ran SET SESSION sql_mode = ''; to disable strict mode,
-- this only affects the current session and will revert back to the default
-- when the session ends or when you close phpMyAdmin.
-- 
-- If you want to restore the original SQL mode for the current session,
-- you can run:
-- SET SESSION sql_mode = (SELECT @@GLOBAL.sql_mode);
--
-- If you want to permanently change the SQL mode for the server,
-- you would need to modify the MySQL configuration file (my.cnf or my.ini),
-- but this is generally not recommended unless you understand the implications.
