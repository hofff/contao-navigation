-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

CREATE TABLE `tl_module` (
  `backboneit_navigation_skip` smallint(5) NOT NULL default '0',
  `backboneit_navigation_ignoreGuests` char(1) NOT NULL default '',
  `backboneit_navigation_ignoreHidden` char(1) NOT NULL default '',
  `backboneit_navigation_stop` smallint(5) unsigned NOT NULL default '0',
  `backboneit_navigation_hard` smallint(5) unsigned NOT NULL default '0',
  `backboneit_navigation_showProtected` char(1) NOT NULL default '',
  `backboneit_navigation_showHidden` char(1) NOT NULL default '',
  `backboneit_navigation_currentAsRoot` char(1) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
