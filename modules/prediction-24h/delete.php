<?php
/**
 * 24-48 Hrs Prediction Module - Delete Handler
 * 
 * Processes single and bulk deletion requests for 24-48 Hrs prediction records
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles single delete trigger from table actions
 */
function cv_delete_prediction_24h_handler() {
    cv_check_admin_referer('manage_options');
    
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    cv_verify_request_nonce('cv_delete_prediction_24h_' . $id, 'nonce');
    
    $prediction = cv_get_prediction_24_48($id);
    if (!$prediction) {
        wp_die(__('Record not found.', 'clairvoyant-core'));
    }
    
    $deleted = cv_delete_prediction_24_48($id);
    
    if ($deleted) {
        cv_log_activity(sprintf('Deleted 24-48 Hrs Prediction for %s on %s', ucfirst($prediction->element), $prediction->date));
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=cv-prediction-24h-manage')));
        exit;
    } else {
        wp_die(__('Failed to delete record. Please try again.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_delete_prediction_24h', 'cv_delete_prediction_24h_handler');

/**
 * Handles bulk actions form submission
 */
function cv_bulk_delete_prediction_24h_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_prediction_24h_bulk_action', 'cv_prediction_24h_bulk_nonce');
    
    $action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
    $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_safe_redirect(admin_url('admin.php?page=cv-prediction-24h-manage'));
        exit;
    }
    
    if ($action === 'delete') {
        $count = cv_delete_bulk_predictions_24_48($ids);
        if ($count > 0) {
            cv_log_activity(sprintf('Bulk deleted %d 24-48 Hrs prediction records', $count));
            cv_clear_shortcode_transients();
            wp_safe_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=cv-prediction-24h-manage')));
            exit;
        }
    }
    
    wp_safe_redirect(admin_url('admin.php?page=cv-prediction-24h-manage'));
    exit;
}
add_action('admin_post_cv_bulk_prediction_24h', 'cv_bulk_delete_prediction_24h_handler');
