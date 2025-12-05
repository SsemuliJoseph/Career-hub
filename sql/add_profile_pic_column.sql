-- Add profile_pic column to students table for profile images
-- Run this migration to fix missing profile picture placeholder issue

ALTER TABLE `students` 
ADD COLUMN `profile_pic` VARCHAR(255) DEFAULT NULL AFTER `cv_file`;

-- Update existing students to have default profile picture
UPDATE `students` 
SET `profile_pic` = '/career_hub/uploads/profile/default-avatar.png' 
WHERE `profile_pic` IS NULL OR `profile_pic` = '';

-- Add profile_pic column to users table if it exists and doesn't have it
ALTER TABLE `users` 
ADD COLUMN `profile_pic` VARCHAR(255) DEFAULT NULL;

-- Update existing users to have default profile picture  
UPDATE `users` 
SET `profile_pic` = '/career_hub/uploads/profile/default-avatar.png' 
WHERE `profile_pic` IS NULL OR `profile_pic` = '';
