<?php
/**
 * Data Validation Functions
 * 
 * Validates fields for different models before database insertion
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate Daily Rashi data
 * 
 * @param array $data Input form data
 * @return array Validation response with 'valid' (bool) and 'errors' (array)
 */
function cv_validate_daily_rashi($data) {
    $errors = array();

    if (empty($data['date']) || !strtotime($data['date'])) {
        $errors['date'] = __('Please provide a valid date.', 'clairvoyant-core');
    }

    if (empty($data['zodiac_sign']) || !cv_get_zodiac_info($data['zodiac_sign'])) {
        $errors['zodiac_sign'] = __('Please select a valid zodiac sign.', 'clairvoyant-core');
    }

    if (empty($data['prediction']) || mb_strlen(strip_tags($data['prediction'])) < 50) {
        $errors['prediction'] = __('Prediction text is required and must be at least 50 characters long.', 'clairvoyant-core');
    }

    if (isset($data['lucky_number']) && $data['lucky_number'] !== '') {
        if (!preg_match('/^\d+$/', $data['lucky_number'])) {
            $errors['lucky_number'] = __('Lucky number must contain digits only.', 'clairvoyant-core');
        }
    }

    if (isset($data['today_luck_rating']) && $data['today_luck_rating'] !== '') {
        $rating = (int) $data['today_luck_rating'];
        if ($rating < 1 || $rating > 5) {
            $errors['today_luck_rating'] = __('Rating must be an integer between 1 and 5.', 'clairvoyant-core');
        }
    }

    // Max length check for short descriptions
    $short_fields = array('career', 'love', 'health', 'finance', 'lucky_color');
    foreach ($short_fields as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 500) {
            $errors[$field] = sprintf(__('%s cannot exceed 500 characters.', 'clairvoyant-core'), ucfirst($field));
        }
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}

/**
 * Validate Daily Horoscope data
 * 
 * @param array $data Input form data
 * @return array
 */
function cv_validate_daily_horoscope($data) {
    $errors = array();

    if (empty($data['date']) || !strtotime($data['date'])) {
        $errors['date'] = __('Please provide a valid date.', 'clairvoyant-core');
    }

    if (empty($data['zodiac_sign']) || !cv_get_zodiac_info($data['zodiac_sign'])) {
        $errors['zodiac_sign'] = __('Please select a valid zodiac sign.', 'clairvoyant-core');
    }

    if (empty($data['prediction']) || mb_strlen(strip_tags($data['prediction'])) < 50) {
        $errors['prediction'] = __('Prediction text is required and must be at least 50 characters.', 'clairvoyant-core');
    }

    if (isset($data['lucky_number']) && $data['lucky_number'] !== '') {
        if (!preg_match('/^\d+$/', $data['lucky_number'])) {
            $errors['lucky_number'] = __('Lucky number must contain digits only.', 'clairvoyant-core');
        }
    }

    if (isset($data['today_rating']) && $data['today_rating'] !== '') {
        $rating = (int) $data['today_rating'];
        if ($rating < 1 || $rating > 5) {
            $errors['today_rating'] = __('Rating must be between 1 and 5.', 'clairvoyant-core');
        }
    }

    $short_fields = array('career', 'love', 'health', 'money', 'lucky_color');
    foreach ($short_fields as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 500) {
            $errors[$field] = sprintf(__('%s cannot exceed 500 characters.', 'clairvoyant-core'), ucfirst($field));
        }
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}

/**
 * Validate Weekly Horoscope data
 * 
 * @param array $data Input form data
 * @return array
 */
function cv_validate_weekly_horoscope($data) {
    $errors = array();

    if (empty($data['week_start']) || !strtotime($data['week_start'])) {
        $errors['week_start'] = __('Please provide a valid start date for the week.', 'clairvoyant-core');
    }

    if (empty($data['week_end']) || !strtotime($data['week_end'])) {
        $errors['week_end'] = __('Please provide a valid end date for the week.', 'clairvoyant-core');
    }

    if (!empty($data['week_start']) && !empty($data['week_end'])) {
        if (strtotime($data['week_start']) > strtotime($data['week_end'])) {
            $errors['week_end'] = __('Week end date must be after the week start date.', 'clairvoyant-core');
        }
    }

    if (empty($data['zodiac_sign']) || !cv_get_zodiac_info($data['zodiac_sign'])) {
        $errors['zodiac_sign'] = __('Please select a valid zodiac sign.', 'clairvoyant-core');
    }

    if (empty($data['prediction']) || mb_strlen(strip_tags($data['prediction'])) < 50) {
        $errors['prediction'] = __('Prediction text is required and must be at least 50 characters.', 'clairvoyant-core');
    }

    if (isset($data['overall_rating']) && $data['overall_rating'] !== '') {
        $rating = (int) $data['overall_rating'];
        if ($rating < 1 || $rating > 5) {
            $errors['overall_rating'] = __('Rating must be between 1 and 5.', 'clairvoyant-core');
        }
    }

    $short_fields = array('career', 'love', 'health', 'money');
    foreach ($short_fields as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 500) {
            $errors[$field] = sprintf(__('%s cannot exceed 500 characters.', 'clairvoyant-core'), ucfirst($field));
        }
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}

