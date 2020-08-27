<?php get_header(); ?>

<main class="container">
    <div class="page-404">
    <h1>
        404 PÁGINA NO ENCONTRADA
    </h1>
    <H2>
        Haz click <a href="<?php echo home_url();?>">aquí</a> para volver a la página principal
    </H2>
    <img src="<?php echo get_template_directory_uri();?>/assets/img/404.jpg" alt="imagen_404_página_no_encontrada_hechoenlaweb">
    </div>

</main>

<?php get_footer(); ?>