<?php


function schmAVInstall()
{
	global $wpdb;
    global $schm_av_db_version;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
    $charset_collate = $wpdb->get_charset_collate();
	
	$table_name = $wpdb->prefix . "schm_av_mitglieder";
	$sqls[] = "CREATE TABLE $table_name (
  Id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  Vorname varchar(255) NOT NULL,
  Nachname varchar(255) NOT NULL,
  Email varchar(255) NOT NULL,
  Mitgliedschaft mediumint(9) UNSIGNED NOT NULL,
  PRIMARY KEY (Id)
) $charset_collate;";
    
    $table_name = $wpdb->prefix . "schm_av_arbeitsstunden";
    $sqls[] = "CREATE TABLE $table_name (
  Id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  Mitglied mediumint(9) UNSIGNED NOT NULL,
  Beschreibung text NOT NULL DEFAULT '',
  Datum date,
  Dauer decimal(3,1) DEFAULT 0 NOT NULL,
  PRIMARY KEY (Id)
) $charset_collate;";
	
	$table_name = $wpdb->prefix . "schm_av_saison";
	$sqls[] = "CREATE TABLE $table_name (
  Id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  Mitglied mediumint(9) UNSIGNED NOT NULL,
  Arbeitsgruppe mediumint(9) UNSIGNED NOT NULL,
  Jahr integer(4) UNSIGNED NOT NULL,
  Stunden integer(3) UNSIGNED NOT NULL,
  Stichtag date,
  PRIMARY KEY (Id)
) $charset_collate;";
	
	$table_name = $wpdb->prefix . "schm_av_arbeitsgruppen";
	$sqls[] = "CREATE TABLE $table_name (
  Id mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  Name varchar(255) NOT NULL,
  Ansprechpartner varchar(255) NOT NULL,
  PRIMARY KEY (Id)
) $charset_collate;";
	
	dbDelta($sqls);
    add_option('schm_av_db_version', $schm_av_db_version);
    
}


function schmAVUpdateCheck()
{
    global $schm_av_db_version;
    if (get_option("schm_av_db_version") != $schm_av_db_version)
    {
        schmAVInstall();
    }
}