/**
 * Validate Transit Horoscope data
 * 
 * @param array $data Input form data
 * @return array
 */
function cv_validate_transit_horoscope($data) {
    $errors = array();

    if (empty($data['planet'])) {
        $errors['planet'] = __('Planet field is required.', 'clairvoyant-core');
    }

    if (empty($data['transit_start_date']) || !strtotime($data['transit_start_date'])) {
        $errors['transit_start_date'] = __('Please provide a valid transit start date.', 'clairvoyant-core');
    }

    if (!empty($data['transit_end_date']) && !strtotime($data['transit_end_date'])) {
        $errors['transit_end_date'] = __('Please provide a valid transit end date or leave empty.', 'clairvoyant-core');
    }

    if (!empty($data['transit_start_date']) && !empty($data['transit_end_date'])) {
        if (strtotime($data['transit_start_date']) > strtotime($data['transit_end_date'])) {
            $errors['transit_end_date'] = __('Transit end date must be after start date.', 'clairvoyant-core');
        }
    }

    if (empty($data['title'])) {
        $errors['title'] = __('Title is required.', 'clairvoyant-core');
    }

    if (empty($data['prediction']) || mb_strlen(strip_tags($data['prediction'])) < 50) {
        $errors['prediction'] = __('Prediction text is required and must be at least 50 characters.', 'clairvoyant-core');
    }

    if (isset($data['affected_signs']) && mb_strlen($data['affected_signs']) > 500) {
        $errors['affected_signs'] = __('Affected signs cannot exceed 500 characters.', 'clairvoyant-core');
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}

/**
 * Validate Testimonial data
 * 
 * @param array $data Input form data
 * @return array
 */
function cv_validate_testimonial($data) {
    $errors = array();

    if (empty($data['client_name'])) {
        $errors['client_name'] = __('Client name is required.', 'clairvoyant-core');
    }

    if (empty($data['review']) || mb_strlen(strip_tags($data['review'])) < 10) {
        $errors['review'] = __('Review text is required and must be at least 10 characters.', 'clairvoyant-core');
    }

    if (isset($data['rating']) && $data['rating'] !== '') {
        $rating = (int) $data['rating'];
        if ($rating < 1 || $rating > 5) {
            $errors['rating'] = __('Rating must be between 1 and 5.', 'clairvoyant-core');
        }
    }

    $short_fields = array('service', 'location');
    foreach ($short_fields as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 255) {
            $errors[$field] = sprintf(__('%s cannot exceed 255 characters.', 'clairvoyant-core'), ucfirst($field));
        }
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}

/**
 * Validate 24-48 Hours Prediction data
 * 
 * @param array $data Input form data
 * @return array
 */
function cv_validate_prediction_24_48($data) {
    $errors = array();

    if (empty($data['date']) || !strtotime($data['date'])) {
        $errors['date'] = __('Please provide a valid date.', 'clairvoyant-core');
    }

    if (empty($data['element']) || !in_array($data['element'], array('fire', 'earth', 'air', 'water'))) {
        $errors['element'] = __('Please select a valid element.', 'clairvoyant-core');
    }

    if (empty($data['prediction']) || mb_strlen(strip_tags($data['prediction'])) < 50) {
        $errors['prediction'] = __('Prediction text is required and must be at least 50 characters long.', 'clairvoyant-core');
    }

    $text_fields_100 = array('suryoday', 'suryast');
    foreach ($text_fields_100 as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 100) {
            $errors[$field] = sprintf(__('%s cannot exceed 100 characters.', 'clairvoyant-core'), ucfirst($field));
        }
    }

    $text_fields_255 = array('good_time', 'hindu_muhurat', 'rahu_kaal');
    foreach ($text_fields_255 as $field) {
        if (isset($data[$field]) && mb_strlen($data[$field]) > 255) {
            $errors[$field] = sprintf(__('%s cannot exceed 255 characters.', 'clairvoyant-core'), ucfirst(str_replace('_', ' ', $field)));
        }
    }

    return array(
        'valid'  => empty($errors),
        'errors' => $errors
    );
}
