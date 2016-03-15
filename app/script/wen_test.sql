CREATE DATABASE IF NOT EXISTS wen_test DEFAULT CHARSET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `t_userinfo`;
CREATE TABLE `t_userinfo` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(30) NOT NULL,
	`title` varchar(100) NULL default '',
	`status` int(1) NOT NULL default 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;