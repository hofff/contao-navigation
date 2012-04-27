-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

CREATE TABLE `tl_module` (

-- general fields
  `bbit_nav_roots` blob NULL,
  `bbit_nav_start` smallint(5) NOT NULL default '0',
  `bbit_nav_includeStart` char(1) NOT NULL default '',
  `bbit_nav_respectHidden` char(1) NOT NULL default '',
  `bbit_nav_respectPublish` char(1) NOT NULL default '',
  `bbit_nav_respectGuests` char(1) NOT NULL default '',
  `bbit_nav_showHidden` char(1) NOT NULL default '',
  `bbit_nav_showProtected` char(1) NOT NULL default '',
  `bbit_nav_showGuests` char(1) NOT NULL default '',
  `bbit_nav_addFields` blob NULL,
  `bbit_nav_noForwardResolution` char(1) NOT NULL default '',
  `bbit_nav_disableHooks` char(1) NOT NULL default '',
  
-- navigation menu fields
  `bbit_nav_currentAsRoot` char(1) NOT NULL default '',
  `bbit_nav_defineRoots` char(1) NOT NULL default '',
  `bbit_nav_defineStop` char(1) NOT NULL default '',
  `bbit_nav_stop` smallint(5) unsigned NOT NULL default '0',
  `bbit_nav_defineHard` char(1) NOT NULL default '',
  `bbit_nav_hard` smallint(5) unsigned NOT NULL default '0',
  `bbit_nav_isSitemap` char(1) NOT NULL default '',
  `bbit_nav_addLegacyCss` char(1) NOT NULL default '',

-- navigation preorder fields
  `bbit_nav_rootSelectionType` varchar(32) NOT NULL default '',
  `bbit_nav_chainDirections` blob NULL,
  `bbit_nav_defineDepth` char(1) NOT NULL default '',
  `bbit_nav_depth` smallint(5) unsigned NOT NULL default '0',

  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `tl_bbit_nav_hook` (

  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  
  `title` varchar(255) NOT NULL default '',
  `publish` char(1) NOT NULL default '',
  `hookType` varchar(255) NOT NULL default '',
  
  `tree` blob NULL,
  `entries` blob NULL,

  PRIMARY KEY  (`id`),
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `tl_bbit_nav_injection` (

  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  
  `injectionType` varchar(255) NOT NULL default '',
  `publish` char(1) NOT NULL default '',
  
  `pages` blob NULL,
  `injection` varchar(255) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
