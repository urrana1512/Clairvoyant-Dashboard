<?php
/**
 * Daily Horoscope Module - Add/Edit Form View
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
    $horo = cv_get_daily_horoscope($id);
    if (!$horo) {
        echo '<div class="cv-alert cv-alert-danger">Record not found.</div>';
        return;
    }
}

$transient_key = 'cv_daily_horo_errors_' . get_current_user_id();
$cached_data = get_transient($transient_key);
$errors = array();
$inputs = array();

if ($cached_data) {
    $errors = $cached_data['errors'];
    $inputs = $cached_data['inputs'];
    delete_transient($transient_key);
}

$date = isset($inputs['date']) ? $inputs['date'] : ($horo ? $horo->date : current_time('Y-m-d'));
$zodiac_sign = isset($inputs['zodiac_sign']) ? $inputs['zodiac_sign'] : ($horo ? $horo->zodiac_sign : '');
$prediction = isset($inputs['prediction']) ? $inputs['prediction'] : ($horo ? $horo->prediction : '');
$career = isset($inputs['career']) ? $inputs['career'] : ($horo ? $horo->career : '');
$love = isset($inputs['love']) ? $inputs['love'] : ($horo ? $horo->love : '');
$health = isset($inputs['health']) ? $inputs['health'] : ($horo ? $horo->health : '');
$money = isset($inputs['money']) ? $inputs['money'] : ($horo ? $horo->money : '');
$lucky_number = isset($inputs['lucky_number']) ? $inputs['lucky_number'] : ($horo ? $horo->lucky_number : '');
$lucky_color = isset($inputs['lucky_color']) ? $inputs['lucky_color'] : ($horo ? $horo->lucky_color : '#C8A96A');
$today_rating = isset($inputs['today_rating']) ? (int) $inputs['today_rating'] : ($horo ? (int) $horo->today_rating : 3);
$status = isset($inputs['status']) ? $inputs['status'] : ($horo ? $horo->status : 'publish');
$scheduled_at = isset($inputs['scheduled_at']) ? $inputs['scheduled_at'] : ($horo && !empty($horo->scheduled_at) && $horo->scheduled_at !== '0000-00-00 00:00:00' ? date('Y-m-d\TH:i', strtotime($horo->scheduled_at)) : '');

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-manage')); ?>">Daily Horoscope</a> &gt; 
    <span><?php echo $is_edit ? 'Edit' : 'Add New'; ?></span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php echo $is_edit ? __('Edit Daily Horoscope', 'clairvoyant-core') : __('Add New Daily Horoscope', 'clairvoyant-core'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-manage')); ?>" class="cv-form-button secondary">Back to List</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="cv-alert cv-alert-danger">
        <div><strong>Validation Errors:</strong> Please fix the highlighted fields.</div>
        <span class="cv-alert-close">×</span>
    </div>
<?php endif; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="cv-validated-form">
    <input type="hidden" name="action" value="cv_save_daily_horo">
    <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field('cv_save_daily_horo_action', 'cv_daily_horo_nonce'); ?>

    <div class="cv-form-layout">
        <div>
            <!-- General Info Panel -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">General Info</h3>
                <div class="cv-form-grid-2">
                    <div class="cv-form-group">
                        <label class="cv-form-label" for="date">Date <span style="color:var(--danger)">*</span></label>
                        <input type="date" name="date" id="date" value="<?php echo esc_attr($date); ?>" required class="cv-form-input <?php echo isset($errors['date']) ? 'cv-field-error' : ''; ?>">
                        <?php if (isset($errors['date'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['date']); ?></span><?php endif; ?>
                    </div>
                    <div class="cv-form-group">
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
            <!-- Lucky Items & Ratings Panel -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Metadata</h3>
                <div class="cv-form-group">
                    <label class="cv-form-label" for="lucky_number">Lucky Number</label>
                    <input type="text" name="lucky_number" id="lucky_number" value="<?php echo esc_attr($lucky_number); ?>" class="cv-form-input cv-validate-numeric <?php echo isset($errors['lucky_number']) ? 'cv-field-error' : ''; ?>">
                    <?php if (isset($errors['lucky_number'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['lucky_number']); ?></span><?php endif; ?>
                </div>
                <div class="cv-form-group">
                    <label class="cv-form-label" for="lucky_color">Lucky Color</label>
                    <input type="text" name="lucky_color" id="lucky_color" value="<?php echo esc_attr($lucky_color); ?>" class="cv-form-input cv-color-picker <?php echo isset($errors['lucky_color']) ? 'cv-field-error' : ''; ?>">
                    <?php if (isset($errors['lucky_color'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['lucky_color']); ?></span><?php endif; ?>
                </div>
                <div class="cv-form-group">
                    <label class="cv-form-label">Today's Rating</label>
                    <div class="cv-rating-selector">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <input type="radio" id="star-<?php echo $i; ?>" name="today_rating" value="<?php echo $i; ?>" <?php checked($today_rating, $i); ?>>
                            <label for="star-<?php echo $i; ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($errors['today_rating'])) : ?><span class="cv-error-message"><?php echo esc_html($errors['today_rating']); ?></span><?php endif; ?>
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
                    <button type="submit" class="cv-form-button"><?php echo $is_edit ? 'Update Horoscope' : 'Publish Horoscope'; ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cv-horoscope-daily-manage')); ?>" class="cv-form-button secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
