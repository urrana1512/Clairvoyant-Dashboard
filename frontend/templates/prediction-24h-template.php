<?php
/**
 * 24-48 Hours Prediction Frontend Shortcode Template
 * 
 * Renders predictions for Fire, Earth, Air, and Water elements in a responsive grid
 * 
 * @package Clairvoyant_Core
 * @subpackage Frontend
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$consultation_url = cv_get_setting('consultation_url', 'https://clairvoyantofficial.com/services/#booking');
$consultation_btn_text = cv_get_setting('consultation_btn_text', 'Book Consultation');

// 1. Gather Date
$target_date = isset($atts['date']) ? sanitize_text_field($atts['date']) : '';
if (empty($target_date)) {
    global $wpdb;
    $target_date = $wpdb->get_var($wpdb->prepare(
        "SELECT date FROM {$wpdb->prefix}cv_prediction_24_48 WHERE status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s) ORDER BY date DESC LIMIT 1",
        current_time('mysql')
    ));
}
if (empty($target_date)) {
    $target_date = current_time('Y-m-d');
}

// 2. Fetch predictions
global $wpdb;
$records = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cv_prediction_24_48 WHERE date = %s AND status = 'publish' AND (scheduled_at IS NULL OR scheduled_at <= %s)",
    $target_date,
    current_time('mysql')
));

$predictions = array();
foreach ($records as $r) {
    $predictions[strtolower($r->element)] = $r;
}

$suryoday = '';
$suryast = '';
$good_time = '';
$hindu_muhurat = '';
$rahu_kaal = '';

if (!empty($records)) {
    $first_rec = $records[0];
    $suryoday = $first_rec->suryoday;
    $suryast = $first_rec->suryast;
    $good_time = $first_rec->good_time;
    $hindu_muhurat = $first_rec->hindu_muhurat;
    $rahu_kaal = $first_rec->rahu_kaal;
}

$elements = cv_get_element_list();
$formatted_date = cv_format_date($target_date);
?>

<div class="cv-prediction-24h-wrapper">
    <div class="cv-prediction-24h-heading">
        <h2>24-48 Hours Forecast</h2>
        <p>Short-term cosmic alignments and elemental shifts - <?php echo esc_html($formatted_date); ?></p>
    </div>

    <?php if ($suryoday || $suryast || $good_time || $hindu_muhurat || $rahu_kaal) : ?>
        <div class="cv-panchang-banner">
            <h3 class="cv-panchang-title">✨ Daily Auspicious Times & Panchang</h3>
            <div class="cv-panchang-grid">
                <?php if ($suryoday) : ?>
                    <div class="cv-panchang-item">
                        <span class="cv-panchang-icon">🌅</span>
                        <div class="cv-panchang-info">
                            <strong>Suryoday (Sunrise)</strong>
                            <span><?php echo esc_html($suryoday); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($suryast) : ?>
                    <div class="cv-panchang-item">
                        <span class="cv-panchang-icon">🌇</span>
                        <div class="cv-panchang-info">
                            <strong>Suryast (Sunset)</strong>
                            <span><?php echo esc_html($suryast); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($good_time) : ?>
                    <div class="cv-panchang-item">
                        <span class="cv-panchang-icon">⏱️</span>
                        <div class="cv-panchang-info">
                            <strong>Good Time</strong>
                            <span><?php echo esc_html($good_time); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hindu_muhurat) : ?>
                    <div class="cv-panchang-item">
                        <span class="cv-panchang-icon">🔱</span>
                        <div class="cv-panchang-info">
                            <strong>Hindu Muhurat</strong>
                            <span><?php echo esc_html($hindu_muhurat); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($rahu_kaal) : ?>
                    <div class="cv-panchang-item rahu-kaal">
                        <span class="cv-panchang-icon">🚫</span>
                        <div class="cv-panchang-info">
                            <strong>Rahu Kaal</strong>
                            <span><?php echo esc_html($rahu_kaal); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="cv-prediction-24h-grid">
        <?php foreach ($elements as $key => $el) : 
            $has_data = isset($predictions[$key]);
            $pred_text = $has_data ? strip_tags($predictions[$key]->prediction) : 'Cosmic trends are adjusting. No element prediction available for this period.';
            $short_pred = mb_strimwidth($pred_text, 0, 140, '...');
            $full_prediction = $has_data ? wp_kses_post($predictions[$key]->prediction) : '';
            ?>
            <div class="cv-prediction-card cv-element-card-<?php echo esc_attr($key); ?> <?php echo $has_data ? 'cv-clickable-element-card' : 'cv-disabled-card'; ?>"
                 data-name="<?php echo esc_attr($el['name']); ?>"
                 data-icon="<?php echo esc_attr($el['icon']); ?>"
                 data-date="24-48 Hours Forecast"
                 data-prediction="<?php echo esc_attr($full_prediction); ?>"
                 data-signs="<?php echo esc_attr($el['label']); ?>">
                 
                <div class="cv-element-card-glow"></div>
                <div class="cv-element-badge">
                    <span class="cv-element-badge-icon"><?php echo esc_html($el['icon']); ?></span>
                </div>
                
                <h3 class="cv-element-title"><?php echo esc_html($el['name']); ?></h3>
                <div class="cv-element-signs"><?php echo esc_html($el['label']); ?></div>
                <p class="cv-element-desc"><?php echo esc_html($short_pred); ?></p>
                
                <a href="#" class="cv-element-card-btn">Read Elemental Forecast</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 24-48 Hrs Prediction Detail Lightbox Modal -->
<div class="cv-modal-lightbox" id="cv-prediction-lightbox">
    <button class="cv-lightbox-close" id="cv-prediction-lightbox-close-btn">&times;</button>
    <div class="cv-lightbox-content">
        <div class="cv-lightbox-header">
            <span class="cv-lightbox-icon" id="cv-prediction-lightbox-icon">✨</span>
            <div>
                <h3 class="cv-lightbox-title" id="cv-prediction-lightbox-title">-</h3>
                <span class="cv-lightbox-date" id="cv-prediction-lightbox-date">-</span>
            </div>
        </div>
        <div class="cv-lightbox-body">
            <!-- Signs Included Badge row -->
            <div class="cv-prediction-lightbox-signs" id="cv-prediction-lightbox-signs" style="margin-bottom: 20px; font-weight: 500; font-size: 14px; background: rgba(200, 169, 106, 0.08); padding: 10px 14px; border-radius: 8px; color: #1b1b1b; display: inline-block;"></div>
            
            <div class="cv-lightbox-prediction" id="cv-prediction-lightbox-text"></div>
            
            <?php if (!empty($consultation_url)) : ?>
                <div class="cv-lightbox-actions" style="margin-top: 32px; text-align: center; border-top: 1px solid #E8DFD0; padding-top: 24px;">
                    <a href="<?php echo esc_url($consultation_url); ?>" target="_blank" class="cv-consultation-btn">
                        <?php echo esc_html($consultation_btn_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
