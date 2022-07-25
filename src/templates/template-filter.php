<?php

global $wpdb;

$post_id = get_the_ID();

// Get data form url.
$params = get_filter_params(get_query_var('prams'));

// get filter data
$brands = [];
$taxonomies = [];
$attributes = [];
$custom_price = '';
$title = '';
$subtitle = '';
$image_desktop = '';
$image_mobile = '';
$desc = '';
$select_products = '';
$filter_options = [];

if ( function_exists( 'product_filter_get_filter' ) ) {
    [
        $brands,
        $taxonomies,
        $attributes,
        $custom_price,
        $title,
        $subtitle,
        $desc,
        $image_desktop,
        $image_mobile,
        $filter_options,
        $select_products
    ] = product_filter_get_filter( $post_id, $params );
}

if( empty( $brands ) ) return;

?>

<?php get_header(); ?>

    <div class="loader" style="display: none;">
        <img src="<?php bloginfo('template_url'); ?>/images/ajax-loader.gif" alt="ajax load">
    </div>

    <?php
    global $productFilterFileManager;

    if ( function_exists( 'product_filter_get_template' ) ) {
        product_filter_get_template(
            $productFilterFileManager->getPluginDirectory() . 'src/templates/banner-filter.php',
            [
                'title' => $title,
                'subtitle' => $subtitle,
                'desc' => $desc,
                'image_desktop' => $image_desktop,
                'image_mobile' => $image_mobile,
            ]
        );
    }
    ?>

    <div class="product-filter__container">
        <?php
            if ( function_exists( 'product_filter_get_template' ) ) {
                product_filter_get_template(
                    $productFilterFileManager->getPluginDirectory() . 'src/templates/filter-options.php',
                    [
                        'filter_options' => $filter_options,
                    ]
                );

                product_filter_get_template(
                    $productFilterFileManager->getPluginDirectory() . 'src/templates/product-listing.php',
                    [
                        'filter_options' => $filter_options,
                        'select_products' => $select_products,
                    ]
                );
            }
        ?>
    </div>
<?php get_footer(); ?>