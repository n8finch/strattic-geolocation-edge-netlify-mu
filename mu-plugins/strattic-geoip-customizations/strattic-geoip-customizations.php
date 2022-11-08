<?php
/**
 * GeoIP customizations customizations.
 */

class GeoIP_Customizations {
	const AJAX_PATH = '/wp-json/strattic-geoip/geoip-content/';

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		add_filter(	'strattic_paths', array( $this, 'add_ajax_path_to_publish_list' ) );
		add_filter(	'strattic_paths_selective', array( $this, 'add_ajax_path_to_selective_publish_list' ) );

		add_action( 'rest_api_init', [ $this, 'register_geoip_info_route' ] );
		add_shortcode( 'geoip-content', [ $this, 'geoip_content_shortcode' ] );

	}

	public function geoip_content_shortcode( $atts, $content = '' ) {
		// Get any options already there.
		$geo_ip_content = get_option( 'strattic_geo_ip_content', [] );

		// Get the key/value pairs for the JSON object.
		foreach( $atts as $key => $value ) {
			// Create the index for the div.
			$index = hash( 'md5', $value . $content );
			$geo_ip_content[ $index ][ $key ] = explode( ',', $value );
			// Save the content to the JSON object.
			$geo_ip_content[ $index ]['content'] = $content;
		}

		// Update the option with the new info.
		update_option( 'strattic_geo_ip_content', $geo_ip_content, true );

		// replace the content with a div and ID.
		return "<div data-id='$index' class='geoip-targets'></div>";
	}

	public function scripts () {
		wp_enqueue_script( 'strattic-geoip', plugin_dir_url( __FILE__ ) . 'strattic-geoip-script.js' , [], time(), true );
	}

	public function register_geoip_info_route() {
		register_rest_route( '/strattic-geoip/', '/geoip-content/',
			array(
				'methods' => 'GET',
				'callback' =>  function() {
					echo json_encode( get_option( 'strattic_geo_ip_content', [] ) );
				},
				'permission_callback' => function() { return ''; }
			)
		);
	}



	/**
	 * Add new AJAX path to publish list.
	 */
	public function add_ajax_path_to_publish_list( $paths ) {
		$paths[] = array(
			'path'          => self::AJAX_PATH,
			'priority'      => 6,
			'quick_publish' => true,
		);

		return $paths;
	}

	/**
	 * Add new AJAX path to publish list for selective publish.
	 */
	public function add_ajax_path_to_selective_publish_list( $paths ) {
		$paths[]['path'] = self::AJAX_PATH;

		return $paths;
	}

}

new GeoIP_Customizations();
