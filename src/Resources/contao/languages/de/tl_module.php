<?php

declare(strict_types=1);

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_root_legend'] = 'Konfiguration der Wurzelseiten';
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_legend']      = 'Navigations-Einstellungen';

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_start']         = [
    'Startebene',
    'Relativ zu den Referenzseiten. Negative Werte sind möglich.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_currentAsRoot'] = [
    'Aktuelle Seite als Referenzseite',
    'Fügt die aktuelle Seite zu den Referenzseiten hinzu.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_defineRoots']   = [
    'Referenzseiten festlegen',
    'Wurzelseiten der Navigation auswählen. Standardmäßig wird immer der Startpunkt des aktuellen Seitenbaums verwendet. Leere Auswahl kann sinnvoll in Verbindung mit der Option "Aktuelle Seite als Referenzseite" sein.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_roots']         = [
    'Referenzseiten',
    'Ausgewählte Seiten als Wurzelseiten der Navigation benutzen. Leere Auswahl kann sinnvoll in Verbindung mit der Option "Aktuelle Seite als Referenzseite" sein.',
];

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_respectGuests']  = [
    '"Nur für Gäste sichtbar" beachten',
    'Nur Relevant für die Ermittlung der Startebene.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_respectHidden']  = [
    'Versteckte Seiten beachten',
    'Nur Relevant für die Ermittlung der Startebene.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_respectPublish'] = [
    'Unveröffentlichte Seiten beachten',
    'Nur Relevant für die Ermittlung der Startebene.',
];

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_includeStart']    = [
    'Startebene anzeigen',
    'Rendert die Startebene in die Navigation.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_showHiddenStart'] = [
    'Versteckte Seiten anzeigen (Startebene)',
    'Seiten in der Startebene mit der Option "Im Menü verstecken" trotzdem anzeigen.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_defineStop']      = [
    'Stop-Ebenen anpassen',
    'Ebenen die tiefer als die Stop-Ebenen liegen werden nur dann angezeigt, wenn diese sich im aktuellen Navigationspfad befinden.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_stop']            = [
    'Stop-Ebenen',
    'Stop-Ebenen als nicht-negative Ganzzahl (0, 1, 2, ...). Es können mehrere Stop-Ebenen als aufsteigend sortierte, Komma-separierte Liste angegeben werden. Angaben sind relativ zur Startebene.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_defineHard']      = [
    'Anzahl maximal auszugebender Ebenen anpassen',
    'Es werden keine Ebenen ausgegeben die tiefer liegen.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_hard']            = [
    'Maximal auszugebende Ebenen',
    'Angabe als positive Ganzzahl (1, 2, 3, ...). Angaben sind relativ zur Startebene.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_showProtected']   = [
    'Geschützte Seiten anzeigen',
    'Seiten und Unterseiten mit Mitgliedergruppen-Beschränkung anzeigen.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_showHidden']      = [
    'Versteckte Seiten anzeigen',
    'Seiten und Unterseiten mit Option "Im Menü verstecken" trotzdem anzeigen.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_showGuests']      = [
    '"Nur für Gäste sichtbar" ignorieren',
    'Seiten und Unterseiten mit der Option "Nur für Gäste sichtbar" auch angemeldeten Benutzern anzeigen.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_isSitemap']       = [
    'Als Sitemap verwenden',
    'Es werden die Sichtbarkeitseinstellungen für Sitemaps der Seiten genutzt. Wird ignoriert, wenn "Versteckte Seiten anzeigen" ausgewählt ist.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_hideSingleLevel'] = [
    'Nur Multiebenen Navigationen anzeigen',
    'Diese Navigation nur dann ausgeben, wenn mehrere Navigationsebenen vorhanden sind.',
];

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_addLegacyCss'] = [
    'CSS-Klasse des originalen Navigationsmodul',
    'Fügt die Klasse "mod_navigation" zum div-Container des Moduls hinzu, um dieses Modul als vollkompatiblen Ersatz zu verwenden.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_addFields']    = [
    'Zusätzliche Seitenfelder',
    'Diese Datenfelder einer Seite werden zusätzlich in das Navigations-Template übergeben.',
];

$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_noForwardResolution'] = [
    'Weiterleitungsauflösung deaktivieren',
    'Deaktiviert das ersetzen von Weiterleitungs-URLs mit der Ziel-URL, was bei sehr vielen Weiterleitungen oder stark besuchten ungecachten Websiten, die Performance verbessern kann. Zu beachten ist, das <b>verkettete</b> Weiterleitungen, welche die aktuelle Seite als Weiterleitungs<b>end</b>ziel besitzen nicht mehr als aktiv gekennzeichnet werden (kein "isActive" mehr im Template und keine "active" CSS-Klasse).',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_showErrorPages']      = [
    'Fehlerseiten anzeigen',
    'Fehlerseiten vom Typ "403" und "404" werden in der Navigation angezeigt.',
];
$GLOBALS['TL_LANG']['tl_module']['hofff_navigation_disableEvents']       = [
    'Events deaktivieren',
    'Deaktiviert das Ausführen der Navigations-Events. Kann zu einer besseren Performance führen, wenn Events für diese Navigation nicht notwendig sind.',
];
