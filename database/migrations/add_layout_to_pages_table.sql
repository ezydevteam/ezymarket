-- Add layout column to pages table
-- Migration: Add layout column to pages table
-- Date: 2025-11-17

ALTER TABLE `pages`
ADD COLUMN `layout` VARCHAR(20) DEFAULT 'full' AFTER `description`,
ADD INDEX `idx_pages_layout` (`layout`);

-- Update existing records to have default layout
UPDATE `pages`
SET `layout` = 'full'
WHERE `layout` IS NULL;

-- Optional: Add comment to the column
ALTER TABLE `pages`
MODIFY COLUMN `layout` VARCHAR(20) DEFAULT 'full' COMMENT 'Page layout type: full, boxed, sidebar';
