<?php 
    function init_template(){

        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'title-tag' );

        register_nav_menus( 
            array(
                'top_menu' => 'Menú Principal'
            )
        );

    }

    add_action( 'after_setup_theme', 'init_template' );

    function assets(){

        //wp_register_style( 'bootstrap','https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', '4.5.2', 'all' );

        //wp_register_style( $handle:string, $src:string|boolean, $deps:array, $ver:string|boolean|null, $media:string )

        wp_enqueue_style( 'estilos', get_stylesheet_uri(), '', '1.0', 'all');

        //wp_register_script( $handle:string, $src:string|boolean, $deps:array, $ver:string|boolean|null, $in_footer:boolean )
        //wp_register_script( 'popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', '', '1.0', true )
        //wp_enqueue_script( 'bootstrap', 'https://code.jquery.com/jquery-3.5.1.slim.min.js', array('jquery', 'popper'), '4.5.2', true )
   
        wp_enqueue_script('custom', get_template_directory_uri().'/assets/js/custom.js', '', '1.0', true);
    }

    add_action('wp_enqueue_scripts','assets');

    function sidebar(){
        register_sidebar(
            array(
                'name' => 'Pie de página',
                'id' => 'footer',
                'description' => 'Zona de widgets para pie de página',
                'before_title' => '<p>',
                'after_title' => '</p>',
                'before_widget' => '<div id="%1$s" class="%2$s">',
                'after_widget' => '</div>',

            )
        );
    }

    add_action('widgets_init', 'sidebar');

    function productos_type(){
        
        $labels = array(
            'name' => 'Productos',
            'singular_name' => 'Producto',
            'menu_name' => 'Productos',
        );

        $args = array(
            'label' => 'Productos',
            'description' => 'Productos Hecho en la web',
            'labels' => $labels,
            'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
            'public' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-store',
            'can_export' => true,
            'publicly_queryable' => true,
            'rewrite' => true,
            'show_in_rest' => true,
        );
        
        register_post_type('producto', $args);

    }

    add_action('init', 'productos_type');