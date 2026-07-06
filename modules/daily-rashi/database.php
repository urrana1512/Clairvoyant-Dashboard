<?php
/**
 * Daily Rashi Module Database Layer
 * 
 * Functions to handle database operations for Daily Rashi records
 * 
 * @package Clairvoyant_Core
 * @subpackage Daily_Rashi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fetch Daily Rashi records from database
 * 
 * @param array $args Filter parameters
 * @return array List of record objects
 */
function cv_get_daily_rashis($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    $defaults = array(
        'date'              => '',
        'zodiac_sign'       => '',
        'status'            => '',
        'search'            => '',
        'limit'             => 10,
        'offset'            => 0,
        'order_by'          => 'date',
        'order'             => 'DESC',
        'include_scheduled' => is_admin()
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['date'])) {
        $where[] = 'date = %s';
        $params[] = sanitize_text_field($args['date']);
    }
    
    if (!empty($args['zodiac_sign'])) {
        $where[] = 'zodiac_sign = %s';
        $params[] = sanitize_text_field($args['zodiac_sign']);
    }
    
    if (!empty($args['status'])) {
        $where[] = 'status = %s';
        $params[] = sanitize_text_field($args['status']);
    }
    
    if (!$args['include_scheduled']) {
        $where[] = '(scheduled_at IS NULL OR scheduled_at <= %s)';
        $params[] = current_time('mysql');
    }
    
    if (!empty($args['search'])) {
        $where[] = '(prediction LIKE %s OR career LIKE %s OR love LIKE %s)';
        $search_wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $search_wildcard;
        $params[] = $search_wildcard;
        $params[] = $search_wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    
    $order_by = in_array($args['order_by'], array('id', 'date', 'zodiac_sign', 'today_luck_rating', 'status', 'created_at', 'updated_at')) ? $args['order_by'] : 'date';
    $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
    
    $sql = "SELECT r.*, u.display_name as creator_name 
            FROM $table r 
            LEFT JOIN {$wpdb->prefix}users u ON r.created_by = u.ID 
            WHERE $where_sql 
            ORDER BY r.$order_by $order";
            
    if ($args['limit'] > 0) {
        $sql .= " LIMIT %d OFFSET %d";
        $params[] = (int) $args['limit'];
        $params[] = (int) $args['offset'];
    }
    
    if (!empty($params)) {
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    return $wpdb->get_results($sql);
}

/**
 * Fetch a single Daily Rashi record
 * 
 * @param int $id Record ID
 * @return object|null
 */
function cv_get_daily_rashi($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        (int) $id
    ));
}

/**
 * Get count of Daily Rashi records satisfying filter criteria
 * 
 * @param array $args Filter parameters
 * @return int Total records count
 */
function cv_count_daily_rashis($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['date'])) {
        $where[] = 'date = %s';
        $params[] = sanitize_text_field($args['date']);
    }
    
    if (!empty($args['zodiac_sign'])) {
        $where[] = 'zodiac_sign = %s';
        $params[] = sanitize_text_field($args['zodiac_sign']);
    }
    
    if (!empty($args['status'])) {
        $where[] = 'status = %s';
        $params[] = sanitize_text_field($args['status']);
    }
    
    $include_scheduled = isset($args['include_scheduled']) ? (bool) $args['include_scheduled'] : is_admin();
    if (!$include_scheduled) {
        $where[] = '(scheduled_at IS NULL OR scheduled_at <= %s)';
        $params[] = current_time('mysql');
    }
    
    if (!empty($args['search'])) {
        $where[] = '(prediction LIKE %s OR career LIKE %s OR love LIKE %s)';
        $search_wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $search_wildcard;
        $params[] = $search_wildcard;
        $params[] = $search_wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
    
    if (!empty($params)) {
        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }
    
    return (int) $wpdb->get_var($sql);
}

/**
 * Insert new Daily Rashi record
 * 
 * @param array $data Sanitized record values
 * @return int|false Inserted ID or false
 */
function cv_insert_daily_rashi($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    $formats = array(
        'date'              => '%s',
        'zodiac_sign'       => '%s',
        'prediction'        => '%s',
        'lucky_number'      => '%s',
        'lucky_color'       => '%s',
        'today_luck_rating' => '%d',
        'career'            => '%s',
        'love'              => '%s',
        'health'            => '%s',
        'finance'           => '%s',
        'status'            => '%s',
        'scheduled_at'      => '%s',
        'created_by'        => '%d',
        'updated_by'        => '%d'
    );
    
    // Prepare format subset mapping to data columns
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) {
            $data_formats[] = $formats[$key];
        }
    }
    
    $result = $wpdb->insert($table, $data, $data_formats);
    return $result ? $wpdb->insert_id : false;
}

/**
 * Update an existing Daily Rashi record
 * 
 * @param int $id Record ID
 * @param array $data Record values
 * @return bool Whether record updated successfully
 */
function cv_update_daily_rashi($id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    $formats = array(
        'date'              => '%s',
        'zodiac_sign'       => '%s',
        'prediction'        => '%s',
        'lucky_number'      => '%s',
        'lucky_color'       => '%s',
        'today_luck_rating' => '%d',
        'career'            => '%s',
        'love'              => '%s',
        'health'            => '%s',
        'finance'           => '%s',
        'status'            => '%s',
        'scheduled_at'      => '%s',
        'updated_by'        => '%d'
    );
    
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) {
            $data_formats[] = $formats[$key];
        }
    }
    
    $result = $wpdb->update(
        $table, 
        $data, 
        array('id' => (int) $id), 
        $data_formats, 
        array('%d')
    );
    
    return $result !== false;
}

/**
 * Delete a Daily Rashi record
 * 
 * @param int $id Record ID
 * @return bool
 */
function cv_delete_daily_rashi($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    $result = $wpdb->delete($table, array('id' => (int) $id), array('%d'));
    return $result !== false;
}

/**
 * Bulk delete Daily Rashi records
 * 
 * @param array $ids List of Record IDs
 * @return int Total deleted records
 */
function cv_delete_bulk_daily_rashis($ids) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_daily_rashi';
    
    if (empty($ids) || !is_array($ids)) {
        return 0;
    }
    
    $ids_clean = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_clean), '%d'));
    
    $sql = $wpdb->prepare("DELETE FROM $table WHERE id IN ($placeholders)", $ids_clean);
    return (int) $wpdb->query($sql);
}
