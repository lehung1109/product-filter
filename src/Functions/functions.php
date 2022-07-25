<?php

/**
 * @param $template
 * @param $args
 * @return void
 */
function product_filter_get_template( $template, $args ) {
    extract( $args );

    include_once $template;
}

/**
 * @return array
 */
function product_filter_get_config_filter( $post_id ) {
    $brands = [];
    $taxonomies = [];
    $attributes = [];
    $custom_price = '';

    if ( have_rows( 'filter_page', 'options' ) ) {
        while ( have_rows( 'filter_page', 'options' ) ) {
            the_row();

            $page = get_sub_field('page');

            if ( $post_id != $page->ID ) continue;

            $brands = get_sub_field('brands');

            if ( have_rows( 'filter_by' ) ) {
                while ( have_rows( 'filter_by' ) ) {
                    the_row();

                    $taxonomies = get_sub_field('taxonomy') ?: [];
                    $attributes = get_sub_field('attributes') ?: [];
                    $custom_price = get_sub_field('custom_price') ?: '';
                }
            }
        }
    }

    return [ $brands, $taxonomies, $attributes, $custom_price ];
}

/**
 * @param $params
 * @return string
 */
function product_filter_get_price_sql( $params ) {
    global $wpdb;

    $price_sql = '';
    $price_where_sql = [];

    if ( ! empty( $params['price'] ) ) {
        foreach ( $params['price'] as $price ) {
            $price_distance = explode( '-', $price );

            if ( strpos( $price_distance[1], 'max' ) !== false ) {
                $price_where_sql[] = "( {$wpdb->postmeta}.meta_value >= {$price_distance[0]})";
            } else {
                $price_where_sql[] = "( {$wpdb->postmeta}.meta_value >= {$price_distance[0]} AND {$wpdb->postmeta}.meta_value <= {$price_distance[1]} )";
            }
        }
    }

    if ( ! empty( $price_where_sql ) ) {
        $price_sql = "LEFT JOIN
            (
                SELECT post_id, meta_value FROM {$wpdb->postmeta}
                WHERE {$wpdb->postmeta}.meta_key = '_price'
                    AND (%s)
            ) as price_table ON price_table.post_id = post_term_table.object_id";
        $price_sql = sprintf( $price_sql, implode( 'OR', $price_where_sql ) );
    }

    return $price_sql;
}

/**
 * @param $params
 * @param $brands
 * @param $taxonomies
 * @param $attributes
 * @return array
 */
function product_filter_get_term_filter( $params, $brands, $taxonomies, $attributes ) {
    $term_id_filter = [];
    $term_brands = [];

    foreach ( $brands as $brand ) {
        $term_brands[] = $brand->term_id;
    }

    if ( ! empty( $params['taxonomy'] ) ) {
        foreach ( $params['taxonomy'] as $pram ) {
            if ( in_array( $pram->taxonomy, array_merge( $taxonomies, $attributes ) ) ) {
                $term_id_filter[] = $pram->term_id;
            } elseif ( in_array( $pram->term_id, $term_brands ) ) {
                $term_id_filter[] = $pram->term_id;
                $has_taxonomy_brand = true;
            }
        }
    }

    if ( empty( $has_taxonomy_brand ) ) {
        foreach ( $brands as $brand ) {
            $term_id_filter[] = $brand->term_id;
        }
    }

    $term_sql_placeholder = implode( ',', array_fill( 0, count( $term_id_filter ), '%d' ) );

    return [ $term_id_filter, $term_sql_placeholder ];
}

/**
 * @param $params
 * @param $product_visibility_terms
 * @return string[]
 */
function product_filter_get_order_by( $params, $product_visibility_terms ) {
    global $wpdb;

    $fields_sql = '';
    $fields_select = '';
    $order_by_sql = '';

    if ( ! empty( $params['order_by'] ) && ! empty( $params['order'] ) ) {
        if ( $params['order_by'] == 'price' ) {
            $fields_select = ", price_table.meta_value";
            $order_by_sql = "ORDER BY CAST(price_table.meta_value as UNSIGNED) {$params['order']}";
        } elseif ( $params['order_by'] == 'popularity' ) {
            $fields_select = ", popularity_table.meta_value";
            $fields_sql = "LEFT JOIN
                    (
                        SELECT post_id, meta_value FROM {$wpdb->postmeta}
                        WHERE {$wpdb->postmeta}.meta_key = 'total_sales'
                    ) as popularity_table ON popularity_table.post_id = post_term_table.object_id";
            $order_by_sql = "ORDER BY CAST(popularity_table.meta_value as UNSIGNED) {$params['order']}";
        } elseif ( $params['order_by'] == 'featured' ) {
            $fields_select = ", featured_table.object_id";
            $fields_sql = "LEFT JOIN
                    (
                        SELECT object_id FROM {$wpdb->term_relationships}
                        INNER JOIN {$wpdb->term_taxonomy}
                        ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
                        INNER JOIN {$wpdb->terms}
                        ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                        WHERE {$wpdb->terms}.term_id IN({$product_visibility_terms['featured']})
                    ) as featured_table ON featured_table.object_id = post_term_table.object_id";
            $order_by_sql = "ORDER BY CAST(featured_table.object_id as UNSIGNED) {$params['order']}";
        }
    }

    return [ $fields_sql, $fields_select, $order_by_sql ];
}

