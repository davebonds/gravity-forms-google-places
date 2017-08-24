<?php
/**
 * @link              https://github.com/davebonds/
 * @since             1.0.0
 * @package           GFormsGooglePlaces
 *
 * @wordpress-plugin
 * Plugin Name:       Gravity Forms Google Places
 * Plugin URI:        https://github.com/davebonds/gravity-forms-google-places
 * Description:       Adds a Gravity Forms field type for autocompleting addresses using the Google Places API.
 * Version:           1.0.0
 * Author:            Dave Bonds
 * Author URI:        https://github.com/davebonds/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gforms-google-places
 * Domain Path:       /languages
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
