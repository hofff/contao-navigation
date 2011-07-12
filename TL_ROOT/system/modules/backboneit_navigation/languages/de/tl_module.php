<?php

$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_root_legend']
	= 'Konfiguration der Wurzelseiten';
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_legend']
	= 'Navigations-Einstellungen';

$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_start']
	= array('Startebene', 'Relativ zu den Referenzseiten. Negative Werte sind möglich.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_includeStart']
	= array('Startebene anzeigen', 'Rendert die Startebene in die Navigation.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_currentAsRoot']
	= array('Aktuelle Seite als Referenzseite', 'Fügt die aktuelle Seite zu den Referenzseiten hinzu.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineRoots']
	= array('Referenzseiten festlegen', 'Wurzelseiten der Navigation auswählen. Standardmäßig wird immer der Startpunkt des aktuellen Seitenbaums verwendet. Leere Auswahl kann sinnvoll in Verbindung mit der Option "Aktuelle Seite als Referenzseite" sein.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_roots']
	= array('Referenzseiten', 'Ausgewählte Seiten als Wurzelseiten der Navigation benutzen. Leere Auswahl kann sinnvoll in Verbindung mit der Option "Aktuelle Seite als Referenzseite" sein.');
	
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectGuests']
	= array('"Nur für Gäste sichtbar" beachten', 'Nur Relevant für die Ermittlung der Startebene.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectHidden']
	= array('Versteckte Seiten beachten', 'Nur Relevant für die Ermittlung der Startebene.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_respectPublish']
	= array('Unveröffentlichte Seiten beachten', 'Nur Relevant für die Ermittlung der Startebene.');
	
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineStop']
	= array('Anzahl angezeigter Ebenen anpassen', 'Anzahl der Ebenen die immer angezeigt werden festlegen.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_stop']
	= array('Angezeigte Ebenen', 'Anzahl der Ebenen die immer angezeigt werden. (Größer oder gleich 0)');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_defineHard']
	= array('Anzahl maximal angezeigter Ebenen anpassen', 'Obergrenze der sichbaren Ebenen festlegen.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_hard']
	= array('Maximal angezeigte Ebenen', 'Obergrenze der sichbaren Ebenen. (Größer oder gleich 1)');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showProtected']
	= array('Geschützte Seiten anzeigen', 'Seiten und Unterseiten mit Mitgliedergruppen-Beschränkung anzeigen.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_showHidden']
	= array('Versteckte Seiten anzeigen', 'Seiten und Unterseiten mit Option "Im Menü verstecken" trotzdem anzeigen.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_isSitemap']
	= array('Als Sitemap verwenden', 'Es werden die Sichtbarkeitseinstellungen für Sitemaps der Seiten genutzt. Wird ignoriert, wenn "Versteckte Seiten anzeigen" ausgewählt ist.');
	
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addLegacyCss']
	= array('CSS-Klasse des originalen Navigationsmodul', 'Fügt die Klasse "mod_navigation" zum div-Container des Moduls hinzu, um dieses Modul als vollkompatiblen Ersatz zu verwenden.');
$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_addFields']
	= array('Zusätzliche Seitenfelder', 'Diese Datenfelder einer Seite werden zusätzlich in das Navigations-Template übergeben.');

$GLOBALS['TL_LANG']['tl_module']['backboneit_navigation_noForwardResolution']
	= array('Weiterleitungsauflösung deaktivieren', 'Deaktiviert das ersetzen von Weiterleitungs-URLs mit der Ziel-URL, was bei sehr vielen Weiterleitungen oder stark besuchten ungecachten Websiten, die Performance verbessern kann. Zu beachten ist, das <b>verkettete</b> Weiterleitungen, welche die aktuelle Seite als Weiterleitungs<b>end</b>ziel besitzen nicht mehr als aktiv gekennzeichnet werden (kein "isActive" mehr im Template und keine "active" CSS-Klasse).');
	