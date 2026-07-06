<?php
/**
 * Testimonials Module - Add/Edit Form View
 * 
 * @package Clairvoyant_Core
 * @subpackage Testimonials
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = ($id > 0);
$testimonial = null;

if ($is_edit) {
    $testimonial = cv_get_testimonial($id);
    if (!$testimonial) {
        echo '<div class="cv-alert cv-alert-danger">Record not found.</div>';
        return;
    }
}

$transient_key = 'cv_testimonial_errors_' . get_current_user_id();
$cached_data = get_transient($transient_key);
$errors = array();
$inputs = array();

if ($cached_data) {
    $errors = $cached_data['errors'];
    $inputs = $cached_data['inputs'];
    delete_transient($transient_key);
}

$client_name = isset($inputs['client_name']) ? $inputs['client_name'] : ($testimonial ? $testimonial->client_name : '');
$client_image = isset($inputs['client_image']) ? $inputs['client_image'] : ($testimonial ? $testimonial->client_image : '');
$service = isset($inputs['service']) ? $inputs['service'] : ($testimonial ? $testimonial->service : '');
$rating = isset($inputs['rating']) ? (int) $inputs['rating'] : ($testimonial ? (int) $testimonial->rating : 5);
$review = isset($inputs['review']) ? $inputs['review'] : ($testimonial ? $testimonial->review : '');
$location = isset($inputs['location']) ? $inputs['location'] : ($testimonial ? $testimonial->location : '');
$status = isset($inputs['status']) ? $inputs['status'] : ($testimonial ? $testimonial->status : 'publish');
$scheduled_at = isset($inputs['scheduled_at']) ? $inputs['scheduled_at'] : ($testimonial && !empty($testimonial->scheduled_at) && $testimonial->scheduled_at !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($testimonial->scheduled_at)) : '');

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-manage')); ?>">Testimonials</a> &gt; 
    <span><?php echo $is_edit ? 'Edit' : 'Add New'; ?></span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php echo $is_edit ? __('Edit Testimonial', 'clairvoyant-core') : __('Add New Testimonial', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-manage')); ?>" class="cv-form-button secondary">Back to List</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="cv-alert cv-alert-danger">
        <div><strong>Validation Errors:</strong> Please fix the highlighted fields.</div>
        <span class="cv-alert-close">×</span>
    </div>
<?php endif; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="cv-validated-form">
    <input type="hidden" name="action" value="cv_save_testimonial">
    <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field('cv_save_testimonial_action', 'cv_testimonial_nonce'); ?>

    <div class="cv-form-layout">
        <div>
            <!-- Review Content -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Review Content</h3>
                
                <div class="cv-form-group">
                    <label class="cv-form-label" for="service">Service Reviewed</label>
                    <input type="text" name="service" id="service" value="<?php echo esc_attr($service); ?>" class="cv-form-input <?php echo isset($errors['service']) ? 'cv-field-error' : ''; ?>" placeholder="e.g. Tarot Card Reading, Birth Chart">
                    <?php if (isset($errors['service'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['service']); ?></span><?php endif; ?>
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="review">Review <span style="color:var(--danger)">*</span></label>
                    <?php 
                    wp_editor($review, 'review', array(
                        'textarea_name' => 'review',
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'tinymce'       => array(
                            'setup' => 'function(ed) { ed.on("change", function(e) { tinymce.triggerSave(); }); }'
                        )
                    ));
                    ?>
                    <?php if (isset($errors['review'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['review']); ?></span><?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <!-- Client Info Panel -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Client Details</h3>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="client_name">Client Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="client_name" id="client_name" value="<?php echo esc_attr($client_name); ?>" required class="cv-form-input <?php echo isset($errors['client_name']) ? 'cv-field-error' : ''; ?>">
                    <?php if (isset($errors['client_name'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['client_name']); ?></span><?php endif; ?>
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="location">Location</label>
                    <input type="text" name="location" id="location" value="<?php echo esc_attr($location); ?>" class="cv-form-input" placeholder="e.g. New York, USA">
                </div>

                <!-- Client Image Upload -->
                <div class="cv-form-group">
                    <label class="cv-form-label">Client Image</label>
                    <div class="cv-upload-container">
                        <img src="<?php echo !empty($client_image) ? esc_url($client_image) : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png'; ?>" id="cv-avatar-preview" class="cv-preview-image" alt="Client Avatar">
                        <div class="cv-upload-details">
                            <input type="hidden" name="client_image" id="client_image_url" value="<?php echo esc_url($client_image); ?>">
                            <button type="button" class="cv-form-button secondary cv-upload-trigger" data-target="client_image_url" data-preview="cv-avatar-preview">Upload Photo</button>
                        </div>
                    </div>
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label">Rating</label>
                    <div class="cv-rating-selector">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <input type="radio" id="star-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php checked($rating, $i); ?>>
                            <label for="star-<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
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
                    <button type="submit" class="cv-form-button"><?php echo $is_edit ? 'Update Review' : 'Publish Review'; ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-testimonials-manage')); ?>" class="cv-form-button secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
