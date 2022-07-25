<?php namespace ProductFilter\Frontend;

use Premmerce\SDK\V2\FileManager\FileManager;

/**
 * Class Frontend
 *
 * @package ProductFilter\Frontend
 */
class Frontend {


	/**
	 * @var FileManager
	 */
	private $fileManager;

	public function __construct( FileManager $fileManager ) {
		$this->fileManager = $fileManager;

        add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ] );

        add_filter( 'template_include', [ $this, 'load_template_filter' ] );
	}

    /**
     * @return void
     */
    function add_scripts() {
        if ( \have_rows( 'filter_page', 'options' ) ) {
            while( \have_rows( 'filter_page', 'options' ) ) {
                \the_row();

                $page = \get_sub_field('page');

                if ( empty( $page ) ) continue;

                $page_id = $page->ID;

                if ( ! \is_page( $page_id ) ) continue;

                wp_enqueue_script(
                    'filter-page-js',
                    $this->fileManager->getPluginUrl() . 'dist/assets/frontend/filter-page.min.js',
                    [],
                    '1.0'
                );

                wp_enqueue_style(
                    'filter-page-css',
                    $this->fileManager->getPluginUrl() . 'dist/assets/frontend/filter-page.min.css',
                    [],
                    '1.0'
                );

                break;
            }

            if ( \function_exists( 'acf_remove_loop' ) ) {
                acf_remove_loop('active');
            }
        }
    }

    /**
     * @param $template
     * @return void
     */
    public function load_template_filter( $template )
    {
        if ( \have_rows( 'filter_page', 'options' ) ) {
            while( \have_rows( 'filter_page', 'options' ) ) {
                \the_row();

                $page = \get_sub_field('page');

                if ( empty( $page ) || get_the_ID() != $page->ID ) continue;

                $template = $this->fileManager->getPluginDirectory() . 'src/templates/template-filter.php';

                break;
            }

            if ( \function_exists( 'acf_remove_loop' ) ) {
                acf_remove_loop('active');
            }
        }

        return $template;
    }

}