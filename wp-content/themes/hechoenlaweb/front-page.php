<?php get_header(); ?>

    <main class="container">
        <?php 
            if(have_posts()){
                while(have_posts()){
                    the_post(); ?>
                
                <h1 class="title_portada"><?php the_title(); ?>!!</h1>
                <?php the_content(); ?>

                <?php }
            }
        ?>

        <div class="lista-productos">
            <h2 class="lista-productos__h2">Productos</h2>
            <?php
                $args = array(
                    'post_type' => 'producto',
                    'post_per_page' => -1,
                    'order' => 'ASC',
                    'orderby' => 'title',
                );
                $productos = new WP_Query($args);

                if($productos->have_posts()){
                    while($productos->have_posts()){
                        $productos->the_post();
                    ?>
                   
            <div class="producto">
                        <figure>
                            <?php the_post_thumbnail( 'large' ); ?>
                        </figure>
                        <h4>
                            <a href="<?php the_permalink() ; ?>"><?php the_title();?></a>
                        </h4>
            </div>

                <?php }

                }
            ?>
        </div>
    </main>


<?php get_footer(); ?>