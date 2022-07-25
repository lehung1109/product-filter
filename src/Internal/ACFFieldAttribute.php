<?php namespace ProductFilter\Internal;

use Premmerce\SDK\V2\FileManager\FileManager;
use acf_field;

/**
 * Class Admin
 *
 * @package ProductFilter\Admin
 */
class ACFFieldAttribute extends acf_field {
    public $fileManager;

    /**
     * Admin constructor.
     *
     * Register menu items and handlers
     *
     */
    public function __construct() {
        parent::__construct();

        add_action( 'wp_ajax_acf/fields/' . $this->name . '/query',        [ $this, 'ajax_query' ] );
        add_action( 'wp_ajax_nopriv_acf/fields/' . $this->name . '/query', [ $this, 'ajax_query' ] );
    }

    /**
     * @return void
     */
    function ajax_query() {

        // validate
        if( ! \ acf_verify_ajax() ) die();


        // get choices
        $response = $this->get_ajax_query( $_POST );


        // return
        \ acf_send_ajax_results($response);

    }

    /**
     * @param $options
     * @return array|array[]
     */
    public function get_ajax_query( $options )
    {
        $field = acf_get_field( $options['field_key'] );

        $attributes = $this->get_attributes( $field );

        $s = strtolower( $options['s'] );
        $results = [];

        foreach ( $attributes as $key => $attribute ) {
            if ( ! empty( $s ) && strpos( strtolower( $attribute ), $s ) === false ) continue;

            $results[] = [
                'text' => $attribute,
                'id' => $key
            ];
        }

        return [
            'results' => $results
        ];
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $this->name     = 'list_attribute';
        $this->label    = 'List Attribute';
        $this->category = 'relational';
        $this->defaults = array(
            'post_type'     => array(),
        );
    }

    /**
     * @param $field
     * @return void
     */
    function prepare_field( $field ) {

        // Change Field into a select
        $field['type']          = 'select';
        $field['ui']            = 1;
        $field['ajax']          = 1;
        $field['multiple']      = 1;
        $field['choices'] = $this->get_attributes( $field );
        $field['ajax_action'] = 'acf/fields/' . $this->name . '/query';

        return $field;
    }

    /**
     * @return mixed
     */
    public function get_attributes( $field )
    {
        if ( ! function_exists( 'wc_get_attribute_taxonomy_names' ) ) return;

        $attribute_taxonomies = \ wc_get_attribute_taxonomies();
        $attributes = [];

        foreach ( $attribute_taxonomies as $attribute ) {
            $attributes['pa_' . $attribute->attribute_name] = $attribute->attribute_label;
        }

        return $attributes;
    }

    /**
     * @param $field
     * @return void
     */
    public function render_field_settings( $field )
    {
    }

    /**
     * @param $value
     * @param $post_id
     * @param $field
     * @return mixed
     */
    function load_value( $value, $post_id, $field ) {
        return $value;

    }

    /**
     * @param $value
     * @param $post_id
     * @param $field
     * @return mixed
     */
    function format_value( $value, $post_id, $field ) {
        return $value;
    }

    /**
     * @param $value
     * @param $post_id
     * @param $field
     * @return mixed
     */
    function update_value( $value, $post_id, $field ) {
        return $value;
    }

}