-- Rename preview_image column to thumbnail in themes table
-- This migration renames the preview_image field to thumbnail

ALTER TABLE `themes`
CHANGE COLUMN `preview_image` `thumbnail` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path to theme thumbnail/preview image';
