-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

CREATE TABLE `tl_module` (
  `backboneit_navigation_start` smallint(5) NOT NULL default '0',
  `backboneit_navigation_includeStart` char(1) NOT NULL default '',
  `backboneit_navigation_currentAsRoot` char(1) NOT NULL default '',
  `backboneit_navigation_defineRoots` char(1) NOT NULL default '',
  `backboneit_navigation_roots` blob NULL,
  `backboneit_navigation_respectGuests` char(1) NOT NULL default '',
  `backboneit_navigation_respectHidden` char(1) NOT NULL default '',
  `backboneit_navigation_respectPublish` char(1) NOT NULL default '',
  `backboneit_navigation_defineStop` char(1) NOT NULL default '',
  `backboneit_navigation_stop` smallint(5) unsigned NOT NULL default '0',
  `backboneit_navigation_defineHard` char(1) NOT NULL default '',
  `backboneit_navigation_hard` smallint(5) unsigned NOT NULL default '0',
  `backboneit_navigation_showProtected` char(1) NOT NULL default '',
  `backboneit_navigation_showHidden` char(1) NOT NULL default '',
  `backboneit_navigation_isSitemap` char(1) NOT NULL default '',
  `backboneit_navigation_addLegacyCss` char(1) NOT NULL default '',
  `backboneit_navigation_addFields` blob NULL,
  `backboneit_navigation_noForwardResolution` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
