<?php
/**
 * Weekly Horoscope Module - Delete Handler
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_delete_weekly_horo_handler() {
    cv_check_admin_referer('manage_options');
    
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    cv_verify_request_nonce('cv_delete_weekly_horo_' . $id, 'nonce');
    
    $horo = cv_get_weekly_horoscope($id);
    if (!$horo) {
        wp_die(__('Record not found.', 'clairvoyant-core'));
    }
    
    $deleted = cv_delete_weekly_horoscope($id);
    
    if ($deleted) {
        cv_log_activity(sprintf('Deleted Weekly Horoscope for %s for week %s', ucfirst($horo->zodiac_sign), $horo->week_start));
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=cv-horoscope-weekly-manage')));
        exit;
    } else {
        wp_die(__('Failed to delete record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_delete_weekly_horo', 'cv_delete_weekly_horo_handler');

function cv_bulk_delete_weekly_horo_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_weekly_horo_bulk_action', 'cv_weekly_horo_bulk_nonce');
    
    $action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
    $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_safe_redirect(admin_url('admin.php?page=cv-horoscope-weekly-manage'));
        exit;
    }
    
    if ($action === 'delete') {
        $count = cv_delete_bulk_weekly_horoscopes($ids);
        if ($count > 0) {
            cv_log_activity(sprintf('Bulk deleted %d Weekly Horoscope records', $count));
            cv_clear_shortcode_transients();
            wp_safe_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=cv-horoscope-weekly-manage')));
            exit;
        }
    }
    
    wp_safe_redirect(admin_url('admin.php?page=cv-horoscope-weekly-manage'));
    exit;
}
add_action('admin_post_cv_bulk_weekly_horo', 'cv_bulk_delete_weekly_horo_handler');
