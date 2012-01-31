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
  `bbit_navi_roots` blob NULL,
  `bbit_navi_start` smallint(5) NOT NULL default '0',
  `bbit_navi_includeStart` char(1) NOT NULL default '',
  `bbit_navi_respectHidden` char(1) NOT NULL default '',
  `bbit_navi_respectPublish` char(1) NOT NULL default '',
  `bbit_navi_respectGuests` char(1) NOT NULL default '',
  `bbit_navi_showHidden` char(1) NOT NULL default '',
  `bbit_navi_showProtected` char(1) NOT NULL default '',
  `bbit_navi_showGuests` char(1) NOT NULL default '',
  `bbit_navi_addFields` blob NULL,
  `bbit_navi_noForwardResolution` char(1) NOT NULL default '',
  `bbit_navi_disableHooks` char(1) NOT NULL default '',
  
-- navigation menu fields
  `bbit_navi_currentAsRoot` char(1) NOT NULL default '',
  `bbit_navi_defineRoots` char(1) NOT NULL default '',
  `bbit_navi_defineStop` char(1) NOT NULL default '',
  `bbit_navi_stop` smallint(5) unsigned NOT NULL default '0',
  `bbit_navi_defineHard` char(1) NOT NULL default '',
  `bbit_navi_hard` smallint(5) unsigned NOT NULL default '0',
  `bbit_navi_isSitemap` char(1) NOT NULL default '',
  `bbit_navi_addLegacyCss` char(1) NOT NULL default '',

-- navigation preorder fields
  `bbit_navi_rootSelectionType` varchar(32) NOT NULL default '',
  `bbit_navi_chainDirections` blob NULL,
  `bbit_navi_defineDepth` char(1) NOT NULL default '',
  `bbit_navi_depth` smallint(5) unsigned NOT NULL default '0',

  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `tl_bbit_navi_hook` (

  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  
  `title` varchar(255) NOT NULL default '',
  `publish` char(1) NOT NULL default '',
  `hookType` varchar(255) NOT NULL default '',
  
  `tree` blob NULL,
  `entries` blob NULL,

  PRIMARY KEY  (`id`),
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `tl_bbit_navi_injection` (

  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  
  `injectMode` varchar(255) NOT NULL default '',
  `publish` char(1) NOT NULL default '',
  
  `pages` blob NULL,
  `injection` varchar(255) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
