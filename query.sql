CREATE TABLE `bets` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `subtitle` varchar(255) default NULL,
  `categories_id` int(11) NOT NULL default '0',
  `image` smallint(6) NOT NULL default '0',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `autor_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `categories_id` (`categories_id`,`id`)
) ENGINE=MyISAM;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL auto_increment,
  `position` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) default NULL,
   `image` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM;


CREATE TABLE `log` (
  `id` int(11) NOT NULL auto_increment,
  `rem_addr` varchar(14) NOT NULL default '',
  `rem_host` varchar(50) NOT NULL default '',
  `user` varchar(20) NOT NULL default '',
  `event` text NOT NULL,
  `rem_agt` varchar(50) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE `possibilities` (
  `id` int(11) NOT NULL auto_increment,
  `bets_id` int(11) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `win` char(3) NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `bets_id` (`bets_id`,`id`)
) ENGINE=MyISAM;

CREATE TABLE `sites` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `author` varchar(20) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL auto_increment,
  `possibilities_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `credits` smallint(6) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `possibilities_id` (`possibilities_id`,`user_id`,`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM;

CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(20) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `firstname` varchar(100) NOT NULL default '',
  `lastname` varchar(100) NOT NULL default '',
  `balance` int(6) NOT NULL default '0',
  `status` varchar(100) NOT NULL default '',
  `conf_num` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

INSERT INTO `user` VALUES (1, 'admin', '827ccb0eea8a706c4c34a16891f84e7b', 'info@yourbetoffice.com', 'Foo', 'Bar', 20000, 'administrator', '34791');

CREATE TABLE `userwins` (
  `id` int(11) NOT NULL auto_increment,
  `possibilities_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `won_credits` int(11) NOT NULL default '0',
  `quote` varchar(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `possibilities_id` (`possibilities_id`,`user_id`),
  KEY `user_id` (`user_id`,`possibilities_id`)
) ENGINE=MyISAM;
