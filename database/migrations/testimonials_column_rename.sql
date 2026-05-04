-- Rename columns in testimonials table
-- This migration renames avatar to image and body to content

ALTER TABLE `testimonials`
CHANGE COLUMN `avatar` `image` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path to testimonial image',
CHANGE COLUMN `body` `content` TEXT NOT NULL COMMENT 'Testimonial content/text';
