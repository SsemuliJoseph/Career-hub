-- Safe migration to support external job imports
-- Run this in phpMyAdmin (MySQL 8+) for the active database

ALTER TABLE `jobs`
    ADD COLUMN IF NOT EXISTS `company` VARCHAR(255) NULL AFTER `title`,
    ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `location` VARCHAR(255) NULL AFTER `created_at`,
    ADD COLUMN IF NOT EXISTS `url` VARCHAR(500) NULL AFTER `location`,
    ADD COLUMN IF NOT EXISTS `source` VARCHAR(50) NULL AFTER `url`;

-- Optional: defaults for convenience
UPDATE `jobs` SET `created_at` = IFNULL(`created_at`, NOW());

-- Helpful index for deduplication/search
-- Note: MySQL doesn't support IF NOT EXISTS for indexes; run once or adjust as needed
-- CREATE INDEX `idx_jobs_title_company` ON `jobs` (`title`, `company`);
