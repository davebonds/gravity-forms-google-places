<?php
/**
Plugin Name: Gravity Forms Google Places
Description: Adds a Gravity Forms field type for autocompleting addresses using the Google Places API.
Author: Dave Bonds
Version: 1.0.0
Author URI: http://www.agentevolution.com/
License: GPL

@package GFormsGooglePlaces
 */

define( 'GF_GOOGLE_PLACES_ADDON_VERSION', '1.0.0' );
define( 'GF_GOOGLE_PLACES_ADDON_URL', plugins_url( '/', __FILE__ ) );

add_action( 'gform_loaded', array( 'GFormsGooglePlaces', 'load' ), 5 );

/**
 * Loads plugin class and registers addon with Gravity Forms.
 */
class GFormsGooglePlaces {

	/**
	 * Loads plugin class and registers addon with Gravity Forms.
	 *
	 * @return void|null Returns early if Gravity Forms not active.
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once __DIR__ . '/classes/gforms-google-places.php';

		GFAddOn::register( 'GPlacesAddon' );
	}

}
