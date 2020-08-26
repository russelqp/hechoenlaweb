<?php
    //Plugin name: Helw Oscuro
    //Description: Activa el modo oscuro en tu theme
    //version: 1.0
    //Author: Russel QP
    //Author URI: https://github.com/russelqp

    function estilos_plugin(){

        $estilos_url = plugin_dir_url( __FILE__ );

        wp_enqueue_style( 'modo_oscuro', $estilos_url.'/assets/css/style.css', '', '1.0', 'all' );



    }

    add_action('wp_enqueue_scripts', 'estilos_plugin');
    

