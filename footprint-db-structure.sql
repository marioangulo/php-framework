# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.17)
# Database: footprint
# Generation Time: 2012-02-05 02:16:27 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `account`;

CREATE TABLE `account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name_first` varchar(45) NOT NULL DEFAULT '',
  `name_last` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`fk_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8;



# Dump of table admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name_first` varchar(45) NOT NULL DEFAULT '',
  `name_last` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`fk_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8;



# Dump of table category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `category`;

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_pivot_table` varchar(255) DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `is_active` enum('yes','no') DEFAULT 'yes',
  PRIMARY KEY (`id`),
  KEY `fk_pivot_table` (`fk_pivot_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ip_access
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ip_access`;

CREATE TABLE `ip_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `is_active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `ip_access` WRITE;
/*!40000 ALTER TABLE `ip_access` DISABLE KEYS */;

INSERT INTO `ip_access` (`id`, `ip_address`, `is_active`, `description`)
VALUES
	(1,'127.0.0.1','yes','This machine. //localhost');

/*!40000 ALTER TABLE `ip_access` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table note
# ------------------------------------------------------------

DROP TABLE IF EXISTS `note`;

CREATE TABLE `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(11) NOT NULL DEFAULT '0',
  `fk_pivot_table` varchar(64) NOT NULL DEFAULT '',
  `fk_pivot_id` int(11) NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `timestamp_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `pivot` (`fk_pivot_table`,`fk_pivot_id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `timestamp_created` (`timestamp_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table status
# ------------------------------------------------------------

DROP TABLE IF EXISTS `status`;

CREATE TABLE `status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_pivot_table` varchar(45) NOT NULL,
  `is_active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pivot_table` (`fk_pivot_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;



# Dump of table status_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `status_log`;

CREATE TABLE `status_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_status_id` int(10) unsigned NOT NULL,
  `fk_pivot_table` varchar(45) NOT NULL,
  `fk_pivot_id` int(10) unsigned NOT NULL,
  `fk_user_id` int(10) unsigned NOT NULL,
  `timestamp_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_status_id` (`fk_status_id`),
  KEY `fk_pivot` (`fk_pivot_table`,`fk_pivot_id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `timestamp_created` (`timestamp_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;



# Dump of table tag
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tag`;

CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_pivot_table` varchar(45) NOT NULL,
  `is_active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pivot_table` (`fk_pivot_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;



# Dump of table tag_data
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tag_data`;

CREATE TABLE `tag_data` (
  `fk_tag_id` int(10) unsigned NOT NULL,
  `fk_pivot_table` varchar(45) NOT NULL,
  `fk_pivot_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `unique` (`fk_tag_id`,`fk_pivot_table`,`fk_pivot_id`),
  KEY `fk_tag_id` (`fk_tag_id`),
  KEY `fk_pivot_table` (`fk_pivot_table`),
  KEY `fk_pivot` (`fk_pivot_table`,`fk_pivot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;



# Dump of table user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` enum('yes','no') NOT NULL DEFAULT 'no',
  `username` varchar(45) NOT NULL DEFAULT '',
  `password` varchar(45) NOT NULL DEFAULT '',
  `session_id` varchar(45) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `timezone` varchar(90) NOT NULL DEFAULT 'UTC',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `session_id` (`session_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`id`, `is_active`, `username`, `password`, `session_id`, `email`, `timezone`)
VALUES
	(1,'yes','root','63a9f0ea7bb98050796b649e85481845','','root@localhost','UTC');

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_action_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_action_log`;

CREATE TABLE `user_action_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_pivot_table` varchar(45) NOT NULL DEFAULT '',
  `fk_pivot_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL DEFAULT '',
  `timestamp_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `fk_pivot` (`fk_pivot_table`,`fk_pivot_id`),
  KEY `timestamp_created` (`timestamp_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;



# Dump of table user_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_group`;

CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_active` enum('yes','no') NOT NULL DEFAULT 'no',
  `name` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_category_id` (`fk_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;

INSERT INTO `user_group` (`id`, `fk_category_id`, `is_active`, `name`, `description`)
VALUES
	(1,1,'yes','Root','This is a special user group. If a user is a member of this group, they will by-pass ALL security settings. '),
	(2,1,'yes','Administrator','This is the administrator account group.'),
	(3,1,'yes','Account','This is the member account group.');

/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_group_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_group_category`;

CREATE TABLE `user_group_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` enum('yes','no') NOT NULL DEFAULT 'yes',
  `name` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `user_group_category` WRITE;
/*!40000 ALTER TABLE `user_group_category` DISABLE KEYS */;

INSERT INTO `user_group_category` (`id`, `is_active`, `name`, `description`)
VALUES
	(1,'yes','Default','This is a default user group category.');

/*!40000 ALTER TABLE `user_group_category` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_history`;

CREATE TABLE `user_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `details` text NOT NULL,
  `url` varchar(1024) NOT NULL DEFAULT '',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `timestamp_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`fk_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_membership
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_membership`;

CREATE TABLE `user_membership` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_default` enum('yes','no') NOT NULL DEFAULT 'no',
  `timestamp_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `timestamp_cancelled` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `fk_group_id` (`fk_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

LOCK TABLES `user_membership` WRITE;
/*!40000 ALTER TABLE `user_membership` DISABLE KEYS */;

INSERT INTO `user_membership` (`id`, `fk_user_id`, `fk_group_id`, `is_default`, `timestamp_created`, `timestamp_cancelled`)
VALUES
	(1,1,1,'yes','0000-00-00 00:00:00','0000-00-00 00:00:00');

/*!40000 ALTER TABLE `user_membership` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_permission
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_permission`;

CREATE TABLE `user_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_security_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `fk_group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `permit` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `fk_security_id` (`fk_security_id`),
  KEY `fk_user_id` (`fk_user_id`),
  KEY `fk_group_id` (`fk_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

LOCK TABLES `user_permission` WRITE;
/*!40000 ALTER TABLE `user_permission` DISABLE KEYS */;

INSERT INTO `user_permission` (`id`, `fk_security_id`, `fk_user_id`, `fk_group_id`, `permit`)
VALUES
	(1,1003,0,3,'yes'),
	(2,1000,0,3,'yes'),
	(3,1002,0,2,'yes'),
	(4,1000,0,2,'yes');

/*!40000 ALTER TABLE `user_permission` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_security
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_security`;

CREATE TABLE `user_security` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dk_id_parent` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(45) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `dk_id_parent` (`dk_id_parent`)
) ENGINE=InnoDB AUTO_INCREMENT=1004 DEFAULT CHARSET=utf8;

LOCK TABLES `user_security` WRITE;
/*!40000 ALTER TABLE `user_security` DISABLE KEYS */;

INSERT INTO `user_security` (`id`, `dk_id_parent`, `name`, `description`)
VALUES
	(1000,0,'May Skip IP Check','Users with this permission may access the system from ANY IP address.'),
	(1001,0,'/root','Access to the \"/root\" area of the system.'),
	(1002,0,'/admin','Access to the \"/admin\" area of the system.'),
	(1003,0,'/account','Access to the \"/account\" area of the system.');

/*!40000 ALTER TABLE `user_security` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
