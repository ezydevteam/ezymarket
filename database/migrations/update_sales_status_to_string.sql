-- Update referral_earnings status column from integer to string (lowercase)

-- Step 1: Add temporary column
ALTER TABLE `product_reports` ADD COLUMN `status_temp` VARCHAR(20) NULL AFTER `status`;

-- Step 2: Migrate data from integer to string
UPDATE `product_reports`
SET `status_temp` = CASE
    WHEN `status` = 'pending' THEN 'pending'
    WHEN `status` = 'reviewed' THEN 'reviewed'
    WHEN `status` = 'resolved' THEN 'resolved'
    WHEN `status` = 'dismissed' THEN 'cancelled'
    ELSE 'pending'
END;

-- Step 3: Drop old status column
ALTER TABLE `product_reports` DROP COLUMN `status`;
-- Step 4: Rename temp column to status
ALTER TABLE `product_reports` CHANGE COLUMN `status_temp` `status` VARCHAR(20) NOT NULL DEFAULT 'pending';

ALTER TABLE `product_reports`
  MODIFY COLUMN `status`
    ENUM('pending', 'reviewed', 'resolved', 'cancelled')
    NOT NULL
    DEFAULT 'pending';
