<?php
/**
 * Settings Module Database Layer
 * 
 * Handles reading and writing key-value configuration values from cv_settings table
 * 
 * @package Clairvoyant_Core
 * @subpackage Settings
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Save setting key-value pair to database
 * 
 * @param string $key Setting key name
 * @param mixed $value Setting value
 * @return bool Whether save succeeded
 */
function cv_save_setting($key, $value) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_settings';
    
    // Normalize value
    if (is_array($value) || is_object($value)) {
        $value = json_encode($value);
    } else {
        $value = (string) $value;
    }
    
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE setting_key = %s",
        $key
    ));
    
    if ($existing !== null) {
        $result = $wpdb->update(
            $table,
            array('setting_value' => $value),
            array('setting_key' => $key),
            array('%s'),
            array('%s')
        );
        return $result !== false;
    } else {
        $result = $wpdb->insert(
            $table,
            array('setting_key' => $key, 'setting_value' => $value),
            array('%s', '%s')
        );
        return $result !== false;
    }
}
