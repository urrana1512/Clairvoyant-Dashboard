<?php
/**
 * Daily Rashi Module - Delete Handler
 * 
 * Processes single and bulk deletion requests for Daily Rashi records
 * 
 * @package Clairvoyant_Core
 * @subpackage Daily_Rashi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles single delete trigger from table actions
 */
function cv_delete_daily_rashi_handler() {
    cv_check_admin_referer('manage_options');
    
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    cv_verify_request_nonce('cv_delete_rashi_' . $id, 'nonce');
    
    $rashi = cv_get_daily_rashi($id);
    if (!$rashi) {
        wp_die(__('Record not found.', 'clairvoyant-core'));
    }
    
    $deleted = cv_delete_daily_rashi($id);
    
    if ($deleted) {
        cv_log_activity(sprintf('Deleted Daily Rashi for %s on %s', ucfirst($rashi->zodiac_sign), $rashi->date));
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=cv-daily-rashi-manage')));
        exit;
    } else {
        wp_die(__('Failed to delete record. Please try again.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_delete_daily_rashi', 'cv_delete_daily_rashi_handler');

/**
 * Handles bulk actions form submission
 */
function cv_bulk_delete_daily_rashi_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_rashi_bulk_action', 'cv_rashi_bulk_nonce');
    
    $action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
    $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_safe_redirect(admin_url('admin.php?page=cv-daily-rashi-manage'));
        exit;
    }
    
    if ($action === 'delete') {
        $count = cv_delete_bulk_daily_rashis($ids);
        if ($count > 0) {
            cv_log_activity(sprintf('Bulk deleted %d Daily Rashi records', $count));
            cv_clear_shortcode_transients();
            wp_safe_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=cv-daily-rashi-manage')));
            exit;
        }
    }
    
    wp_safe_redirect(admin_url('admin.php?page=cv-daily-rashi-manage'));
    exit;
}
add_action('admin_post_cv_bulk_daily_rashi', 'cv_bulk_delete_daily_rashi_handler');
