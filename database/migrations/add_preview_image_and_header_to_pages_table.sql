-- Add preview_image and header columns to pages table
-- Migration: Add preview_image and header fields to pages table
-- Date: 2025-11-17

-- Add preview_image column
ALTER TABLE `pages`
ADD COLUMN `preview_image` VARCHAR(255) NULL AFTER `description`,
ADD INDEX `idx_pages_preview_image` (`preview_image`);

-- Add header column (stores JSON)
ALTER TABLE `pages`
ADD COLUMN `header` JSON NULL AFTER `preview_image`;

-- Update existing records to have default header configuration
UPDATE `pages`
SET `header` = JSON_OBJECT(
    'style', 'style-1',
    'breadcrumb', true,
    'description', false
)
WHERE `header` IS NULL;

-- Optional: Add comments to the columns
ALTER TABLE `pages`
MODIFY COLUMN `preview_image` VARCHAR(255) NULL COMMENT 'Preview/featured image path for the page',
MODIFY COLUMN `header` JSON NULL COMMENT 'Header configuration: style, breadcrumb, description';
