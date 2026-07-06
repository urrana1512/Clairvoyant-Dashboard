<?php
/**
 * Testimonials Module - Delete Handler
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_delete_testimonial_handler() {
    cv_check_admin_referer('manage_options');
    
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    cv_verify_request_nonce('cv_delete_testimonial_' . $id, 'nonce');
    
    $testimonial = cv_get_testimonial($id);
    if (!$testimonial) {
        wp_die(__('Record not found.', 'clairvoyant-core'));
    }
    
    $deleted = cv_delete_testimonial($id);
    
    if ($deleted) {
        cv_log_activity(sprintf('Deleted Testimonial of client %s', $testimonial->client_name));
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', 'deleted', admin_url('admin.php?page=cv-testimonials-manage')));
        exit;
    } else {
        wp_die(__('Failed to delete record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_delete_testimonial', 'cv_delete_testimonial_handler');

function cv_bulk_delete_testimonial_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_testimonial_bulk_action', 'cv_testimonial_bulk_nonce');
    
    $action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
    $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_safe_redirect(admin_url('admin.php?page=cv-testimonials-manage'));
        exit;
    }
    
    if ($action === 'delete') {
        $count = cv_delete_bulk_testimonials($ids);
        if ($count > 0) {
            cv_log_activity(sprintf('Bulk deleted %d Testimonial records', $count));
            cv_clear_shortcode_transients();
            wp_safe_redirect(add_query_arg('message', 'bulk_deleted', admin_url('admin.php?page=cv-testimonials-manage')));
            exit;
        }
    }
    
    wp_safe_redirect(admin_url('admin.php?page=cv-testimonials-manage'));
    exit;
}
add_action('admin_post_cv_bulk_testimonial', 'cv_bulk_delete_testimonial_handler');