/**
 * @param $fields_select
 * @param $term_sql_placeholder
 * @param $price_sql
 * @param $fields_sql
 * @param $order_by_sql
 * @param $term_id_filter
 * @param $product_visibility_not_in
 * @return array
 */
function product_filter_get_result_filter(
    $fields_select,
    $term_sql_placeholder,
    $price_sql,
    $fields_sql,
    $order_by_sql,
    $term_id_filter,
    $product_visibility_not_in
) {
    global $wpdb;

    $sql = "SELECT ID FROM ( SELECT DISTINCT post_table.ID {$fields_select} FROM
              (
                  SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' and post_status = 'publish'
              ) as post_table INNER JOIN
              (
                  SELECT object_id FROM {$wpdb->term_relationships}
                  INNER JOIN {$wpdb->term_taxonomy}
                  ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
                  INNER JOIN {$wpdb->terms}
                  ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                  WHERE {$wpdb->terms}.term_id IN({$term_sql_placeholder}) AND {$wpdb->terms}.term_id NOT IN (%d)
              ) as post_term_table ON post_table.ID = post_term_table.object_id
              {$price_sql}
              {$fields_sql}
              {$order_by_sql} ) as final_table";

    $product_ids = $wpdb->get_col(
        $wpdb->prepare(
            $sql,
            array_merge( $term_id_filter, [ $product_visibility_not_in ] )
        )
    );

    return [ $product_ids ];
}

/**
 * @param $product_ids
 * @param $taxonomies
 * @param $attributes
 * @return array[]
 */
function product_filter_get_filter_options( $product_ids, $taxonomies, $attributes ) {
    global $wpdb;

    $filter_options = [];

    if ( ! empty( $product_ids ) ) {
        $product_id_placeholder = implode( ',', array_fill( 0, count( $product_ids ), '%d' ) );
        $sql = "SELECT DISTINCT {$wpdb->term_taxonomy}.taxonomy, {$wpdb->terms}.term_id, {$wpdb->terms}.name
                FROM (
                    SELECT DISTINCT {$wpdb->term_relationships}.term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id IN ({$product_id_placeholder})
                ) as taxonomy INNER JOIN {$wpdb->term_taxonomy}
                ON {$wpdb->term_taxonomy}.term_taxonomy_id = taxonomy.term_taxonomy_id
                INNER JOIN {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id;";

        $result = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                $product_ids
            )
        );

        if ( ! empty( $result ) ) {
            $options = array_merge( $taxonomies, $attributes );

            foreach ( $result as $row ) {
                if ( in_array( $row->taxonomy, $options ) ) {
                    $filter_options[$row->taxonomy][] = [
                        'term_id' => $row->term_id,
                        'name' => $row->name,
                    ];
                }
            }
        }
    }

    return [ $filter_options ];
}

/**
 * @param $filter_options
 * @param $post_id
 * @return array
 */
