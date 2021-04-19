<?php
/*
Plugin Name: Arbeitsstunden-Verwaltung
Plugin URI:
Description: Management- und Übersichtstool für geleistete und zu leistende Arbeitsstunden der Mitglieder
Author: Markus Buscher - Segel-Club Hennesee e.V. Meschede
Author URI: https://schm.info
Version: 1.0.0
*/

// Direkten Aufruf verhindern
if(!defined( 'WPINC'))
{
    die;
}

// do database preparations
global $schm_av_db_version;
$schm_av_db_version = '1.0.0';
require_once(__DIR__ . '/install.php');
register_activation_hook( __FILE__, 'jal_install' );
add_action( 'plugins_loaded', 'schmAVUpdateCheck' );

// load methods for views
require_once(__DIR__ . '/views.php');


// create menu
add_action('admin_menu', 'schmAVAddMenu');
function schmAVAddMenu()
{
    add_menu_page('Arbeitsstunden-Verwaltung', 'Arbeitsstunden', 'read', 'schmav', 'schmAVIndex', '', 30);
	
	// We don't need to have the main as a submenu entry, so this removes it
	add_submenu_page( 'schmav', '', '', 'read', 'schmav', 'schmAVIndex');
	remove_submenu_page( 'schmav', 'schmav');
	
	add_submenu_page('schmav', 'Arbeitsstunden-Verwaltung', 'Verwaltung', 'read', 'schmav_index', 'schmAVIndex');
	add_submenu_page('schmav', 'Geleistete Arbeitsstunden', 'geleistet', 'read', 'schmav_geleistet', 'schmAVListDone');
	add_submenu_page('schmav', 'Offene Arbeitsstunden', 'offen', 'read', 'schmav_offen', 'schmAVListOpen');
}
