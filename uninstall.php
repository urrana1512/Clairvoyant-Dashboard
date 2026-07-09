<?php
/**
 * Uninstall Cleanup
 * 
 * Cleans up options and drops custom tables when the plugin is uninstalled
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

// If uninstall is not called by WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Table list to drop
$tables = array(
    $wpdb->prefix . 'cv_daily_rashi',
    $wpdb->prefix . 'cv_daily_horoscope',
    $wpdb->prefix . 'cv_weekly_horoscope',
    $wpdb->prefix . 'cv_transit_horoscope',
    $wpdb->prefix . 'cv_testimonials',
    $wpdb->prefix . 'cv_settings',
    $wpdb->prefix . 'cv_prediction_24_48',
);

// Disable foreign key checks to avoid deletion blocks
$wpdb->query('SET FOREIGN_KEY_CHECKS = 0');

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

$wpdb->query('SET FOREIGN_KEY_CHECKS = 1');

// Delete options
delete_option('clairvoyant_installed');
delete_option('clairvoyant_version');
