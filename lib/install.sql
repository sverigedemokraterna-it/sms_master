SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `level` enum('debug','error','info') NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Log events';

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `completed` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is this order complete?',
  `datetime_completed` datetime DEFAULT NULL,
  `datetime_created` datetime NOT NULL,
  `email_addresses` text COMMENT 'Who to notify of changes? Comma separated' DEFAULT NULL,
  `email_report_sent` datetime DEFAULT NULL,
  `email_report_text` longtext DEFAULT NULL,
  `error_log` longtext NOT NULL COMMENT 'Error log for this order',
  `numbers` longtext NOT NULL COMMENT 'Numbers to send the order to.',
  `number_count` int(11) NOT NULL COMMENT 'How many numbers there are',
  `order_uuid` varchar(128) NOT NULL COMMENT 'Unique order ID used by users',
  `priority` int(11) NOT NULL DEFAULT '0' COMMENT 'Higher priority gets sent first',
  `text` text NOT NULL COMMENT 'Text to send',
  `user_id` int(11) NOT NULL COMMENT 'Which user created the order',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_id` (`order_uuid`),
  KEY `finished` (`completed`),
  KEY `order_uuid` (`order_uuid`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `order_numbers` (
  `number_id` int(11) NOT NULL AUTO_INCREMENT,
  `failures` int(1) NOT NULL DEFAULT '0' COMMENT 'How many failures this number has had',
  `number` text NOT NULL,
  `order_id` int(11) NOT NULL,
  `sent` datetime DEFAULT NULL COMMENT 'When was the order sent?',
  `touched` datetime DEFAULT NULL COMMENT 'This number is about to be sent',
  PRIMARY KEY (`number_id`),
  KEY `order_id` (`order_id`),
  KEY `sent` (`sent`),
  KEY `touched` (`touched`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Numbers to send order to';

CREATE TABLE IF NOT EXISTS `phones` (
  `phone_id` int(11) NOT NULL AUTO_INCREMENT,
  `clean` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Should this phone be cleaned?',
  `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is this phone enabled?',
  `phone_description` varchar(128) NOT NULL COMMENT 'Phone description',
  `phone_index` int(11) NOT NULL DEFAULT '1' COMMENT 'Index of phone in gnokii config',
  `slave_id` int(11) NOT NULL DEFAULT '1',
  `touched` datetime DEFAULT NULL COMMENT 'When this phone was last touched at all',
  `touched_successfully` datetime DEFAULT NULL COMMENT 'When this phone was last used',
  PRIMARY KEY (`phone_id`),
  KEY `enabled` (`enabled`),
  KEY `touched` (`touched`),
  KEY `slave_id` (`slave_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of setting',
  `key` varchar(50) NOT NULL COMMENT 'Key',
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`),
  KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hashmap of settings';

CREATE TABLE IF NOT EXISTS `slaves` (
  `slave_id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(128) NOT NULL COMMENT 'Name or IP of host to connect to',
  `port` int(5) NOT NULL DEFAULT '22' COMMENT 'Connection port',
  `public_key` text NOT NULL COMMENT 'Public key of user',
  `private_key` text NOT NULL COMMENT 'Private key of user',
  `slave_description` varchar(128) NOT NULL COMMENT 'Quick description of this slave',
  `username` varchar(128) NOT NULL COMMENT 'Connect to the slave using this username',
  PRIMARY KEY (`slave_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `administrator` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is the user an administrator',
  `datetime_created` datetime DEFAULT NULL COMMENT 'When the user was created',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is the user active?',
  `public_key` text NOT NULL,
  `public_key_id` varchar(8) NOT NULL,
  `user_description` varchar(128) NOT NULL COMMENT 'Short description of user',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `public_key_id` (`public_key_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Users and their public keys';

