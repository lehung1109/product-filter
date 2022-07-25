<?php if ( ! empty( $subtitle ) || ! empty( $title ) || ! empty( $desc ) || ! empty( $image_desktop ) ): ?>
    <section class="product-filter-banner">
        <div class="product-filter-banner__bg"></div>

        <?php if ( ! empty( $subtitle ) ): ?>
            <h2 class="product-filter-banner__subtitle"><?php echo $subtitle; ?></h2>
        <?php endif; ?>

        <?php if ( ! empty( $title ) ): ?>
            <h1 class="product-filter-banner__title"><?php echo $title; ?></h1>
        <?php endif; ?>

        <?php if ( ! empty( $desc ) ): ?>
            <div class="product-filter-banner__description"><?php echo $desc; ?></div>
        <?php endif; ?>

        <?php if ( ! empty( $image_desktop ) ): ?>
            <div class="product-filter-banner__image" style="position: absolute;left: 0;top: 0;width: 100%;height: 100%;object-fit: cover;z-index: -1;">
                <picture>
                    <?php
                        if ( ! empty( $image_mobile ) ):
                    ?>
                        <source media="(max-width: 991px)" srcset="<?php echo wp_get_attachment_image_src( $image_mobile->ID, 'full' ); ?>">
                    <?php endif; ?>

                    <?php
                        if ( is_object( $image_desktop ) ) {
                            echo wp_get_attachment_image( $image_desktop->ID, 'full', false, [ 'loading' => 'lazy' ] );
                        } else {
                            echo wp_get_attachment_image( $image_desktop, 'full', false, [ 'loading' => 'lazy' ] );
                        }
                    ?>
                </picture>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>