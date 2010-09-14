-------------------------------------------------------------------------------
-- Export Status
-------------------------------------------------------------------------------
-- Date:           14.09.2010 15:52:03
-- Server version: 5.1.28-rc-community
-- Host:           localhost
-- Database:       files_container
-- User:           root
-------------------------------------------------------------------------------
-- Options
-------------------------------------------------------------------------------
-- compatible:               None
-- charset:                  latin1
-- add-database-definition:  Yes
-- use-drop-create-database: Yes
-- only-structure:           No
-- add-lock:                 Yes
-- disable-keys:             No
-- single-transactions:      No
-- use-replace:              No
-- use-insert-delayed:       No
-- use-insert-ignore:        No
-------------------------------------------------------------------------------
-- Objects
-------------------------------------------------------------------------------
-- Tables:   11/11
-- Views:    0/0
-- Routines: 0/0
-- Events:   0/0
-------------------------------------------------------------------------------

SET NAMES 'latin1';

--
-- Definition for database "files_container"
--

DROP SCHEMA IF EXISTS `files_container`;

CREATE SCHEMA `files_container` CHARACTER SET `utf8` COLLATE `utf8_general_ci`;

USE `files_container`;

--
-- Definition for table "authorized_urls"
--

CREATE TABLE `authorized_urls`(
  `authorized_url_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`authorized_url_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 6
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "client_files"
--

CREATE TABLE `client_files`(
  `client_file_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_uploaded_date` DATETIME NOT NULL,
  `file_hash` VARCHAR(200) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`client_file_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "client_grant_comments"
--

CREATE TABLE `client_grant_comments`(
  `comment_grant_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_file_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`comment_grant_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "client_history"
--

CREATE TABLE `client_history`(
  `client_history_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) NULL DEFAULT NULL,
  `browser_name` VARCHAR(255) NULL DEFAULT NULL,
  `client_ip_address` VARCHAR(100) NULL DEFAULT NULL,
  `history_action` VARCHAR(255) NOT NULL,
  `session_id` INT(11) UNSIGNED NOT NULL,
  `client_history_date` DATETIME NOT NULL,
  PRIMARY KEY (`client_history_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "comments"
--

CREATE TABLE `comments`(
  `comment_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_content` TEXT NOT NULL,
  `comment_date` DATETIME NOT NULL,
  `client_file_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `user_name` VARCHAR(100) NULL DEFAULT NULL,
  `parent_comment_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`comment_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "modules"
--

CREATE TABLE `modules`(
  `module_id` INT(11) NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(100) NOT NULL,
  `module_description` TEXT NULL DEFAULT NULL,
  `module_path` VARCHAR(255) NOT NULL,
  `module_available` INT(1) UNSIGNED ZEROFILL NOT NULL,
  `module_depends_on` VARCHAR(200) NULL DEFAULT NULL,
  `module_order` INT(2) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`module_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 7
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "registration_keys"
--

CREATE TABLE `registration_keys`(
  `registration_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_email` VARCHAR(255) NOT NULL,
  `registration_date` DATETIME NOT NULL,
  `registration_key` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`registration_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "sessions"
--

CREATE TABLE `sessions`(
  `session_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `session_value` VARCHAR(100) NOT NULL,
  `session_start_date` DATETIME NOT NULL,
  PRIMARY KEY (`session_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "url"
--

CREATE TABLE `url`(
  `url_id` INT(11) NOT NULL AUTO_INCREMENT,
  `module_id` INT(11) UNSIGNED NOT NULL,
  `url_path` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`url_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 10
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "user_address"
--

CREATE TABLE `user_address`(
  `user_address_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(20) NOT NULL,
  `user_email_address` VARCHAR(200) NOT NULL,
  `user_first_name` VARCHAR(255) NOT NULL,
  `user_last_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`user_address_id`),
  UNIQUE KEY `user_id` (`user_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Definition for table "users"
--

CREATE TABLE `users`(
  `user_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
  `user_password` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`user_id`)
)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COLLATE = utf8_general_ci
ROW_FORMAT = COMPACT;

--
-- Data for table "authorized_urls"
--

LOCK TABLE `authorized_urls` WRITE;

START TRANSACTION;

INSERT INTO `authorized_urls` VALUES
    (1, 1),
    (2, 3),
    (3, 6),
    (4, 7),
    (5, 8);

COMMIT;

UNLOCK TABLES;

--
-- Data for table "client_files"
--

LOCK TABLE `client_files` WRITE;

UNLOCK TABLES;

--
-- Data for table "client_grant_comments"
--

LOCK TABLE `client_grant_comments` WRITE;

UNLOCK TABLES;

--
-- Data for table "client_history"
--

LOCK TABLE `client_history` WRITE;

UNLOCK TABLES;

--
-- Data for table "comments"
--

LOCK TABLE `comments` WRITE;

UNLOCK TABLES;

--
-- Data for table "modules"
--

LOCK TABLE `modules` WRITE;

START TRANSACTION;

INSERT INTO `modules` VALUES
    (1, 'file_upload', 'Upload a files via HTTP to web server', 'modules/file_upload/file_upload.php', 1, '2,5,2,3,4', 3),
    (2, 'user_login', 'Provide user login and details', 'modules/user_login/user_login.php', 1, '2,5,1,3', 0),
    (3, 'client_history', 'Save client history', 'modules/client_history/client_history.php', 1, '2', 2),
    (4, 'comments', 'Allow users to put comments', 'modules/comments/comments.php', 1, '2,5,3', 4),
    (5, 'header', 'Header index override', 'modules/header/header.php', 1, '2', 1),
    (6, 'account', 'Account management', 'modules/account/account.php', 1, '2', 5);

COMMIT;

UNLOCK TABLES;

--
-- Data for table "registration_keys"
--

LOCK TABLE `registration_keys` WRITE;

UNLOCK TABLES;

--
-- Data for table "sessions"
--

LOCK TABLE `sessions` WRITE;

UNLOCK TABLES;

--
-- Data for table "url"
--

LOCK TABLE `url` WRITE;

START TRANSACTION;

INSERT INTO `url` VALUES
    (1, 1, '/form/upload/*'),
    (2, 2, '/user/login/*'),
    (3, 2, '/user/logout/*'),
    (4, 1, '/form/get/*'),
    (5, 1, '/form/download/*'),
    (6, 4, '/form/comment/*'),
    (7, 1, '/form/list/*'),
    (8, 6, '/user/profile/*'),
    (9, 6, '/account/register/*'),
    (10, 6, '/account/verify/*'),
    (11, 6, '/account/proceed/*');

COMMIT;

UNLOCK TABLES;

--
-- Data for table "user_address"
--

LOCK TABLE `user_address` WRITE;

UNLOCK TABLES;

--
-- Data for table "users"
--

LOCK TABLE `users` WRITE;

UNLOCK TABLES;

