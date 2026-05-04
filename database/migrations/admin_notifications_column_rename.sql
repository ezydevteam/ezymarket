-- Rename status column to is_unread in admin_notifications table
-- Old logic: 0 = Unread, 1 = Read
-- New logic: 1 (true) = Unread, 0 (false) = Read

-- Step 1: Rename the column
ALTER TABLE `admin_notifications`
CHANGE COLUMN `status` `is_unread` TINYINT(1) NOT NULL DEFAULT 0
COMMENT 'Boolean: 1 = Unread, 0 = Read';

-- Step 2: Invert the boolean logic (swap 0 and 1 values)
UPDATE `admin_notifications`
SET `is_unread` = CASE
    WHEN `is_unread` = 0 THEN 1
    ELSE 0
END;

-- Verify the changes
-- SELECT id, title, is_unread FROM admin_notifications LIMIT 10;
