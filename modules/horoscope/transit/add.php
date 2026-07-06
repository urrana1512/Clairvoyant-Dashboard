<?php
/**
 * Transit Horoscope Module - Add/Edit Form View
 * 
 * @package Clairvoyant_Core
 * @subpackage Horoscope
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = ($id > 0);
$horo = null;

if ($is_edit) {
    $horo = cv_get_transit_horoscope($id);
    if (!$horo) {
        echo '<div class="cv-alert cv-alert-danger">Record not found.</div>';
        return;
    }
}

$transient_key = 'cv_transit_horo_errors_' . get_current_user_id();
$cached_data = get_transient($transient_key);
$errors = array();
$inputs = array();

if ($cached_data) {
    $errors = $cached_data['errors'];
    $inputs = $cached_data['inputs'];
    delete_transient($transient_key);
}

$planet = isset($inputs['planet']) ? $inputs['planet'] : ($horo ? $horo->planet : '');
$transit_start_date = isset($inputs['transit_start_date']) ? $inputs['transit_start_date'] : ($horo ? $horo->transit_start_date : current_time('Y-m-d'));
$transit_end_date = isset($inputs['transit_end_date']) ? $inputs['transit_end_date'] : ($horo ? $horo->transit_end_date : '');
$title = isset($inputs['title']) ? $inputs['title'] : ($horo ? $horo->title : '');
$prediction = isset($inputs['prediction']) ? $inputs['prediction'] : ($horo ? $horo->prediction : '');
$affected_signs = isset($inputs['affected_signs']) ? $inputs['affected_signs'] : ($horo ? $horo->affected_signs : '');
$remedies = isset($inputs['remedies']) ? $inputs['remedies'] : ($horo ? $horo->remedies : '');
$status = isset($inputs['status']) ? $inputs['status'] : ($horo ? $horo->status : 'publish');
$scheduled_at = isset($inputs['scheduled_at']) ? $inputs['scheduled_at'] : ($horo && !empty($horo->scheduled_at) && $horo->scheduled_at !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($horo->scheduled_at)) : '');

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-transit-manage')); ?>">Transit Horoscope</a> &gt; 
    <span><?php echo $is_edit ? 'Edit' : 'Add New'; ?></span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php echo $is_edit ? __('Edit Transit Horoscope', 'clairvoyant-core') : __('Add New Transit Horoscope', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-transit-manage')); ?>" class="cv-form-button secondary">Back to List</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="cv-alert cv-alert-danger">
        <div><strong>Validation Errors:</strong> Please fix the highlighted fields.</div>
        <span class="cv-alert-close">×</span>
    </div>
<?php endif; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="cv-validated-form">
    <input type="hidden" name="action" value="cv_save_transit_horo">
    <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field('cv_save_transit_horo_action', 'cv_transit_horo_nonce'); ?>

    <div class="cv-form-layout">
        <div>
            <!-- Transit details -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Transit Details</h3>
                
                <div class="cv-form-group">
                    <label class="cv-form-label" for="title">Title <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="title" id="title" value="<?php echo esc_attr($title); ?>" required class="cv-form-input <?php echo isset($errors['title']) ? 'cv-field-error' : ''; ?>">
                    <?php if (isset($errors['title'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['title']); ?></span><?php endif; ?>
                </div>

                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="planet">Planet <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="planet" id="planet" value="<?php echo esc_attr($planet); ?>" required class="cv-form-input <?php echo isset($errors['planet']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. Saturn, Mars">
                        <?php if (isset($errors['planet'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['planet']); ?></span><?php endif; ?>
                    </div>
                    
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="transit_start_date">Transit Start <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="transit_start_date" id="transit_start_date" value="<?php echo esc_attr($transit_start_date); ?>" required class="cv-form-input <?php echo isset($errors['transit_start_date']) ? 'cv-field-error' : ''; ?>">
                        <?php if (isset($errors['transit_start_date'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['transit_start_date']); ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label" for="transit_end_date">Transit End</label>
                    <input type="date" name="transit_end_date" id="transit_end_date" value="<?php echo esc_attr($transit_end_date); ?>" class="cv-form-input <?php echo isset($errors['transit_end_date']) ? 'cv-field-error' : ''; ?>">
                    <?php if (isset($errors['transit_end_date'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['transit_end_date']); ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Prediction Content -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Prediction Text <span style="color:var(--danger)">*</span></h3>
                <div class="cv-form-group">
                    <?php 
                    wp_editor($prediction, 'prediction', array(
                        'textarea_name' => 'prediction',
                        'textarea_rows' => 12,
                        'media_buttons' => false,
                        'tinymce'       => array(
                            'setup' => 'function(ed) { ed.on("change", function(e) { tinymce.triggerSave(); }); }'
                        )
                    ));
                    ?>
                    <?php if (isset($errors['prediction'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['prediction']); ?></span><?php endif; ?>
                </div>
            </div>

            <!-- Remedies -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Remedies (Rich editor)</h3>
                <div class="cv-form-group" style="margin-bottom:0;">
                    <?php 
                    wp_editor($remedies, 'remedies', array(
                        'textarea_name' => 'remedies',
                        'textarea_rows' => 8,
                        'media_buttons' => false,
                        'tinymce'       => array(
                            'setup' => 'function(ed) { ed.on("change", function(e) { tinymce.triggerSave(); }); }'
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div>
            <!-- Affected Signs -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Impacted Signs</h3>
                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label" for="affected_signs">Affected Signs (Max 500 characters)</label>
                    <textarea name="affected_signs" id="affected_signs" class="cv-form-textarea" placeholder="e.g. Aries, Cancer, Scorpio"><?php echo esc_textarea($affected_signs); ?></textarea>
                </div>
            </div>

            <!-- Publish Settings -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Publish Controls</h3>
                <div class="cv-form-group">
                    <label class="cv-form-label" for="status">Status</label>
                    <select name="status" id="status" class="cv-form-select">
                        <option value="publish" <?php selected($status, 'publish'); ?>>Published</option>
                        <option value="draft" <?php selected($status, 'draft'); ?>>Draft</option>
                    </select>
                </div>
                <div class="cv-form-group">
                    <label class="cv-form-label" for="scheduled_at">Schedule Live Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="<?php echo esc_attr($scheduled_at); ?>" class="cv-form-input">
                    <span class="description" style="font-size:11px; color:#888; display:block; margin-top:4px;">Optional. If set, this content will only go live on the frontend at the scheduled time.</span>
                </div>
                <div class="cv-btn-container">
                    <button type="submit" class="cv-form-button"><?php echo $is_edit ? 'Update Transit' : 'Publish Transit'; ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-transit-manage')); ?>" class="cv-form-button secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
