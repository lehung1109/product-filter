<?php namespace ProductFilter\Internal;

use Premmerce\SDK\V2\FileManager\FileManager;
use acf_field;

/**
 * Class Admin
 *
 * @package ProductFilter\Admin
 */
class ACFFieldTaxonomy extends acf_field {

    /**
     * @var FileManager
     */
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

        $taxonomies = $this->get_taxonomies( $field );

        $s = strtolower( $options['s'] );
        $results = [];

        foreach ( $taxonomies as $key => $taxonomy ) {
            if ( ! empty( $s ) && strpos( strtolower( $taxonomy ), $s ) === false ) continue;

            $results[] = [
                'text' => $taxonomy,
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
        $this->name     = 'list_taxonomy';
        $this->label    = 'List Taxonomy';
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
        $field['type'] = 'select';
        $field['multiple'] = 1;
        $field['choices'] = $this->get_taxonomies( $field );
        $field['ajax'] = 1;
        $field['ui'] = 1;
        $field['ajax_action'] = 'acf/fields/' . $this->name . '/query';

        return $field;
    }

    /**
     * @return mixed
     */
    public function get_taxonomies( $field )
    {
        global $wp_taxonomies;

        if ( empty( $wp_taxonomies ) ) return;

        $taxonomies = [];
        $post_type = $field['post_type'];

        foreach ( $wp_taxonomies as $taxonomy ) {
            if ( empty( array_intersect( $taxonomy->object_type, $post_type ) ) || ! $taxonomy->show_in_nav_menus ) continue;

            $taxonomies[$taxonomy->name] = $taxonomy->label;
        }

        return $taxonomies;
    }

    /**
     * @param $field
     * @return void
     */
    public function render_field_settings( $field )
    {
        acf_render_field_setting(
            $field,
            array(
                'label'        => __( 'Filter by Post Type', 'acf' ),
                'instructions' => '',
                'type'         => 'select',
                'name'         => 'post_type',
                'choices'      => \acf_get_pretty_post_types(),
                'multiple'     => 1,
                'ui'           => 1,
                'allow_null'   => 1,
                'placeholder'  => __( 'All post types', 'acf' ),
            )
        );
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