<?php
/**
 * Helper Functions
 * 
 * General utility functions for the Clairvoyant Core plugin
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all zodiac signs
 * 
 * @return array
 */
function cv_get_zodiac_list() {
    return defined('CV_ZODIAC_SIGNS') ? CV_ZODIAC_SIGNS : array();
}

/**
 * Get specific zodiac sign details
 * 
 * @param string $sign Sign slug
 * @return array|false
 */
function cv_get_zodiac_info($sign) {
    $signs = cv_get_zodiac_list();
    $sign = strtolower($sign);
    return isset($signs[$sign]) ? $signs[$sign] : false;
}

/**
 * Render rating as stars
 * 
 * @param int $rating Rating out of 5
 * @return string HTML stars
 */
function cv_render_star_rating($rating) {
    $rating = (int) $rating;
    $html = '<div class="cv-star-rating" title="' . esc_attr($rating) . ' / 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="cv-star filled">★</span>';
        } else {
            $html .= '<span class="cv-star">☆</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Render status badge
 * 
 * @param string $status
 * @return string HTML Badge
 */
function cv_get_status_badge($status) {
    $status = sanitize_key($status);
    $label = ucfirst($status);
    $class = 'cv-badge-' . $status;
    return sprintf('<span class="cv-badge %s">%s</span>', esc_attr($class), esc_html($label));
}

/**
 * Retrieve a setting value from the custom settings table
 * 
 * @param string $key Setting key
 * @param mixed $default Default value
 * @return mixed Setting value
 */
function cv_get_setting($key, $default = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cv_settings';
    
    // Check if table exists first (prevents errors on activation/install)
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return $default;
    }
    
    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT setting_value FROM $table_name WHERE setting_key = %s",
        $key
    ));
    
    if ($value === null) {
        return $default;
    }
    
    // Try decoding JSON if applicable
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }
    
    return $value;
}

/**
 * Render pagination links
 * 
 * @param int $total_items Total number of items
 * @param int $per_page Items per page
 * @param int $current_page Current page number
 * @param string $base_url URL to build page links
 * @return string HTML Pagination links
 */
function cv_render_pagination($total_items, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $per_page);
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="cv-pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $prev_url = add_query_arg('paged', $current_page - 1, $base_url);
        $html .= sprintf('<a href="%s" class="cv-page-link prev">← Previous</a>', esc_url($prev_url));
    } else {
        $html .= '<span class="cv-page-link disabled prev">← Previous</span>';
    }
    
    // Page list
    $html .= sprintf('<span class="cv-page-info">Page %d of %d</span>', esc_html($current_page), esc_html($total_pages));
    
    // Next button
    if ($current_page < $total_pages) {
        $next_url = add_query_arg('paged', $current_page + 1, $base_url);
        $html .= sprintf('<a href="%s" class="cv-page-link next">Next →</a>', esc_url($next_url));
    } else {
        $html .= '<span class="cv-page-link disabled next">Next →</span>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Format DATE database field into user-friendly format
 * 
 * @param string $date_str YYYY-MM-DD
 * @return string Formatted date
 */
function cv_format_date($date_str) {
    if (empty($date_str)) {
        return '';
    }
    return date_i18n(get_option('date_format'), strtotime($date_str));
}

/**
 * Log action activity
 * 
 * @param string $action_text
 */
function cv_log_activity($action_text) {
    $logs = get_option('cv_activity_log', array());
    if (!is_array($logs)) {
        $logs = array();
    }
    $user = wp_get_current_user();
    
    array_unshift($logs, array(
        'timestamp' => current_time('mysql'),
        'user'      => $user->display_name ? $user->display_name : 'System',
        'action'    => $action_text
    ));
    
    // Slice to keep only last 20 elements
    $logs = array_slice($logs, 0, 20);
    update_option('cv_activity_log', $logs);
}

/**
 * Clear all shortcode cache transients and flush external/plugin caches
 */
function cv_clear_shortcode_transients() {
    global $wpdb;
    
    // 1. Delete transients from database options (fallback for standard setups)
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_cv_shortcode_%' OR option_name LIKE '_transient_timeout_cv_shortcode_%'");
    
    // 2. If external object cache (Redis/Memcached) is active, flush it to purge object-cached transients
    if (wp_using_ext_object_cache()) {
        wp_cache_flush();
    }
    
    // 3. Purge LiteSpeed Cache (common for "Purge All" requests)
    if (class_exists('LiteSpeed_Cache_API')) {
        LiteSpeed_Cache_API::purge_all();
    }
    if (has_action('litespeed_purge_all')) {
        do_action('litespeed_purge_all');
    }
    
    // 4. Purge W3 Total Cache
    if (function_exists('w3tc_pgcache_flush')) {
        w3tc_pgcache_flush();
    }
    if (function_exists('w3tc_dbcache_flush')) {
        w3tc_dbcache_flush();
    }
    if (function_exists('w3tc_objectcache_flush')) {
        w3tc_objectcache_flush();
    }
    
    // 5. Purge WP Super Cache
    if (function_exists('wp_cache_clean_cache')) {
        global $file_prefix;
        wp_cache_clean_cache($file_prefix);
    }
    
    // 6. Purge WP Rocket
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }
    
    // 7. Purge SiteGround Optimizer
    if (function_exists('sg_cachepress_purge_cache')) {
        sg_cachepress_purge_cache();
    }
    
    // 8. Purge Autoptimize
    if (class_exists('autoptimizeCache')) {
        autoptimizeCache::clearall();
    }
    
    // 9. Purge WP Fastest Cache
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
        $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }
}


