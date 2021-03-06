<?php
/*
Plugin Name: Easy Digital Downloads - Software Specs
Plugin URI: http://isabelcastillo.com/docs/category/easy-digital-downloads-software-specs-plugin
Description: Add software specs and Software Application Microdata to your downloads when using Easy Digital Downloads plugin.
Version: 1.8
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
Text Domain: easy-digital-downloads-software-specs
Domain Path: lang

Copyright 2013 - 2015 Isabel Castillo

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! class_exists('EDD_Software_Specs' ) ) {
class EDD_Software_Specs{

	private static $instance = null;
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private function __construct() {
		add_filter( 'isa_meta_boxes', array( $this, 'specs_metabox' ) );
		add_action( 'init', array( $this, 'init'), 9999 );
		add_filter( 'edd_add_schema_microdata', array( $this, 'remove_microdata') );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'the_content', array( $this, 'featureList_wrap' ), 20 );
		add_action( 'loop_start', array( $this, 'microdata_open' ), 10 );
		add_action( 'loop_end', array( $this, 'microdata_close' ), 10 );
		add_action( 'edd_after_download_content', array( $this, 'specs' ), 30 );
		add_action( 'edd_receipt_files', array( $this, 'receipt' ), 10, 5 );
		add_filter('plugin_row_meta', array( $this, 'rate_link' ), 10, 2);
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		if( ! defined( 'EDDSPECS_PLUGIN_DIR' ) )
			define( 'EDDSPECS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		require_once EDDSPECS_PLUGIN_DIR . 'widget-specs.php';

   }

   	public function enqueue() {
		wp_register_style('edd-software-specs', plugins_url('/edd-software-specs.css', __FILE__));
		if ( is_singular( 'download' ) ) {
	            wp_enqueue_style('edd-software-specs');
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'easy-digital-downloads-software-specs', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Add description Microdata to downloads content
	 * 
	 * @since 0.1
	 */
	
	public function featureList_wrap( $content ) {
		global $post;
		$dm = get_post_meta($post->ID, '_smartest_lastupdate', true);

		// add to conditions - only if last updated date is entered
		if ( ($post->post_type == 'download') && is_singular() && is_main_query() && $dm ) {
			$content = '<div itemprop="description">' . $content . '</div>';
		}
		return $content;
	}
	
	/**
	 * Basically same as edd_price, but has itemprop="price" on it
	 * Price
	 *
	 * Displays a formatted price for a download.
	 *
	 * @access      public
	 * @since       1.0
	 * @param       int $download_id The ID of the download price to show
	 * @param		bool $echo Whether to echo or return the results
	 * @return      void
	 */	
	public function smartest_isa_edd_price( $download_id, $echo = true ) {
		if ( edd_has_variable_prices( $download_id ) ) {
			$prices = edd_get_variable_prices( $download_id );
			// Return the lowest price
			$price_float = 0;
	        foreach ($prices as $key => $value)
	            if ( ( ( (float)$prices[ $key ]['amount'] ) < $price_float ) or ( $price_float == 0 ) )
	                $price_float = (float)$prices[ $key ]['amount'];
	            $price = edd_sanitize_amount( $price_float );
		} else {
			$price = edd_get_download_price( $download_id );// @isa try use this for diaplay my price
		}
		$price = apply_filters( 'edd_download_price', $price, $download_id );
		$price = '<span class="edd_price" id="edd_price_' . $download_id . '" itemprop="price">' . $price . '</span>';
		if ( $echo )
			echo $price;
		else
			return $price;
	}
	
	public function specs() {
	
		global $post;

		$dm = get_post_meta($post->ID, '_smartest_lastupdate', true);
		$pc = get_post_meta($post->ID, '_smartest_pricecurrency', true);
		$isa_curr = $pc ? $pc : '';
	
		/* compatible with EDD Software Licensing plugin. If it's active and its version is entered, use its version instead of ours */
	
		$eddchangelog_version = get_post_meta( $post->ID, '_edd_sl_version', TRUE );

		if ( empty( $eddchangelog_version ) ) {

			// get my own specs version
			$vKey = '_smartest_currentversion';

		} else {
			// get EDD Software Licensing's version
			$vKey = '_edd_sl_version';
		}
	
		$sVersion = get_post_meta($post->ID, $vKey, true);
		$appt = get_post_meta($post->ID, '_smartest_apptype', true);
		$filt = get_post_meta($post->ID, '_smartest_filetype', true);
		$fils = get_post_meta($post->ID, '_smartest_filesize', true);
		$reqs = get_post_meta($post->ID, '_smartest_requirements', true);
		$pric = $this->smartest_isa_edd_price($post->ID, false); // don't echo


		// only show if modified date is entered, and if not surpressed by widget, and if shortcode is not present
		$surpress = '';
		if ( ! $dm )
			$surpress = true;
		if ( has_shortcode( $post->post_content, 'edd-software-specs') )
			$surpress = true;
		if ( empty( $surpress ) && is_active_widget( false, false, 'edd_software_specs_widget', true ) )
			$surpress = true;

		if ( ! $surpress ) {

			// 1st close featurList element and open new div to pair up with closing div inserted by featureList_wrap()
			echo '</div><div><table id="isa-edd-specs"><caption>'. __( 'Specs', 'easy-digital-downloads-software-specs' ). '</caption>
									<tr>
										<td>'. __( 'Release date:', 'easy-digital-downloads-software-specs' ). '</td>
										<td>
		<meta itemprop="datePublished" content="'. get_post_time('Y-m-d', false, $post->ID). '">
								'. get_post_time('F j, Y', false, $post->ID, true). '</td>
									</tr>
									<tr>
										<td>'. __( 'Last updated:', 'easy-digital-downloads-software-specs' ). '</td>
		
													<td><meta itemprop="dateModified" content="';
	
				$moddate = ($dm) ? date('Y-m-d', $dm) : '';
				$moddatenice = ($dm) ? date('F j, Y', $dm) : '';
	echo $moddate . '">' . $moddatenice . '</td>
								</tr>';
			if($sVersion) {


								echo '<tr>
										<td>' . __( 'Current version:', 'easy-digital-downloads-software-specs' ) . '</td>
										<td itemprop="softwareVersion">' . $sVersion . '</td>
									</tr>';

			}


			if($appt) {
								echo '<tr>
										<td>'. __( 'Software application type:', 'easy-digital-downloads-software-specs' ) .'</td>
		
										<td itemprop="applicationCategory">'. $appt . '</td>
									</tr>';
			}

			if($filt) {			

	
								echo '<tr>
										<td>'. __( 'File format:', 'easy-digital-downloads-software-specs' ). '</td>
										<td itemprop="fileFormat">'. $filt .'</td>
									</tr>';

			}

			if($fils) {			

	
								echo '<tr>
										<td>'. __( 'File size:', 'easy-digital-downloads-software-specs' ) . '</td>
										<td itemprop="fileSize">' . $fils . '</td>
									</tr>';

			}

			if($reqs) {			


									echo '<tr>
										<td>' . __( 'Requirements:', 'easy-digital-downloads-software-specs' ) . '</td>
										<td itemprop="requirements">' . $reqs . '</td>
									</tr>';

			}

			if($pric && $isa_curr) {


									echo '<tr itemprop="offers" itemscope itemtype="http://schema.org/Offer">
										<td>' . __( 'Price:', 'easy-digital-downloads-software-specs' ) . '</td>
										<td><span>'. $pric . ' </span>
										 <span itemprop="priceCurrency">' . $isa_curr . '</span>			</td></tr>';

			}
	
			do_action( 'eddss_add_specs_table_row' );
	

				echo '</table>';
		} // end if($dm)	
	
	} // end specs
	
	
	/**
	 * adds specs metabox to downloads
	 */
	
	public function specs_metabox( $ic_meta_boxes ) {
		$prefix = '_smartest_';
	$ic_meta_boxes[] = array(
			'id'         => 'download_specs_meta_box',
			'title'      => __( 'Specs', 'easy-digital-downloads-software-specs' ),
			'pages'      => array( 'download'), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __( 'Date of Last Update', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'lastupdate',
					'type' => 'text_date_timestamp',
				),
				array(
					'name' => __( 'Current Version', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'currentversion',
					'desc' => __( 'If EDD Software Licensing or EDD Changelog plugin is enabled for this download, its version will take precedence in that order, and this field will be ignored.', 'easy-digital-downloads-software-specs' ),
					'type' => 'text_small',
				),

				array(
					'name' => __( 'Software Application Type', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'apptype',
					'desc' => __( 'Text to display (also used for microdata). For example, WordPress plugin, or Game', 'easy-digital-downloads-software-specs' ),
					'type'    => 'text',
				),
				array(
					'name' => __( 'File type', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'filetype',
					'desc' => __( 'For example, .zip, or .eps', 'easy-digital-downloads-software-specs' ),
					'type'    => 'text',
				),
	
				array(
					'name' => __( 'File Size', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'filesize',
					'type' => 'text_small',
				),
	
				array(
					'name' => __( 'Requirements', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'requirements',
					'desc' => __( 'For example, WordPress 3.3.1+, or a certain required plugin. Separate requirements with commas.', 'easy-digital-downloads-software-specs' ),
					'type' => 'text',
				),
	
				array(
					'name' => __( 'Price Currency', 'easy-digital-downloads-software-specs' ),
					'id'   => $prefix . 'pricecurrency',
					'desc' => sprintf(__( 'The type of currency that the price refers to. Use 3-letter %1$s.', 'easy-digital-downloads-software-specs' ), 
											'<a href="http://en.wikipedia.org/wiki/ISO_4217" title="ISO 4217 currency codes" target="_blank">ISO 4217 format</a>.'
									),
					'type' => 'text_small',
					
				),
	
	)
		);

	return $ic_meta_boxes;
	} // end specs_metabox

	public function init() {
		if ( ! class_exists( 'isabelc_Meta_Box' ) ) 
			require_once EDDSPECS_PLUGIN_DIR . 'lib/metabox/init.php';
	}


	/**
	 * remove EDD's itemtype product
	 * @param bool $ret the default return value
	 * @since 1.4
	 */

	public function remove_microdata( $ret ) {
		global $post;

		if ( ! is_object( $post ) ) {
			return $ret;
		}

		if ( get_post_meta($post->ID, '_smartest_lastupdate', true) ) {
			return false;				
		} else {
			return $ret;
		}

	}

	/**
	 * Add version to each download on edd_receipt.
	 *
	 * @since 1.5
	 */

	function receipt( $filekey, $file, $item_ID, $payment_ID, $meta ) {

		
		// If EDD Software Licensing plugin or EDD Changelog is present, don't add Software Specs version to receipt.
		$eddchangelog_version = get_post_meta( $item_ID, '_edd_sl_version', TRUE );

		if ( empty( $eddchangelog_version ) ) {
			$eddsspecs_ver = get_post_meta( $item_ID, '_smartest_currentversion', true );
			if ( ! empty( $eddsspecs_ver ) )
					printf( '<li id="sspecs_download_version" style="text-indent:48px;"> - %1$s %2$s</li>',
						__( 'Current Version:', 'easy-digital-downloads-software-specs' ),
						esc_html( $eddsspecs_ver )
			);		

		}
	}

	/** 
	 * Registers the EDD Related Downloads Widget.
	 * @since 1.5.7
	 */
	public function register_widgets() {
		register_widget( 'edd_software_specs_widget' );
	}

	// rate link on manage plugin page, since 1.4
	public function rate_link($links, $file) {
		if ($file == plugin_basename(__FILE__)) {
			$rate_link = '<a href="http://wordpress.org/support/view/plugin-reviews/easy-digital-downloads-software-specs">' . __('Rate It', 'easy-digital-downloads-software-specs') . '</a>';
			$links[] = $rate_link;
		}
		return $links;
	}
	/** 
	 * Shortcode to insert specs widget anywhere
	 * @since 1.5.9
	 */
	public function edd_software_specs_shortcode($atts) {
		extract( shortcode_atts( 
			array(	'title' => __( 'Specs', 'easy-digital-downloads-software-specs' ),
					'isodate' => false,
					'remove_specs_content_filter' => 'on',
			), 
			$atts
		));
		
		$atts['title'] = empty($atts['title']) ? __( 'Specs', 'easy-digital-downloads-software-specs' ) : $atts['title'];
		ob_start();
		the_widget( 'edd_software_specs_widget', $atts ); 
		$output = ob_get_clean();
		return $output;

	}

/**
* Add SoftwareApplication Microdata to single downloads
*
* @since 1.8
* @return void
*/
public function microdata_open() {
	global $post;
	static $microdata_open = NULL;
	if( true === $microdata_open || ! is_object( $post ) ) {
		return;
	}
	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		// only add microdata if last updated date is entered
		if( get_post_meta($post->ID, '_smartest_lastupdate', true) ) {
			$microdata_open = true;
			echo '<span itemscope itemtype="http://schema.org/SoftwareApplication">';
		}
	}
}
/**
* Close the SoftwareApplication Microdata wrapper on single downloads
*
* @since 1.8
* @return void
*/
public function microdata_close() {
	global $post;
	static $microdata_close = NULL;
	if( true === $microdata_close || ! is_object( $post ) ) {
		return;
	}
	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		// only add microdata if last updated date is entered
		if( get_post_meta($post->ID, '_smartest_lastupdate', true) ) {
			$microdata_close = true;
			echo '</span>';
		}
	}
}

}
}
$EDD_Software_Specs = EDD_Software_Specs::get_instance();
add_shortcode( 'edd-software-specs', array( $EDD_Software_Specs, 'edd_software_specs_shortcode' ) );