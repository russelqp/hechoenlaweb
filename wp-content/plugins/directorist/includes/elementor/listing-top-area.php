<?php
/**
 * @author AazzTech
 */

namespace AazzTech\Directorist\Elementor;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Directorist_Listing_Top_Area extends Custom_Widget_Base {

	public function __construct( $data = [], $args = null ) {
		$this->az_name = __( 'Single Listing Top', 'directorist' );
		$this->az_base = 'directorist_listing_top_area';
		parent::__construct( $data, $args );
	}

	public function az_fields(){
		$fields = array(
			array(
				'mode'    => 'section_start',
				'id'      => 'sec_general',
				'label'   => __( 'General', 'directorist' ),
			),
			array(
				'type'      => Controls_Manager::HEADING,
				'id'        => 'sec_heading',
				'label'     => $this->az_texts['single'],
			),
			array(
				'mode' => 'section_end',
			),
		);
		return $fields;
	}

	protected function render() {

		$shortcode = '[directorist_listing_top_area]';

		echo do_shortcode( $shortcode );
	}
}