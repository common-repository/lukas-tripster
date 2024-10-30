<?php
if ( ! defined( 'ABSPATH' ) )
		exit;

if ( ! class_exists( '_WP_Editors' ) )
		require( ABSPATH . WPINC . '/class-wp-editor.php' );

function lukas_tripster_tinymce_plugin_translation() {
		$strings = array(
				'City' => __('City', 'lukas-tripster'),
				'Number of block' => __('Number of block', 'lukas-tripster'),
				'Settings Title' => __('Settings of the Tripster shortcode', 'lukas-tripster'),
				'Skip' => __('Skip', 'lukas-tripster'),
		);
    $mce_locale = get_user_locale();
    $locale = _WP_Editors::$mce_locale;
		$translated = 'tinyMCE.addI18n("' . $locale . '.lukas_tripster_tinymce_plugin", ' . json_encode( $strings ) . ");\n";

    return $translated;
}

$strings = lukas_tripster_tinymce_plugin_translation();
