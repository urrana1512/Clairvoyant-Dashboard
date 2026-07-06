<?php
/**
 * Weekly Horoscope Module - Add/Edit Form View
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
    $horo = cv_get_weekly_horoscope($id);
    if (!$horo) {
        echo '<div class="cv-alert cv-alert-danger">Record not found.</div>';
        return;
    }
}

$transient_key = 'cv_weekly_horo_errors_' . get_current_user_id();
$cached_data = get_transient($transient_key);
$errors = array();
$inputs = array();

if ($cached_data) {
    $errors = $cached_data['errors'];
    $inputs = $cached_data['inputs'];
    delete_transient($transient_key);
}

// Defaults: start of week (Monday) and end of week (Sunday)
$default_start = date('Y-m-d', strtotime('monday this week'));
$default_end = date('Y-m-d', strtotime('sunday this week'));

$week_start = isset($inputs['week_start']) ? $inputs['week_start'] : ($horo ? $horo->week_start : $default_start);
$week_end = isset($inputs['week_end']) ? $inputs['week_end'] : ($horo ? $horo->week_end : $default_end);
$zodiac_sign = isset($inputs['zodiac_sign']) ? $inputs['zodiac_sign'] : ($horo ? $horo->zodiac_sign : '');
$prediction = isset($inputs['prediction']) ? $inputs['prediction'] : ($horo ? $horo->prediction : '');
$career = isset($inputs['career']) ? $inputs['career'] : ($horo ? $horo->career : '');
$love = isset($inputs['love']) ? $inputs['love'] : ($horo ? $horo->love : '');
$health = isset($inputs['health']) ? $inputs['health'] : ($horo ? $horo->health : '');
$money = isset($inputs['money']) ? $inputs['money'] : ($horo ? $horo->money : '');
$overall_rating = isset($inputs['overall_rating']) ? (int) $inputs['overall_rating'] : ($horo ? (int) $horo->overall_rating : 3);
$status = isset($inputs['status']) ? $inputs['status'] : ($horo ? $horo->status : 'publish');
$scheduled_at = isset($inputs['scheduled_at']) ? $inputs['scheduled_at'] : ($horo && !empty($horo->scheduled_at) && $horo->scheduled_at !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($horo->scheduled_at)) : '');

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-weekly-manage')); ?>">Weekly Horoscope</a> &gt; 
    <span><?php echo $is_edit ? 'Edit' : 'Add New'; ?></span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php echo $is_edit ? __('Edit Weekly Horoscope', 'clairvoyant-core') : __('Add New Weekly Horoscope', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-weekly-manage')); ?>" class="cv-form-button secondary">Back to List</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="cv-alert cv-alert-danger">
        <div><strong>Validation Errors:</strong> Please fix the highlighted fields.</div>
        <span class="cv-alert-close">×</span>
    </div>
<?php endif; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="cv-validated-form">
    <input type="hidden" name="action" value="cv_save_weekly_horo">
    <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field('cv_save_weekly_horo_action', 'cv_weekly_horo_nonce'); ?>

    <div class="cv-form-layout">
        <div>
            <!-- Week Period Panel -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Week Period & Zodiac</h3>
                
                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="week_start">Week Start <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="week_start" id="week_start" value="<?php echo esc_attr($week_start); ?>" required class="cv-form-input <?php echo isset($errors['week_start']) ? 'cv-field-error' : ''; ?>">
                        <?php if (isset($errors['week_start'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['week_start']); ?></span><?php endif; ?>
                    </div>
                    
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="week_end">Week End <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="week_end" id="week_end" value="<?php echo esc_attr($week_end); ?>" required class="cv-form-input <?php echo isset($errors['week_end']) ? 'cv-field-error' : ''; ?>">
                        <?php if (isset($errors['week_end'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['week_end']); ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label" for="zodiac_sign">Zodiac Sign <span style="color:var(--danger)">*</span></label>
                    <select name="zodiac_sign" id="zodiac_sign" required class="cv-form-select <?php echo isset($errors['zodiac_sign']) ? 'cv-field-error' : ''; ?>">
                        <option value="">-- Select Sign --</option>
                        <?php foreach (cv_get_zodiac_list() as $key => $sign) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($zodiac_sign, $key); ?>>
                                <?php echo esc_html($sign['icon'] . ' ' . $sign['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['zodiac_sign'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['zodiac_sign']); ?></span><?php endif; ?>
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

            <!-- Life Areas Highlights -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Life Areas Highlights (Max 500 characters)</h3>
                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="career">Career</label>
                        <textarea name="career" id="career" class="cv-form-textarea <?php echo isset($errors['career']) ? 'cv-field-error' : ''; ?>"><?php echo esc_textarea($career); ?></textarea>
                        <?php if (isset($errors['career'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['career']); ?></span><?php endif; ?>
                    </div>
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="love">Love</label>
                        <textarea name="love" id="love" class="cv-form-textarea <?php echo isset($errors['love']) ? 'cv-field-error' : ''; ?>"><?php echo esc_textarea($love); ?></textarea>
                        <?php if (isset($errors['love'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['love']); ?></span><?php endif; ?>
                    </div>
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="health">Health</label>
                        <textarea name="health" id="health" class="cv-form-textarea <?php echo isset($errors['health']) ? 'cv-field-error' : ''; ?>"><?php echo esc_textarea($health); ?></textarea>
                        <?php if (isset($errors['health'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['health']); ?></span><?php endif; ?>
                    </div>
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="money">Money</label>
                        <textarea name="money" id="money" class="cv-form-textarea <?php echo isset($errors['money']) ? 'cv-field-error' : ''; ?>"><?php echo esc_textarea($money); ?></textarea>
                        <?php if (isset($errors['money'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['money']); ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- Ratings Panel -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Overall Week Rating</h3>
                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label">Overall Rating</label>
                    <div class="cv-rating-selector">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <input type="radio" id="star-<?php echo $i; ?>" name="overall_rating" value="<?php echo $i; ?>" <?php checked($overall_rating, $i); ?>>
                            <label for="star-<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($errors['overall_rating'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['overall_rating']); ?></span><?php endif; ?>
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
                    <button type="submit" class="cv-form-button"><?php echo $is_edit ? 'Update Weekly' : 'Publish Weekly'; ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-weekly-manage')); ?>" class="cv-form-button secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
