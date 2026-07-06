<?php
/**
 * Transit Horoscope Module Database Layer
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_get_transit_horoscopes($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    
    $defaults = array(
        'planet'            => '',
        'status'            => '',
        'search'            => '',
        'limit'             => 10,
        'offset'            => 0,
        'order_by'          => 'transit_start_date',
        'order'             => 'DESC',
        'include_scheduled' => is_admin()
    );
    
    $args = wp_parse_args($args, $defaults);
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['planet'])) {
        $where[] = 'planet = %s';
        $params[] = sanitize_text_field($args['planet']);
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
        $where[] = '(title LIKE %s OR prediction LIKE %s OR remedies LIKE %s)';
        $wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $wildcard; $params[] = $wildcard; $params[] = $wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    $order_by = in_array($args['order_by'], array('id', 'planet', 'transit_start_date', 'status')) ? $args['order_by'] : 'transit_start_date';
    $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
    
    $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY $order_by $order";
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

function cv_get_transit_horoscope($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $id));
}

function cv_count_transit_horoscopes($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['planet'])) {
        $where[] = 'planet = %s';
        $params[] = sanitize_text_field($args['planet']);
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
        $where[] = '(title LIKE %s OR prediction LIKE %s OR remedies LIKE %s)';
        $wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $wildcard; $params[] = $wildcard; $params[] = $wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
    if (!empty($params)) {
        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }
    return (int) $wpdb->get_var($sql);
}

function cv_insert_transit_horoscope($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    $formats = array(
        'planet'             => '%s',
        'transit_start_date' => '%s',
        'transit_end_date'   => '%s',
        'title'              => '%s',
        'prediction'         => '%s',
        'affected_signs'     => '%s',
        'remedies'           => '%s',
        'status'             => '%s',
        'scheduled_at'       => '%s'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->insert($table, $data, $data_formats);
    return $result ? $wpdb->insert_id : false;
}

function cv_update_transit_horoscope($id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    $formats = array(
        'planet'             => '%s',
        'transit_start_date' => '%s',
        'transit_end_date'   => '%s',
        'title'              => '%s',
        'prediction'         => '%s',
        'affected_signs'     => '%s',
        'remedies'           => '%s',
        'status'             => '%s',
        'scheduled_at'       => '%s'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->update($table, $data, array('id' => (int) $id), $data_formats, array('%d'));
    return $result !== false;
}

function cv_delete_transit_horoscope($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    return $wpdb->delete($table, array('id' => (int) $id), array('%d')) !== false;
}

function cv_delete_bulk_transit_horoscopes($ids) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_transit_horoscope';
    if (empty($ids) || !is_array($ids)) return 0;
    $ids_clean = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_clean), '%d'));
    return (int) $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id IN ($placeholders)", $ids_clean));
}
