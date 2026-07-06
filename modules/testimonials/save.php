<?php
/**
 * Testimonials Module - Save Handler
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function cv_save_testimonial_handler() {
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_testimonial_action', 'cv_testimonial_nonce');

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    $scheduled_at = !empty($_POST['scheduled_at']) ? sanitize_text_field($_POST['scheduled_at']) : '';
    $scheduled_val = null;
    if (!empty($scheduled_at)) {
        $ts = strtotime($scheduled_at);
        if ($ts !== false) {
            $scheduled_val = date('Y-m-d H:i:s', $ts);
        }
    }
    
    $fields = array(
        'client_name'  => sanitize_text_field($_POST['client_name']),
        'client_image' => esc_url_raw($_POST['client_image']),
        'service'      => sanitize_text_field($_POST['service']),
        'rating'       => isset($_POST['rating']) ? (int) $_POST['rating'] : 0,
        'review'       => wp_kses_post($_POST['review']),
        'location'     => sanitize_text_field($_POST['location']),
        'status'       => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at' => $scheduled_val
    );

    $validation = cv_validate_testimonial($fields);
    
    if (!$validation['valid']) {
        $error_key = 'cv_testimonial_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300);

        $redirect_url = admin_url('admin.php?page=cv-testimonials-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if ($id > 0) {
        $saved = cv_update_testimonial($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated Testimonial of client %s', $fields['client_name']));
    } else {
        $saved = cv_insert_testimonial($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created Testimonial of client %s', $fields['client_name']));
    }

    if ($saved !== false) {
        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-testimonials-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_testimonial', 'cv_save_testimonial_handler');
