<?php
/**
 * Settings Form Page View
 * 
 * @package Clairvoyant_Core
 * @subpackage Settings
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Check success messages
$message = isset($_GET['message']) ? sanitize_key($_GET['message']) : '';
$alert_html = '';
if ($message === 'saved') {
    $alert_html = '<div class="cv-alert cv-alert-success">Settings saved successfully!<span class="cv-alert-close">×</span></div>';
}

// 2. Fetch current settings from database
$site_name = cv_get_setting('site_name', get_bloginfo('name'));
$logo_url = cv_get_setting('logo_url', '');
$footer_text = cv_get_setting('footer_text', '');
$primary_color = cv_get_setting('primary_color', '#C8A96A');
$secondary_color = cv_get_setting('secondary_color', '#B89050');
$consultation_url = cv_get_setting('consultation_url', 'https://clairvoyantofficial.com/services/#booking');
$consultation_btn_text = cv_get_setting('consultation_btn_text', 'Book Consultation');

// Social links (stored as array/JSON)
$socials = cv_get_setting('social_links', array());
$fb = isset($socials['facebook']) ? $socials['facebook'] : '';
$ig = isset($socials['instagram']) ? $socials['instagram'] : '';
$tw = isset($socials['twitter']) ? $socials['twitter'] : '';
$li = isset($socials['linkedin']) ? $socials['linkedin'] : '';
$wa = isset($socials['whatsapp']) ? $socials['whatsapp'] : '';

?>

<div class="cv-breadcrumb">
    <a href="<?php echo esc_url(admin_url('admin.php?page=clairvoyant-dashboard')); ?>">Clairvoyant Core</a> &gt; 
    <span>Settings</span>
</div>

<div class="cv-page-title-row">
    <h1 class="cv-page-title"><?php esc_html_e('Global Website Settings', 'clairvoyant-core'); ?></h1>
</div>

<?php echo $alert_html; ?>

<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="cv_save_settings">
    <?php wp_nonce_field('cv_save_settings_action', 'cv_settings_nonce'); ?>

    <div class="cv-form-layout">
        <!-- Left Side: Config fields -->
        <div>
            <!-- Box 1: Website Details -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Website Settings</h3>
                
                <div class="cv-form-group">
                    <label class="cv-form-label" for="site_name">Website Name</label>
                    <input type="text" name="site_name" id="site_name" value="<?php echo esc_attr($site_name); ?>" class="cv-form-input">
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label">Logo Upload</label>
                    <div class="cv-upload-container">
                        <img src="<?php echo !empty($logo_url) ? esc_url($logo_url) : CLAIRVOYANT_PLUGIN_URL . 'assets/images/default-avatar.png'; ?>" id="cv-logo-preview" class="cv-preview-image" alt="Website Logo">
                        <div class="cv-upload-details">
                            <input type="hidden" name="logo_url" id="cv_logo_url" value="<?php echo esc_url($logo_url); ?>">
                            <button type="button" class="cv-form-button secondary cv-upload-trigger" data-target="cv_logo_url" data-preview="cv-logo-preview">Upload Logo</button>
                        </div>
                    </div>
                </div>

                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label" for="footer_text">Footer Text</label>
                    <textarea name="footer_text" id="footer_text" class="cv-form-textarea" rows="4"><?php echo esc_textarea($footer_text); ?></textarea>
                </div>
            </div>

            <!-- Box 1b: Consultation Settings -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Consultation Button Settings</h3>
                <div class="cv-form-group">
                    <label class="cv-form-label" for="consultation_btn_text">Button Text</label>
                    <input type="text" name="consultation_btn_text" id="consultation_btn_text" value="<?php echo esc_attr($consultation_btn_text); ?>" class="cv-form-input" placeholder="Book Consultation">
                </div>
                <div class="cv-form-group" style="margin-bottom:0;">
                    <label class="cv-form-label" for="consultation_url">Consultation URL</label>
                    <input type="url" name="consultation_url" id="consultation_url" value="<?php echo esc_url($consultation_url); ?>" class="cv-form-input" placeholder="https://yourwebsite.com/booking-page">
                    <span class="description" style="font-size:11px; color:#888; display:block; margin-top:4px;">Define the target URL for the "Book Consultation" button inside detail modals. Leave empty to hide the button.</span>
                </div>
            </div>

            <!-- Box 2: Colors -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Theme Colors</h3>
                <div class="cv-form-grid-2">
                    <div class="cv-form-group" style="margin-bottom:0;">
                        <label class="cv-form-label" for="primary_color">Primary Color</label>
                        <input type="text" name="primary_color" id="primary_color" value="<?php echo esc_attr($primary_color); ?>" class="cv-form-input cv-color-picker">
                    </div>
                    <div class="cv-form-group" style="margin-bottom:0;">
                        <label class="cv-form-label" for="secondary_color">Secondary Color</label>
                        <input type="text" name="secondary_color" id="secondary_color" value="<?php echo esc_attr($secondary_color); ?>" class="cv-form-input cv-color-picker">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Social Media Links -->
        <div>
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Social Links</h3>
                
                <div class="cv-form-group">
                    <label class="cv-form-label" for="fb">Facebook</label>
                    <input type="url" name="social[facebook]" id="fb" value="<?php echo esc_url($fb); ?>" class="cv-form-input" placeholder="https://facebook.com/yourpage">
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="ig">Instagram</label>
                    <input type="url" name="social[instagram]" id="ig" value="<?php echo esc_url($ig); ?>" class="cv-form-input" placeholder="https://instagram.com/yourpage">
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="tw">Twitter</label>
                    <input type="url" name="social[twitter]" id="tw" value="<?php echo esc_url($tw); ?>" class="cv-form-input" placeholder="https://twitter.com/yourpage">
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="li">LinkedIn</label>
                    <input type="url" name="social[linkedin]" id="li" value="<?php echo esc_url($li); ?>" class="cv-form-input" placeholder="https://linkedin.com/in/yourprofile">
                </div>

                <div class="cv-form-group">
                    <label class="cv-form-label" for="wa">WhatsApp</label>
                    <input type="text" name="social[whatsapp]" id="wa" value="<?php echo esc_attr($wa); ?>" class="cv-form-input" placeholder="+1234567890">
                </div>
            </div>

            <!-- Action submit -->
            <div class="cv-form-panel">
                <h3 class="cv-form-panel-title">Save Configuration</h3>
                <p class="description" style="margin-top:0; margin-bottom: 20px;">
                    Saving these options will dynamically adjust settings across the website.
                </p>
                <button type="submit" class="cv-form-button" style="width: 100%;">Save Settings</button>
            </div>
        </div>
    </div>
</form>
