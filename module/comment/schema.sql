DROP TABLE IF EXISTS `comment`;

CREATE TABLE `comment` (`comment_id` int(10) UNSIGNED NOT NULL auto_increment, `comment_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `comment_created` datetime NOT NULL, `comment_updated` datetime NOT NULL, `comment_image` varchar(255) DEFAULT NULL, `comment_title` varchar(254) NOT NULL, `comment_slug` varchar(255) NOT NULL, `comment_detail` text NOT NULL, `comment_tags` json DEFAULT NULL, `comment_status` varchar(255) DEFAULT 'PENDING', `comment_published` datetime DEFAULT NULL, PRIMARY KEY (`comment_id`), UNIQUE KEY `comment_slug` (`comment_slug`), KEY `comment_active` (`comment_active`), 
KEY `comment_created` (`comment_created`), 
KEY `comment_updated` (`comment_updated`), 
KEY `comment_title` (`comment_title`));

DROP TABLE IF EXISTS `comment_profile`;

CREATE TABLE `comment_profile` (`comment_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`comment_id`, `profile_id`));