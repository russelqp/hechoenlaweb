<?php get_header(); ?>
    <main class="container">
        <?php
            if(have_posts()){
                while(have_posts()){
                    the_post(); ?>
                <section class="post">
                    <h1 class="my_title_post"><?php the_title(); ?></h1>
                    <div class="img_featured">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                    <div class="contenido_post">
                        <?php the_content();?>
                    </div>
                </section>

            <?php    }
            }
        ?>
    </main>
<?php get_footer(); ?>