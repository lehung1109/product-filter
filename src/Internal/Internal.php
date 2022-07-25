<?php namespace ProductFilter\Internal;

use Premmerce\SDK\V2\FileManager\FileManager;
use ProductFilter\Internal\ACFFieldAttribute;
use ProductFilter\Internal\ACFFieldTaxonomy;

/**
 * Class Admin
 *
 * @package ProductFilter\Admin
 */
class Internal {

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Admin constructor.
     *
     * Register menu items and handlers
     *
     * @param FileManager $fileManager
     */
    public function __construct( FileManager $fileManager ) {
        $this->fileManager = $fileManager;

        add_action( 'acf/include_field_types', [ $this, 'create_custom_field_type' ] );

        add_action('acf/init', [ $this, 'option_page' ]);
    }

    /**
     * @return void
     */
    public function option_page()
    {
        if( function_exists('acf_add_options_page') ) {

            \acf_add_options_page(array(
                'page_title' 	=> 'Filter Page Settings',
                'menu_title'	=> 'Filter Page',
                'menu_slug' 	=> 'filter-page-general-settings',
                'capability'	=> 'edit_posts',
                'icon_url'      => 'dashicons-layout',
                'redirect'		=> true
            ));

            \acf_add_options_sub_page(array(
                'page_title' 	=> 'General',
                'menu_title'	=> 'General',
                'parent_slug'	=> 'filter-page-general-settings',
            ));

        }
    }

    /**
     * @param $args
     * @param $field
     * @param $post_id
     * @return mixed
     */
    public function get_product_taxonomies( $args, $field, $post_id )
    {
        return $args;
    }

    /**
     * @return void
     */
    public function create_custom_field_type()
    {
        $ACFFieldTaxonomy = new ACFFieldTaxonomy();
        $ACFFieldTaxonomy->fileManager = $this->fileManager;

        $ACFFieldAttribute = new ACFFieldAttribute();
        $ACFFieldAttribute->fileManager = $this->fileManager;
    }

}