-- Rename status column to is_active in social_authetication table
-- This migration changes the status field (1/0) to is_active boolean

ALTER TABLE `social_authetication`
CHANGE COLUMN `status` `is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Active status of the social authentication provider';
