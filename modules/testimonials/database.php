<?php
/**
 * Testimonials Module Database Layer
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_get_testimonials($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    
    $defaults = array(
        'status'            => '',
        'search'            => '',
        'limit'             => 10,
        'offset'            => 0,
        'order_by'          => 'created_at',
        'order'             => 'DESC',
        'include_scheduled' => is_admin()
    );
    
    $args = wp_parse_args($args, $defaults);
    $where = array('1=1');
    $params = array();
    
    if (!empty($args['status'])) {
        $where[] = 'status = %s';
        $params[] = sanitize_text_field($args['status']);
    }
    if (!$args['include_scheduled']) {
        $where[] = '(scheduled_at IS NULL OR scheduled_at <= %s)';
        $params[] = current_time('mysql');
    }
    if (!empty($args['search'])) {
        $where[] = '(client_name LIKE %s OR service LIKE %s OR review LIKE %s)';
        $wildcard = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
        $params[] = $wildcard; $params[] = $wildcard; $params[] = $wildcard;
    }
    
    $where_sql = implode(' AND ', $where);
    $order_by = in_array($args['order_by'], array('id', 'client_name', 'rating', 'status', 'created_at')) ? $args['order_by'] : 'created_at';
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

function cv_get_testimonial($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", (int) $id));
}

function cv_count_testimonials($args = array()) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    $where = array('1=1');
    $params = array();
    
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
        $where[] = '(client_name LIKE %s OR service LIKE %s OR review LIKE %s)';
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

function cv_insert_testimonial($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    $formats = array(
        'client_name'  => '%s',
        'client_image' => '%s',
        'service'      => '%s',
        'rating'       => '%d',
        'review'       => '%s',
        'location'     => '%s',
        'status'       => '%s',
        'scheduled_at' => '%s'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->insert($table, $data, $data_formats);
    return $result ? $wpdb->insert_id : false;
}

function cv_update_testimonial($id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    $formats = array(
        'client_name'  => '%s',
        'client_image' => '%s',
        'service'      => '%s',
        'rating'       => '%d',
        'review'       => '%s',
        'location'     => '%s',
        'status'       => '%s',
        'scheduled_at' => '%s'
    );
    $data_formats = array();
    foreach ($data as $key => $val) {
        if (isset($formats[$key])) $data_formats[] = $formats[$key];
    }
    $result = $wpdb->update($table, $data, array('id' => (int) $id), $data_formats, array('%d'));
    return $result !== false;
}

function cv_delete_testimonial($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    return $wpdb->delete($table, array('id' => (int) $id), array('%d')) !== false;
}

function cv_delete_bulk_testimonials($ids) {
    global $wpdb;
    $table = $wpdb->prefix . 'cv_testimonials';
    if (empty($ids) || !is_array($ids)) return 0;
    $ids_clean = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_clean), '%d'));
    return (int) $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id IN ($placeholders)", $ids_clean));
}
