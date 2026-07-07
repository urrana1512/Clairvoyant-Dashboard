<?php
/**
 * Global Constants
 * 
 * Defines global constants used throughout the Clairvoyant Core plugin
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin Version
if (!defined('CLAIRVOYANT_VERSION')) {
    define('CLAIRVOYANT_VERSION', '1.1.1');
}

// Plugin Directory Path
if (!defined('CLAIRVOYANT_PLUGIN_DIR')) {
    define('CLAIRVOYANT_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
}

// Plugin Directory URL
if (!defined('CLAIRVOYANT_PLUGIN_URL')) {
    define('CLAIRVOYANT_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
}

// Zodiac signs reference map
if (!defined('CV_ZODIAC_SIGNS')) {
    define('CV_ZODIAC_SIGNS', array(
        'aries'       => array('name' => 'Aries', 'icon' => '♈', 'dates' => 'Mar 21 - Apr 19'),
        'taurus'      => array('name' => 'Taurus', 'icon' => '♉', 'dates' => 'Apr 20 - May 20'),
        'gemini'      => array('name' => 'Gemini', 'icon' => '♊', 'dates' => 'May 21 - Jun 20'),
        'cancer'      => array('name' => 'Cancer', 'icon' => '♋', 'dates' => 'Jun 21 - Jul 22'),
        'leo'         => array('name' => 'Leo', 'icon' => '♌', 'dates' => 'Jul 23 - Aug 22'),
        'virgo'       => array('name' => 'Virgo', 'icon' => '♍', 'dates' => 'Aug 23 - Sep 22'),
        'libra'       => array('name' => 'Libra', 'icon' => '♎', 'dates' => 'Sep 23 - Oct 22'),
        'scorpio'     => array('name' => 'Scorpio', 'icon' => '♏', 'dates' => 'Oct 23 - Nov 21'),
        'sagittarius' => array('name' => 'Sagittarius', 'icon' => '♐', 'dates' => 'Nov 22 - Dec 21'),
        'capricorn'   => array('name' => 'Capricorn', 'icon' => '♑', 'dates' => 'Dec 22 - Jan 19'),
        'aquarius'    => array('name' => 'Aquarius', 'icon' => '♒', 'dates' => 'Jan 20 - Feb 18'),
        'pisces'      => array('name' => 'Pisces', 'icon' => '♓', 'dates' => 'Feb 19 - Mar 20'),
    ));
}