function product_filter_get_banner_filter_fields( $filter_options, $post_id ) {
    $title = '';
    $subtitle = '';
    $image_desktop = '';
    $image_mobile = '';
    $desc = '';

    if (
        ! empty( $filter_options['product_brands'] )
        && count( $filter_options['product_brands'] ) === 1
        && (
            (
                ! empty( $filter_options['product_collection'] )
                && count( $filter_options['product_collection'] ) > 1
            )
            || empty( $filter_options['product_collection'] )
        )
    ) {
        $title = $filter_options['product_brands'][0]['name'];
        $desc = term_description( $filter_options['product_brands'][0]['term_id'] );
        $image_desktop = get_field('banner', 'product_brands_' . $filter_options['product_brands'][0]['term_id'] );
        $image_mobile = get_field('banner_mobile', 'product_brands_' . $filter_options['product_brands'][0]['term_id'] );
    } elseif (
        ! empty( $filter_options['product_collection'] )
        && count( $filter_options['product_collection'] ) === 1
        && (
            (
                ! empty( $filter_options['product_brands'] )
                && count( $filter_options['product_brands'] ) > 1
            )
            || empty( $filter_options['product_brands'] )
        )
    ) {
        $title = $filter_options['product_collection'][0]['name'];
        $desc = term_description( $filter_options['product_collection'][0]['term_id'] );
        $image_desktop = get_field('banner', 'product_collection_' . $filter_options['product_collection'][0]['term_id'] );
        $image_mobile = get_field('banner_mobile', 'product_collection_' . $filter_options['product_collection'][0]['term_id'] );
    } elseif(
        ! empty( $filter_options['product_collection'] )
        && count( $filter_options['product_collection'] ) === 1
        && ! empty( $filter_options['product_brands'] )
        && count( $filter_options['product_brands'] ) === 1
    ) {
        $title = $filter_options['product_brands'][0]['name'];
        $subtitle = $filter_options['product_collection'][0]['name'];
        $desc = term_description( $filter_options['product_brands'][0]['term_id'] );
        $image_desktop = get_field('banner', 'product_brands_' . $filter_options['product_brands'][0]['term_id'] );
        $image_mobile = get_field('banner_mobile', 'product_brands_' . $filter_options['product_brands'][0]['term_id'] );
    }

    if ( empty( $title ) ) {
        $title = get_the_title( $post_id );
    }

    if ( empty( $image_desktop ) && has_post_thumbnail( $post_id ) ) {
        $image_desktop = get_post_thumbnail_id( $post_id );
        $image_mobile = get_field('thumbnail_mobile', $post_id);
    }

    return [ $title, $subtitle, $image_desktop, $image_mobile, $desc ];
}

/**
 * @param $post_id
 * @return array
 */
function product_filter_get_custom_filter_products( $post_id ) {
    $select_products = get_field( 'select_products', $post_id );

    return [ $select_products ];
}

/**
 * @param $post_id
 * @param $params
 * @return array
 */
function product_filter_get_filter( $post_id, $params ) {
    // get options filter
    $brands = [];
    $taxonomies = [];
    $attributes = [];
    $custom_price = '';

    if ( function_exists( 'product_filter_get_config_filter' ) ) {
        [ $brands, $taxonomies, $attributes, $custom_price ] = product_filter_get_config_filter( $post_id );
    }

    // get visibility term
    $product_visibility_terms  = wc_get_product_visibility_term_ids();
    $product_visibility_not_in = $product_visibility_terms['exclude-from-catalog'];
    // end get visibility term

    // get price filter
    $price_sql = '';

    if ( function_exists( 'product_filter_get_price_sql' ) ) {
        $price_sql = product_filter_get_price_sql( $params );
    }
    // end get price filter

    // get term filter
    $term_id_filter = [];
    $term_sql_placeholder = '';

    if ( function_exists( 'product_filter_get_term_filter' ) ) {
        [ $term_id_filter, $term_sql_placeholder ] = product_filter_get_term_filter( $params, $brands, $taxonomies, $attributes );
    }
    // end get term filter

    // order by
    $fields_sql = '';
    $fields_select = '';
    $order_by_sql = '';

    if ( function_exists( 'product_filter_get_order_by' ) ) {
        [ $fields_sql, $fields_select, $order_by_sql ] = product_filter_get_order_by( $params, $product_visibility_terms );
    }
    // end order by

    // get product ids
    $product_ids = '';

    if ( function_exists( 'product_filter_get_result_filter' ) ) {
        [ $product_ids ] = product_filter_get_result_filter(
            $fields_select,
            $term_sql_placeholder,
            $price_sql,
            $fields_sql,
            $order_by_sql,
            $term_id_filter,
            $product_visibility_not_in
        );
    }
    // end get product ids

    // get filter options
    $filter_options = [];

    if ( function_exists( 'product_filter_get_filter_options' ) ) {
        [ $filter_options ] = product_filter_get_filter_options( $product_ids, $taxonomies, $attributes );
    }
    // end get filter options

    // build banner components
    $title = '';
    $subtitle = '';
    $image_desktop = '';
    $image_mobile = '';
    $desc = '';

    if ( function_exists( 'product_filter_get_banner_filter_fields' ) ) {
        [ $title, $subtitle, $image_desktop, $image_mobile, $desc ] = product_filter_get_banner_filter_fields( $filter_options, $post_id );
    }
    // end build banner components

    // get custom product
    $select_products = product_filter_get_custom_filter_products( $post_id );
    // end get custom product

    return [
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
    ];
}
