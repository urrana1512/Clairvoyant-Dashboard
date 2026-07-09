<?php
/**
 * 24-48 Hrs Prediction Module - Save Handler
 * 
 * Processes form submissions for creating and updating 24-48 Hrs predictions
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle saving 24-48 Hrs prediction record
 */
function cv_save_prediction_24h_handler() {
    // 1. Verify capability and nonce
    cv_check_admin_referer('manage_options');
    cv_verify_request_nonce('cv_save_prediction_24h_action', 'cv_prediction_24h_nonce');

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    
    $scheduled_at = !empty($_POST['scheduled_at']) ? sanitize_text_field($_POST['scheduled_at']) : '';
    $scheduled_val = null;
    if (!empty($scheduled_at)) {
        $ts = strtotime($scheduled_at);
        if ($ts !== false) {
            $scheduled_val = date('Y-m-d H:i:s', $ts);
        }
    }
    
    // 2. Extract & Sanitize fields
    $fields = array(
        'date'          => sanitize_text_field($_POST['date']),
        'element'       => sanitize_key($_POST['element']),
        'prediction'    => wp_kses_post($_POST['prediction']), // Keep rich text formatting
        'suryoday'      => isset($_POST['suryoday']) ? sanitize_text_field($_POST['suryoday']) : '',
        'suryast'       => isset($_POST['suryast']) ? sanitize_text_field($_POST['suryast']) : '',
        'good_time'     => isset($_POST['good_time']) ? sanitize_text_field($_POST['good_time']) : '',
        'hindu_muhurat' => isset($_POST['hindu_muhurat']) ? sanitize_text_field($_POST['hindu_muhurat']) : '',
        'rahu_kaal'     => isset($_POST['rahu_kaal']) ? sanitize_text_field($_POST['rahu_kaal']) : '',
        'status'        => in_array($_POST['status'], array('publish', 'draft')) ? $_POST['status'] : 'draft',
        'scheduled_at'  => $scheduled_val
    );

    // 3. Validate
    $validation = cv_validate_prediction_24_48($fields);
    
    if (!$validation['valid']) {
        // Validation failed: cache values & errors in transient and redirect back
        $error_key = 'cv_prediction_24h_errors_' . get_current_user_id();
        set_transient($error_key, array(
            'errors' => $validation['errors'],
            'inputs' => $_POST
        ), 300); // 5 mins cache

        $redirect_url = admin_url('admin.php?page=cv-prediction-24h-add');
        if ($id > 0) {
            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id), $redirect_url);
        }
        $redirect_url = add_query_arg('message', 'validation_failed', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $current_user_id = get_current_user_id();
    global $wpdb;
    $table = $wpdb->prefix . 'cv_prediction_24_48';

    // 4. Save to Database
    if ($id > 0) {
        // Edit flow
        $fields['updated_by'] = $current_user_id;
        
        // Check if unique key (date, element) isn't violated by another record
        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE date = %s AND element = %s AND id != %d",
            $fields['date'], $fields['element'], $id
        ));

        if ($dup) {
            $error_key = 'cv_prediction_24h_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('element' => __('A prediction for this Element Sign Group on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg(array('action' => 'edit', 'id' => $id, 'message' => 'validation_failed'), admin_url('admin.php?page=cv-prediction-24h-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_update_prediction_24_48($id, $fields);
        $action_label = 'updated';
        cv_log_activity(sprintf('Updated 24-48 Hrs Prediction for %s on %s', ucfirst($fields['element']), $fields['date']));
    } else {
        // Add flow
        $fields['created_by'] = $current_user_id;
        $fields['updated_by'] = $current_user_id;

        $dup = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE date = %s AND element = %s",
            $fields['date'], $fields['element']
        ));

        if ($dup) {
            $error_key = 'cv_prediction_24h_errors_' . get_current_user_id();
            set_transient($error_key, array(
                'errors' => array('element' => __('A prediction for this Element Sign Group on this date already exists.', 'clairvoyant-core')),
                'inputs' => $_POST
            ), 300);

            $redirect_url = add_query_arg('message', 'validation_failed', admin_url('admin.php?page=cv-prediction-24h-add'));
            wp_safe_redirect($redirect_url);
            exit;
        }

        $saved = cv_insert_prediction_24_48($fields);
        $action_label = 'created';
        cv_log_activity(sprintf('Created 24-48 Hrs Prediction for %s on %s', ucfirst($fields['element']), $fields['date']));
    }

    if ($saved !== false) {
        // Synchronize daily auspicious times across all entries for this date
        $wpdb->update(
            $table,
            array(
                'suryoday'      => $fields['suryoday'],
                'suryast'       => $fields['suryast'],
                'good_time'     => $fields['good_time'],
                'hindu_muhurat' => $fields['hindu_muhurat'],
                'rahu_kaal'     => $fields['rahu_kaal'],
            ),
            array('date' => $fields['date']),
            array('%s', '%s', '%s', '%s', '%s'),
            array('%s')
        );

        cv_clear_shortcode_transients();
        wp_safe_redirect(add_query_arg('message', $action_label, admin_url('admin.php?page=cv-prediction-24h-manage')));
        exit;
    } else {
        wp_die(__('An error occurred while saving the record. Please try again.', 'clairvoyant-core'));
    }
}
add_action('admin_post_cv_save_prediction_24h', 'cv_save_prediction_24h_handler');
