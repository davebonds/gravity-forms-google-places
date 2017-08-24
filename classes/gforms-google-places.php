<?php
/**
 * Google Places Addon for Gravity Forms
 *
 * @package GFormsGooglePlaces
 */

/**
 * Initialize addon framework.
 */
GFForms::include_addon_framework();

/**
 * Google Places Extension for Gravity Forms
 */
class GPlacesAddon extends GFAddOn {

	protected $_version = GF_GOOGLE_PLACES_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gplaces';
	protected $_path = 'gravity-forms-google-places/google-places.php';
	protected $_full_path = __FILE__;
	protected $_plugin_url = GF_GOOGLE_PLACES_ADDON_URL;
	protected $_title = 'Gravity Forms Google Places Add-On';
	protected $_short_title = 'Google Places';
	private static $type = 'places-api';

	/**
	 * The class instance.
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * The addon instance.
	 *
	 * @return obj  The class instance.
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new GPlacesAddOn();
		}

		return self::$_instance;
	}
	/**
	 * Override parent admin_init. Loads actions and filters for admin only.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_add_field_buttons', array( $this, 'add_field_buttons' ) );
		add_filter( 'gform_field_type_title', array( $this, 'field_title' ) );
		add_action( 'gform_editor_js_set_default_values', array( $this, 'field_edit_defaults' ) );
		add_action( 'gform_editor_js', array( $this, 'editor_js' ) );
		add_filter( 'gform_field_input', array( $this, 'field' ), 10, 5 );
	}

	/**
	 * Add a new button to the field types add panel
	 *
	 * @param  array $field_groups array of field groups which each contain buttons for fields.
	 * @gform_add_field_buttons
	 */
	public function add_field_buttons( $field_groups ) {
		foreach ( $field_groups as &$group ) {
			if ( $group['name'] === 'advanced_fields' ) {
				$group['fields'][] = [
					'class' => 'button',
					'data-type' => self::$type,
					'value' => __( 'Google Places', 'gforms-google-places' ),
				];
			}
		}
		return $field_groups;
	}

	/**
	 * Filter the title for the field as it appears in the editor.
	 *
	 * @param  string $type Field type.
	 * @filter gform_field_type_title
	 */
	public function field_title( $type ) {
		if ( $type === self::$type ) {
			return __( 'Google Places Lookup', 'gforms-google-places' );
		}
	}

	/**
	 * Determine if the given field object is a google places type.
	 *
	 * @param  object $field Field object.
	 * @return bool        Is it?
	 */
	static function field_is_places( $field ) {
		return $field->type === self::$type;
	}

	/**
	 * Filter the HTML markup for a particular field.
	 *
	 * @param  string $input   original markup (at this point, always an empty string).
	 * @param  object $field   field object.
	 * @param  string $value   default value.
	 * @param  int    $lead_id lead id.
	 * @param  int    $form_id form id.
	 * @filter gform_field_input
	 */
	public function field( $input, $field, $value, $lead_id, $form_id ) {
		$html_id = 'input_' . ( is_admin() || ! $form_id ? '' : $form_id . '_' ) . $field->id;
		if ( self::field_is_places( $field ) ) {
			return '<div class="ginput_container"><input type="text" class="' . $field->size . ' geo-complete"'
				. ' name="input_' . esc_attr( $field->id ) . '" id="' . esc_attr( $html_id ) . '" value="' . esc_attr( $value ) . '" '
				. disabled( is_admin(), true, false ) . ' data-field-id="' . esc_attr( $field->id ) . '"'
				. 'placeholder="' . esc_html( $field->placeholder ) . '" ></div>';
		}
		return $input;
	}

	/**
	 * Fill in defaults for a new field
	 *
	 * @action gform_editor_js_set_default_values
	 */
	public function field_edit_defaults() {
	?>
		case <?php echo json_encode( self::$type ); ?>:
			field.label = 'Location';
		break;
	<?php
	}

	/**
	 * Additional JS to be included after gravity forms JS
	 *
	 * @action gform_editor_js
	 */
	public function editor_js() {
	?>
		<script type="text/javascript">
			// Defining settings for the new custom field.
			fieldSettings[<?php echo json_encode( self::$type ); ?>] = '.conditional_logic_field_setting, .error_message_setting, .label_setting, .label_placement_setting, .rules_setting, .admin_label_setting, .size_setting, .visibility_setting, .duplicate_setting, .placeholder_setting, .description_setting, .css_class_setting';
			fieldSettings['text'] += ', .geo_field_setting';
			fieldSettings['hidden'] += ', .geo_field_setting';
		</script>
	<?php
	}

	/**
	 * Enqueue scripts needed for the geo complete field
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'google-places',
				'src'     => 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->get_plugin_setting( 'gplaces-api' ),
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array(
						'field_types' => array( 'places-api' ),
					),
				),
			),
			array(
				'handle'  => 'jquery-geocomplete',
				'src'     => $this->_plugin_url . '/assets/jquery.geocomplete.min.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery', 'google-places' ),
				'enqueue' => array(
					array(
						'field_types' => array( 'places-api' ),
					),
				),
			),
			array(
				'handle'  => 'gforms-google-places',
				'src'     => $this->_plugin_url . '/assets/gforms-google-places.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery', 'jquery-geocomplete' ),
				'enqueue' => array(
					array(
						'field_types' => array( 'places-api' ),
					),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Google Places', 'gforms-google-places' ),
				'fields' => array(
					array(
						'name'              => 'gplaces-api',
						'tooltip'           => esc_html__( 'This is for your Google Maps Javascript API Key. Your key can be found in the <a href="https://console.developers.google.com/apis/credentials/key/" target="_blank">Google API console &raquo; Credentials Tab</a>', 'gforms-google-places' ),
						'label'             => esc_html__( 'Google Maps JS API key', 'gforms-google-places' ),
						'type'              => 'text',
						'class'             => 'large',
						'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
				),
			),
		);
	}

	/**
	 * The feedback callback for the 'gplaces-api' setting on the plugin settings page. Only checks for length.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) > 30;
	}

}
