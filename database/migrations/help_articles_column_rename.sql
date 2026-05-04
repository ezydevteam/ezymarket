-- Rename columns in help_articles table
ALTER TABLE `help_articles`
CHANGE COLUMN `body` `content` TEXT NOT NULL,
CHANGE COLUMN `short_description` `description` VARCHAR(200) NOT NULL,
CHANGE COLUMN `views` `total_views` BIGINT UNSIGNED NOT NULL DEFAULT 0;

-- Rename columns in help_categories table
ALTER TABLE `help_categories`
CHANGE COLUMN `views` `total_views` BIGINT UNSIGNED NOT NULL DEFAULT 0;
