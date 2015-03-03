<?php
namespace TheTrainingMangerLMS;

/**
 * Training LMS (WooCommerce) Product class
 *
 * This class encapsulates fields and functionality
 * specific to Training LMS Product
 * which is actually a wrapper for the WooCommerce
 * Product post type.
 *
 **/
@import('WP_DB_Object');

/*
	The class
*/
abstract class Product extends \WP_DB_Object {

protected static $fields = array(
	'title' => array( 'source' => 'post', 'wp_name' => 'post_title' ),
	'description' => array( 'source' => 'post', 'wp_name' => 'post_content' ),
	'short_description' => array( 'source' => 'post', 'wp_name' => 'post_excerpt' ),
	'product_attributes' => array( 'source' => 'postmeta', 'default' => array() ),
	'visibility' => array( 'source' => 'postmeta', 'default' => 'visible' ),
	'stock_status' => array( 'source' => 'postmeta', 'default' => 'instock' ),
	'downloadable' => array( 'source' => 'postmeta', 'default' => 'no' ),
	'virtual' => array( 'source' => 'postmeta', 'default' => 'no' ),
	'regular_price' => array( 'source' => 'postmeta', 'default' => '' ),
	'sale_price' => array( 'source' => 'postmeta', 'default' => '' ),
	'purchase_note' => array( 'source' => 'postmeta', 'default' => '' ),
	'featured' => array( 'source' => 'postmeta', 'default' => 'no' ),
	'weight' => array( 'source' => 'postmeta', 'default' => '' ),
	'length' => array( 'source' => 'postmeta', 'default' => '' ),
	'width' => array( 'source' => 'postmeta', 'default' => '' ),
	'height' => array( 'source' => 'postmeta', 'default' => '' ),
	'sku' => array( 'source' => 'postmeta', 'default' => '' ),
	'sale_price_dates_from' => array( 'source' => 'postmeta', 'default' => '' ),
	'sale_price_dates_to' => array( 'source' => 'postmeta', 'default' => '' ),
	'price' => array( 'source' => 'postmeta', 'default' => '' ),
	'sold_individually' => array( 'source' => 'postmeta', 'default' => '' ),
	'manage_stock' => array( 'source' => 'postmeta', 'default' => 'no' ),
	'backorders' => array( 'source' => 'postmeta', 'default' => 'no' ),
	'stock'=> array( 'source' => 'postmeta', 'default' => '' ),
	'product_image_gallery' => array( 'source' => 'postmeta', 'default' => '' ),
	//'thumbnail_id' => array( 'source' => 'postmeta', 'default' => '' ), // only include with actual value
);
protected static function getFieldsDescriptor() {
	return array_replace(parent::getFieldsDescriptor(), self::$fields);
}
protected static function prefix( $key ) {
	return '_' . $key;
}
protected static function getPostType() {
	return Constants::WooCommerceProductPostType;
}

	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}

	public function getPrice() {
		return $this->price;
	}
}
