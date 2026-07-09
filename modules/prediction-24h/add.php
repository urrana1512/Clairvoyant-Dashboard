<?php
/**
 * 24-48 Hrs Prediction Module - Add/Edit Form View
 * 
 * Renders the form to add or edit 24-48 Hrs prediction records
 * 
 * @package Clairvoyant_Core
 * @subpackage Prediction_24h
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Determine if we are editing
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = ($id > 0);
$prediction_record = null;

if ($is_edit) {
    $prediction_record = cv_get_prediction_24_48($id);
    if (!$prediction_record) {
        echo '<div class="cv-alert cv-alert-danger">Record not found.</div>';
        return;
    }
}

// 2. Fetch any cached transient data from validation failure
$transient_key = 'cv_prediction_24h_errors_' . get_current_user_id();
$cached_data = get_transient($transient_key);
$errors = array();
$inputs = array();

if ($cached_data) {
    $errors = $cached_data['errors'];
    $inputs = $cached_data['inputs'];
    delete_transient($transient_key); // Clear immediately
}

// 3. Resolve field values (pre-fill priorities: 1. inputs from error, 2. DB value, 3. empty default)
$date = isset($inputs['date']) ? $inputs['date'] : ($prediction_record ? $prediction_record->date : current_time('Y-m-d'));
$element = isset($inputs['element']) ? $inputs['element'] : ($prediction_record ? $prediction_record->element : '');
$prediction = isset($inputs['prediction']) ? $inputs['prediction'] : ($prediction_record ? $prediction_record->prediction : '');
$status = isset($inputs['status']) ? $inputs['status'] : ($prediction_record ? $prediction_record->status : 'publish');
$scheduled_at = isset($inputs['scheduled_at']) ? $inputs['scheduled_at'] : ($prediction_record && !empty($prediction_record->scheduled_at) && $prediction_record->scheduled_at !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($prediction_record->scheduled_at)) : '');

// Auspicious times fields pre-filling
$suryoday = '';
$suryast = '';
$good_time = '';
$hindu_muhurat = '';
$rahu_kaal = '';

if ($is_edit && $prediction_record) {
    $suryoday = $prediction_record->suryoday;
    $suryast = $prediction_record->suryast;
    $good_time = $prediction_record->good_time;
    $hindu_muhurat = $prediction_record->hindu_muhurat;
    $rahu_kaal = $prediction_record->rahu_kaal;
} else {
    global $wpdb;
    $existing_date_record = $wpdb->get_row($wpdb->prepare(
        "SELECT suryoday, suryast, good_time, hindu_muhurat, rahu_kaal FROM {$wpdb->prefix}cv_prediction_24_48 WHERE date = %s AND suryoday != '' LIMIT 1",
        $date
    ));
    if ($existing_date_record) {
        $suryoday = $existing_date_record->suryoday;
        $suryast = $existing_date_record->suryast;
        $good_time = $existing_date_record->good_time;
        $hindu_muhurat = $existing_date_record->hindu_muhurat;
        $rahu_kaal = $existing_date_record->rahu_kaal;
    }
}

$suryoday = isset($inputs['suryoday']) ? $inputs['suryoday'] : $suryoday;
$suryast = isset($inputs['suryast']) ? $inputs['suryast'] : $suryast;
$good_time = isset($inputs['good_time']) ? $inputs['good_time'] : $good_time;
$hindu_muhurat = isset($inputs['hindu_muhurat']) ? $inputs['hindu_muhurat'] : $hindu_muhurat;
$rahu_kaal = isset($inputs['rahu_kaal']) ? $inputs['rahu_kaal'] : $rahu_kaal;

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-manage')); ?>">24-48 Hrs Prediction</a> &gt; 
    <span><?php echo $is_edit ? 'Edit Record' : 'Add New'; ?></span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php echo $is_edit ? __('Edit 24-48 Hrs Prediction', 'clairvoyant-core') : __('Add New 24-48 Hrs Prediction', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-manage')); ?>" class="cv-form-button secondary">Back to List</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="cv-alert cv-alert-danger">
        <div>
            <strong>Validation Errors:</strong> Please fix the errors highlighted below and re-submit the form.
        </div>
        <span class="cv-alert-close">×</span>
    </div>
<?php endif; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="cv-validated-form">
    <!-- Action hooks and fields -->
    <input type="hidden" name="action" value="cv_save_prediction_24h">
    <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field('cv_save_prediction_24h_action', 'cv_prediction_24h_nonce'); ?>

    <div class="cv-form-layout">
        <!-- Left Panel: Core Details -->
        <div>
            <!-- Box 1: Core Details -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">General Info</h3>
                
                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="date">Date <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="date" id="date" value="<?php echo esc_attr($date); ?>" required class="cv-form-input <?php echo isset($errors['date']) ? 'cv-field-error' : ''; ?>">
                        <?php if (isset($errors['date'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['date']); ?></span><?php endif; ?>
                    </div>

                    <div class="cv-form-group">
                        <label class="cv-form-label" for="element">Element Sign Group <span style="color:var(--danger)">*</span></label>
                        <select name="element" id="element" required class="cv-form-select <?php echo isset($errors['element']) ? 'cv-field-error' : ''; ?>">
                            <option value="">-- Select Element --</option>
                            <?php foreach (cv_get_element_list() as $key => $el_info) : ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($element, $key); ?>>
                                    <?php echo esc_html($el_info['icon'] . ' ' . $el_info['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['element'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['element']); ?></span><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Box 1.5: Auspicious Times & Panchang -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Auspicious Times & Panchang (Common for this Date)</h3>
                
                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="suryoday">Suryoday (Sunrise)</label>
                        <input type="text" name="suryoday" id="suryoday" value="<?php echo esc_attr($suryoday); ?>" class="cv-form-input <?php echo isset($errors['suryoday']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. 05:42 AM">
                        <?php if (isset($errors['suryoday'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['suryoday']); ?></span><?php endif; ?>
                    </div>

                    <div class="cv-form-group">
                        <label class="cv-form-label" for="suryast">Suryast (Sunset)</label>
                        <input type="text" name="suryast" id="suryast" value="<?php echo esc_attr($suryast); ?>" class="cv-form-input <?php echo isset($errors['suryast']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. 07:12 PM">
                        <?php if (isset($errors['suryast'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['suryast']); ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="cv-form-group" style="margin-top: 15px;">
                    <label class="cv-form-label" for="good_time">Good Time (Shubh Samay)</label>
                    <input type="text" name="good_time" id="good_time" value="<?php echo esc_attr($good_time); ?>" class="cv-form-input <?php echo isset($errors['good_time']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. 11:45 AM - 12:35 PM">
                    <?php if (isset($errors['good_time'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['good_time']); ?></span><?php endif; ?>
                </div>

                <div class="cv-form-grid-2" style="margin-top: 15px;">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="hindu_muhurat">Hindu Muhurat</label>
                        <input type="text" name="hindu_muhurat" id="hindu_muhurat" value="<?php echo esc_attr($hindu_muhurat); ?>" class="cv-form-input <?php echo isset($errors['hindu_muhurat']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. Abhijit: 11:50 AM, Amrit: 04:20 PM">
                        <?php if (isset($errors['hindu_muhurat'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['hindu_muhurat']); ?></span><?php endif; ?>
                    </div>

                    <div class="cv-form-group">
                        <label class="cv-form-label" for="rahu_kaal">Rahu Kaal</label>
                        <input type="text" name="rahu_kaal" id="rahu_kaal" value="<?php echo esc_attr($rahu_kaal); ?>" class="cv-form-input <?php echo isset($errors['rahu_kaal']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. 03:00 PM - 04:30 PM">
                        <?php if (isset($errors['rahu_kaal'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['rahu_kaal']); ?></span><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Box 2: Prediction text -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Prediction Text <span style="color:var(--danger)">*</span></h3>
                <div class="cv-form-group">
                    <?php 
                    wp_editor($prediction, 'prediction', array(
                        'textarea_name' => 'prediction',
                        'textarea_rows' => 12,
                        'media_buttons' => false,
                        'tinymce'       => array(
                            'setup' => 'function(ed) {
                                ed.on("change", function(e) {
                                    tinymce.triggerSave();
                                });
                            }'
                        )
                    ));
                    ?>
                    <?php if (isset($errors['prediction'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['prediction']); ?></span><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Panel: Sidebar Actions / Settings -->
        <div>
            <!-- Box: Status & Publishing Options -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Publishing Settings</h3>
                
                <div class="cv-form-group">
                    <label class="cv-form-label" for="status">Status</label>
                    <select name="status" id="status" class="cv-form-select">
                        <option value="publish" <?php selected($status, 'publish'); ?>>Published</option>
                        <option value="draft" <?php selected($status, 'draft'); ?>>Draft</option>
                    </select>
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="scheduled_at">Schedule Date / Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="<?php echo esc_attr($scheduled_at); ?>" class="cv-form-input">
                    <p class="description" style="margin-top: 6px; font-size: 11px;">Leave blank to publish instantly. Otherwise, the prediction will be hidden on the frontend until the scheduled time.</p>
                </div>
            </div>

            <!-- Form Buttons panel -->
            <div class="cv-form-actions-panel">
                <button type="submit" class="cv-form-button"><?php echo $is_edit ? 'Update Prediction' : 'Save Prediction'; ?></button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cv-prediction-24h-manage')); ?>" class="cv-form-button secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
