<?php
/**
 * Database Installation
 * 
 * Handles table creation and structure adjustments on activation
 * 
 * @package Clairvoyant_Core
 * @subpackage Database
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create custom tables for the plugin
 */
function cv_install_database() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    $users_table = $wpdb->prefix . 'users';
    
    // Table 1: wp_cv_daily_rashi
    $table_daily_rashi = $wpdb->prefix . 'cv_daily_rashi';
    $sql_daily_rashi = "CREATE TABLE $table_daily_rashi (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        zodiac_sign varchar(20) NOT NULL,
        prediction longtext NOT NULL,
        lucky_number varchar(50) DEFAULT '',
        lucky_color varchar(50) DEFAULT '',
        today_luck_rating int(11) DEFAULT 0,
        career varchar(500) DEFAULT '',
        love varchar(500) DEFAULT '',
        health varchar(500) DEFAULT '',
        finance varchar(500) DEFAULT '',
        status varchar(20) DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by bigint(20) unsigned DEFAULT NULL,
        updated_by bigint(20) unsigned DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_date_zodiac (date, zodiac_sign),
        KEY idx_zodiac (zodiac_sign),
        KEY idx_date (date),
        KEY idx_status (status)
    ) $charset_collate;";
    
    // Table 2: wp_cv_daily_horoscope
    $table_daily_horoscope = $wpdb->prefix . 'cv_daily_horoscope';
    $sql_daily_horoscope = "CREATE TABLE $table_daily_horoscope (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        date date NOT NULL,
        zodiac_sign varchar(20) NOT NULL,
        prediction longtext NOT NULL,
        career varchar(500) DEFAULT '',
        love varchar(500) DEFAULT '',
        health varchar(500) DEFAULT '',
        money varchar(500) DEFAULT '',
        lucky_number varchar(50) DEFAULT '',
        lucky_color varchar(50) DEFAULT '',
        today_rating int(11) DEFAULT 0,
        status varchar(20) DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_date_zodiac (date, zodiac_sign),
        KEY idx_zodiac (zodiac_sign),
        KEY idx_date (date),
        KEY idx_status (status)
    ) $charset_collate;";
 
    // Table 3: wp_cv_weekly_horoscope
    $table_weekly_horoscope = $wpdb->prefix . 'cv_weekly_horoscope';
    $sql_weekly_horoscope = "CREATE TABLE $table_weekly_horoscope (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        week_start date NOT NULL,
        week_end date NOT NULL,
        zodiac_sign varchar(20) NOT NULL,
        prediction longtext NOT NULL,
        career varchar(500) DEFAULT '',
        love varchar(500) DEFAULT '',
        health varchar(500) DEFAULT '',
        money varchar(500) DEFAULT '',
        overall_rating int(11) DEFAULT 0,
        status varchar(20) DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_zodiac (zodiac_sign),
        KEY idx_week_start (week_start),
        KEY idx_status (status)
    ) $charset_collate;";
 
    // Table 4: wp_cv_transit_horoscope
    $table_transit_horoscope = $wpdb->prefix . 'cv_transit_horoscope';
    $sql_transit_horoscope = "CREATE TABLE $table_transit_horoscope (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        planet varchar(50) NOT NULL,
        transit_start_date date NOT NULL,
        transit_end_date date DEFAULT NULL,
        title varchar(255) NOT NULL,
        prediction longtext NOT NULL,
        affected_signs varchar(500) DEFAULT '',
        remedies longtext DEFAULT '',
        status varchar(20) DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_planet (planet),
        KEY idx_start_date (transit_start_date),
        KEY idx_status (status)
    ) $charset_collate;";
 
    // Table 5: wp_cv_testimonials
    $table_testimonials = $wpdb->prefix . 'cv_testimonials';
    $sql_testimonials = "CREATE TABLE $table_testimonials (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        client_name varchar(255) NOT NULL,
        client_image longtext DEFAULT NULL,
        service varchar(255) DEFAULT '',
        rating int(11) DEFAULT 0,
        review longtext NOT NULL,
        location varchar(255) DEFAULT '',
        status varchar(20) DEFAULT 'draft',
        scheduled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_rating (rating),
        KEY idx_status (status)
    ) $charset_collate;";

    // Table 6: wp_cv_settings
    $table_settings = $wpdb->prefix . 'cv_settings';
    $sql_settings = "CREATE TABLE $table_settings (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        setting_key varchar(255) NOT NULL,
        setting_value longtext DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_key (setting_key)
    ) $charset_collate;";

    // Run dbDelta for structural updates
    dbDelta($sql_daily_rashi);
    dbDelta($sql_daily_horoscope);
    dbDelta($sql_weekly_horoscope);
    dbDelta($sql_transit_horoscope);
    dbDelta($sql_testimonials);
    dbDelta($sql_settings);

    // Apply foreign keys directly using raw SQL since dbDelta doesn't support them fully
    // Adding FOREIGN KEY constraint if they do not exist
    $has_fk_created = $wpdb->get_results("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table_daily_rashi' AND COLUMN_NAME = 'created_by' AND REFERENCED_TABLE_NAME = '$users_table'");
    if (empty($has_fk_created)) {
        $wpdb->query("ALTER TABLE $table_daily_rashi ADD CONSTRAINT fk_cv_daily_rashi_created_by FOREIGN KEY (created_by) REFERENCES $users_table (ID) ON DELETE SET NULL");
    }

    $has_fk_updated = $wpdb->get_results("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table_daily_rashi' AND COLUMN_NAME = 'updated_by' AND REFERENCED_TABLE_NAME = '$users_table'");
    if (empty($has_fk_updated)) {
        $wpdb->query("ALTER TABLE $table_daily_rashi ADD CONSTRAINT fk_cv_daily_rashi_updated_by FOREIGN KEY (updated_by) REFERENCES $users_table (ID) ON DELETE SET NULL");
    }
}
