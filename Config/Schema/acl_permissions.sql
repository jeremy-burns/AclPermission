CREATE TABLE `acl_permissions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `plugin` varchar(100) DEFAULT NULL,
  `controller` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `allow` varchar(20) DEFAULT NULL,
  `deny` varchar(20) DEFAULT NULL,
  `complete` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;