<?php
/**
 * 24-48 Hrs Prediction Module Database Layer
 * 
 * Functions to handle database operations for 24-48 Hrs prediction records
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get predictions based on arguments
 * 
 * @param array $args
 * @return array
 */
function cv_get_predictions_24_48($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    $users_table = $wpdb->prefix . 'users';
    
    $defaults = array(
        'date'              => '',
        'element'           => '',
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
        $where[] = 't.date = %s';
        $params[] = sanitize_text_field($args['date']);
    }
    if (!empty($args['element'])) {
        $where[] = 't.element = %s';
        $params[] = sanitize_text_field($args['element']);
    }
    if (!empty($args['status'])) {
        $where[] = 't.status = %s';
        $params[] = sanitize_text_field($args['status']);
    }
    if (!$args['include_scheduled']) {
        $where[] = '(t.scheduled_at IS NULL OR t.scheduled_at <= %s)';
        $params[] = current_time('mysql');
    }
    if (!empty($args['search'])) {
        $where[] = '(t.prediction LIKE %s)';
        $wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    $order_by_col = in_array($args['order_by'], array('id', 'date', 'element', 'status')) ? $args['order_by'] : 'date';
    $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
    
    $sql = "SELECT t.*, u.display_name as creator_name 
            FROM $table t 
            LEFT JOIN $users_table u ON t.created_by = u.ID 
            WHERE $where_sql 
            ORDER BY t.$order_by_col $order";
            
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
 * Get single prediction
 * 
 * @param int $id
 * @return object|null
 */
function cv_get_prediction_24_48($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $id));
}

/**
 * Count predictions matching arguments
 * 
 * @param array $args
 * @return int
 */
function cv_count_predictions_24_48($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['date'])) {
        $where[] = 'date = %s';
        $params[] = sanitize_text_field($args['date']);
    }
    if (!empty($args['element'])) {
        $where[] = 'element = %s';
        $params[] = sanitize_text_field($args['element']);
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
        $where[] = '(prediction LIKE %s)';
        $wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
    if (!empty($params)) {
        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }
    return (int) $wpdb->get_var($sql);
}

/**
 * Insert new prediction
 * 
 * @param array $data
 * @return int|bool
 */
function cv_insert_prediction_24_48($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    $formats = array(
        'date'          => '%s',
        'element'       => '%s',
        'prediction'    => '%s',
        'suryoday'      => '%s',
        'suryast'       => '%s',
        'good_time'     => '%s',
        'hindu_muhurat' => '%s',
        'rahu_kaal'     => '%s',
        'status'        => '%s',
        'scheduled_at'  => '%s',
        'created_by'    => '%d',
        'updated_by'    => '%d'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->insert($table, $data, $data_formats);
    return $result ? $wpdb->insert_id : false;
}

/**
 * Update prediction
 * 
 * @param int $id
 * @param array $data
 * @return bool
 */
function cv_update_prediction_24_48($id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    $formats = array(
        'date'          => '%s',
        'element'       => '%s',
        'prediction'    => '%s',
        'suryoday'      => '%s',
        'suryast'       => '%s',
        'good_time'     => '%s',
        'hindu_muhurat' => '%s',
        'rahu_kaal'     => '%s',
        'status'        => '%s',
        'scheduled_at'  => '%s',
        'updated_by'    => '%d'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->update($table, $data, array('id' => (int) $id), $data_formats, array('%d'));
    return $result !== false;
}

/**
 * Delete single prediction
 * 
 * @param int $id
 * @return bool
 */
function cv_delete_prediction_24_48($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    return $wpdb->delete($table, array('id' => (int) $id), array('%d')) !== false;
}

/**
 * Bulk delete predictions
 * 
 * @param array $ids
 * @return int
 */
function cv_delete_bulk_predictions_24_48($ids) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';
    if (empty($ids) || !is_array($ids)) return 0;
    $ids_clean = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_clean), '%d'));
    return (int) $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id IN ($placeholders)", $ids_clean));
}
