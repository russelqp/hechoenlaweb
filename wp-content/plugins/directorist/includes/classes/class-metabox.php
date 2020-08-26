<?php

if ( !class_exists('ATBDP_Metabox') ):
class ATBDP_Metabox {

    /**
     * Add meta boxes for ATBDP_POST_TYPE and ATBDP_SHORT_CODE_POST_TYPE
     * and Save the meta data
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action('add_meta_boxes_'.ATBDP_POST_TYPE,	array($this, 'listing_info_meta'));
            add_action('transition_post_status',	array($this, 'publish_atbdp_listings'), 10, 3);
            // edit_post hooks is better than save_post hook for nice checkbox
            // http://wordpress.stackexchange.com/questions/228322/how-to-set-default-value-for-checkbox-in-wordpress
            add_action( 'edit_post', array($this, 'save_post_meta'), 10, 2);
            add_action('post_submitbox_misc_actions', array($this, 'post_submitbox_meta'));


            add_action('wp_ajax_atbdp_custom_fields_listings', array($this, 'ajax_callback_custom_fields'), 10, 2 );
            add_action('wp_ajax_atbdp_custom_fields_listings_selected', array($this, 'ajax_callback_custom_fields'), 10, 2 );

        }
     }

    /**
     * @since 5.4.0
     */
    public function publish_atbdp_listings( $new_status, $old_status, $post ){
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : null;
        if ( ($post->post_type == 'at_biz_dir') && ( $old_status == 'pending'  &&  $new_status == 'publish' ) && !wp_verify_nonce( $nonce, 'quick-publish-action' ) ){
            do_action('atbdp_listing_published', $post->ID);//for sending email notification
        }
    }

    /**
     * Display custom fields.
     *
     * @since	 3.2
     * @access   public
     * @param	 int    $post_id	Post ID.
     * @param	 array    $term_id    Category ID.
     */
    public function ajax_callback_custom_fields( $post_id = 0, $term_id = array() ) {
        $ajax = false;
        if( isset( $_POST['term_id'] ) ) {
            $ajax = true;
            $post_ID = !empty($_POST['post_id'])?(int)$_POST['post_id']:'' ;
            $term_id = $_POST['term_id'];
        }
        // Get custom fields
        $custom_field_ids = !empty($term_id) ? $term_id : array();
        $args = array(
            'post_type'      => ATBDP_CUSTOM_FIELD_POST_TYPE,
            'posts_per_page' => -1,
            'status'        => 'published'
        );
        $meta_queries = array();
        if (!empty($custom_field_ids)){
            if (count($custom_field_ids)>1){
                $sub_meta_queries = array();
                foreach( $custom_field_ids as $value ) {
                    $sub_meta_queries[] = array(
                        'key'		=> 'category_pass',
                        'value'		=> $value,
                        'compare'	=> 'LIKE'
                    );
                }
                $meta_queries[] = array_merge( array( 'relation' => 'OR' ), $sub_meta_queries );
            }else{
                $meta_queries[] = array(
                    'key'		=> 'category_pass',
                    'value'		=> $custom_field_ids[0],
                    'compare'	=> 'LIKE'
                );
            }
        }
        $meta_queries[] = array(
            array(
                'key'       => 'associate',
                'value'     => 'categories',
                'compare'   => 'LIKE',
            ),
        );
        $count_meta_queries = count( $meta_queries );
        if( $count_meta_queries ) {
            $args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
        }
        $atbdp_query = new WP_Query( $args );

        if ($atbdp_query->have_posts()){
            // Start the Loop
            global $post;
            // Process output
            ob_start();

            include ATBDP_TEMPLATES_DIR . 'add-listing-custom-field.php';
            wp_reset_postdata(); // Restore global post data stomped by the_post()
            $output = ob_get_clean();

            print $output;

            if( $ajax ) {
                wp_die();
            }
        }else{
            echo '<div class="custom_field_empty_area"></div>';
            if( $ajax ) {
                wp_die();
            }
        }
    }


    /**
     * Render Metaboxes for ATBDP_POST_TYPE
     * @param Object $post Current Post Object being displayed
     */
    public function listing_info_meta( $post )
    {
        $display_prv_field = get_directorist_option('display_prv_field', 1);
        $display_gallery_field = get_directorist_option('display_gallery_field', 1);
        $display_video_field = get_directorist_option('display_video_field', 1);
        add_meta_box('_listing_info',
            __('General Information', 'directorist'),
            array($this, 'listing_info'),
            ATBDP_POST_TYPE,
            'normal', 'high');

        add_meta_box('_listing_contact_info',
            __('Contact Information', 'directorist'),
            array($this, 'listing_contact_info'),
            ATBDP_POST_TYPE,
            'normal', 'high');
        if (!empty($display_prv_field) || !empty($display_gallery_field)) {
            add_meta_box('_listing_gallery',
                __('Upload Preview & Slider Images for the Listing', 'directorist'),
                array($this, 'listing_gallery'),
                ATBDP_POST_TYPE,
                'normal', 'high');
         }
        /*
         *
         * It fires after the video metabox
         * @since 1.0.0
         */
        do_action('atbdp_before_video_gallery_backend',$post);
        if(!empty($display_video_field)) {
            add_meta_box('_listing_video_gallery',
                __('Add Video for the Listing', 'directorist'),
                array($this, 'listing_video_gallery'),
                ATBDP_POST_TYPE,
                'normal', 'high');
        }
wp_reset_postdata();

        /*
         *
         * It fires after the video metabox
         * @since 4.0.3
         */
        do_action('atbdp_after_video_metabox_backend_add_listing',$post);

    }

    /**
     * It displays Listing information meta on Add listing page on the backend
     * @param WP_Post $post
     */
    public function listing_info( $post ) {

        //data needed for the custom field
        $post_meta = get_post_meta( $post->ID );
        $category = wp_get_object_terms( $post->ID, 'at_biz_dir-category', array( 'fields' => 'ids' ) );

        // get all the meta values from the db, prepare them for use and then send in in a single bar to the add listing view
        $listing_info['never_expire']           = get_post_meta($post->ID, '_never_expire', true);
        $listing_info['featured']               = get_post_meta($post->ID, '_featured', true);
        $listing_info['price']                  = get_post_meta($post->ID, '_price', true);
        $listing_info['atbd_listing_pricing']   = get_post_meta($post->ID, '_atbd_listing_pricing', true);
        $listing_info['price_range']            = get_post_meta($post->ID, '_price_range', true);
        $listing_info['listing_status']         = get_post_meta($post->ID, '_listing_status', true);
        $listing_info['tagline']                = get_post_meta($post->ID, '_tagline', true);
        $listing_info['excerpt']                = get_post_meta($post->ID, '_excerpt', true);
        $listing_info['atbdp_post_views_count']                = get_post_meta($post->ID, '_atbdp_post_views_count', true);
        $listing_info['expiry_date']            = get_post_meta($post->ID, '_expiry_date', true);


        // add nonce security token
        wp_nonce_field( 'listing_info_action', 'listing_info_nonce' );
        ATBDP()->load_template('add-listing', compact('listing_info') ); // load metabox view and pass data to it.
    }
    /**
     * It displays meta box for listing contact information from the backend editor of ATBDP_POST_TYPE
     * @param WP_Post $post
     */
    public function listing_contact_info($post )
    {
        // get all the meta values from the db, prepare them for use and then send in in a single bar to the add listing view
        $listing_contact_info['address']                = get_post_meta($post->ID, '_address', true);
        $listing_contact_info['phone']                  = get_post_meta($post->ID, '_phone', true);
        $listing_contact_info['phone2']                  = get_post_meta($post->ID, '_phone2', true);
        $listing_contact_info['fax']                  = get_post_meta($post->ID, '_fax', true);
        $listing_contact_info['email']                 = get_post_meta($post->ID, '_email', true);
        $listing_contact_info['website']               = get_post_meta($post->ID, '_website', true);
        $listing_contact_info['zip']                    = get_post_meta($post->ID, '_zip', true);
        $listing_contact_info['social']                = get_post_meta($post->ID, '_social', true);
        $listing_contact_info['manual_lat']             = get_post_meta($post->ID, '_manual_lat', true);
        $listing_contact_info['manual_lng']            = get_post_meta($post->ID, '_manual_lng', true);
        $listing_contact_info['hide_map']               = get_post_meta($post->ID, '_hide_map', true);
        $listing_contact_info['bdbh']                  = get_post_meta($post->ID, '_bdbh', true);
        $listing_contact_info['enable247hour']         = get_post_meta($post->ID, '_enable247hour', true);
        $listing_contact_info['disable_bz_hour_listing']         = get_post_meta($post->ID, '_disable_bz_hour_listing', true);
        $listing_contact_info['listing_img']            = get_post_meta($post->ID, '_listing_img', true);
        $listing_contact_info['hide_contact_info']      = get_post_meta($post->ID, '_hide_contact_info', true);
        $listing_contact_info['hide_contact_owner']      = get_post_meta($post->ID, '_hide_contact_owner', true);
        $listing_contact_info['id_itself']              = $post->ID;

        ATBDP()->load_template('contact-info', compact('listing_contact_info') );
    }
    /**
     * It displays meta box for uploading image from the backend editor of ATBDP_POST_TYPE
     * @param WP_Post $post
     */
    public function listing_gallery($post )
    {

        $listing_img= get_post_meta($post->ID, '_listing_img', true);
        $listing_prv_img= get_post_meta($post->ID, '_listing_prv_img', true);?>
        <div id="directorist" class="directorist atbd_wrapper"><?php  ATBDP()->load_template('media-upload', compact('listing_img', 'listing_prv_img') );?></div>

   <?php }

    /**
     * It displays meta box for uploading video from the backend editor of ATBDP_POST_TYPE
     * @param WP_Post $post
     */
    public function listing_video_gallery($post )
    {
        $id = $post->ID;
        $post_meta = get_post_meta( $post->ID );
        $videourl = get_post_meta($post->ID, '_videourl', true);
        $enable_video_url = get_directorist_option('atbd_video_url',1);
        $video_placeholder = get_directorist_option('video_placeholder',__('Only YouTube & Vimeo URLs.', 'directorist'));
        $video_label = get_directorist_option('video_label', __('Video Url', 'directorist'));
        ?>
        <div id="directorist" class="directorist atbd_wrapper atbdp_video_field">
            <div class="form-group">
                <label for="videourl"><?php
                    $video_label = get_directorist_option('video_label', __('Video Url', ATBDP_TEXTDOMAIN));
                    esc_html_e($video_label.':', ATBDP_TEXTDOMAIN); ?></label>
                <input type="text" id="videourl"  name="videourl" value="<?php echo !empty($videourl) ? esc_url($videourl) : ''; ?>" class="form-control directory_field" placeholder="<?php echo esc_attr($video_placeholder); ?>"/>
            </div>
            <?php do_action('atbdp_video_field',$id); ?>
        </div>
        <?php
        //$video_gallery = apply_filters('atbdp_video_field',$video_field,$id);
    }

    /**
     * It outputs expiration date and featured checkbox custom field on the submit box metabox.
     * @param WP_Post $post
     */
    public function post_submitbox_meta($post)
    {
        if(ATBDP_POST_TYPE !=$post->post_type) return; // vail if it is not our post type
        // show expiration date and featured listing.
        $expire_in_days         = get_directorist_option('listing_expire_in_days');
        $f_active               = get_directorist_option('enable_featured_listing');
        $never_expire           = get_post_meta($post->ID, '_never_expire', true);
        $never_expire           = !empty($never_expire) ? (int) $never_expire : (empty($expire_in_days) ? 1 : 0);

        $e_d                    = get_post_meta($post->ID, '_expiry_date', true);
        $e_d                    = !empty($e_d) ? $e_d : calc_listing_expiry_date();
        $expiry_date            = atbdp_parse_mysql_date($e_d);

        $featured               = get_post_meta($post->ID, '_featured', true);
        $listing_status         = get_post_meta($post->ID, '_listing_status', true);
        $default_expire_in_days = !empty($default_expire_in_days) ? $default_expire_in_days : '';
        // load the meta fields
        $data = compact('f_active', 'never_expire', 'expiry_date', 'featured', 'listing_status', 'default_expire_in_days');
        ATBDP()->load_template('meta-partials/expiration-featured-fields', array('data'=> $data));
    }

    /**
     * Save Meta Data of ATBDP_POST_TYPE
     * @param int       $post_id    Post ID of the current post being saved
     * @param object    $post       Current post object being saved
     */
    public function save_post_meta( $post_id, $post ) {
        if ( ! $this->passSecurity($post_id, $post) )  return; // vail if security check fails
        $metas = array();
        $expire_in_days = get_directorist_option('listing_expire_in_days');
        $p = $_POST; // save some character
        $exp_dt = !empty($p['exp_date'])?atbdp_sanitize_array($p['exp_date']):array(); // get expiry date from the $_POST and then later sanitize it.
        $admin_category_select = !empty($p['admin_category_select'])? (int) $p['admin_category_select'] : '';
        $custom_field = (!empty($p['custom_field'])) ? $p['custom_field'] : array();
        // if the posted data has info about never_expire, then use it, otherwise, use the data from the settings.
        $metas['_never_expire']      = !empty($p['never_expire']) ? (int) $p['never_expire'] : (empty($expire_in_days) ? 1 : 0);
        $metas['_featured']          = !empty($p['featured'])? (int) $p['featured'] : 0;
        $metas['_price']             = !empty($p['price'])? (float) $p['price'] : '';
        $metas['_price_range']       = !empty($p['price_range'])?  $p['price_range'] : '';
        $metas['_atbd_listing_pricing'] = !empty($p['atbd_listing_pricing'])?  $p['atbd_listing_pricing'] : '';
        $metas['_videourl']          = !empty($p['videourl']) ?  sanitize_text_field($p['videourl']) : '';
        $metas['_listing_status']    = !empty($p['listing_status'])? sanitize_text_field($p['listing_status']) : 'post_status';
        $metas['_tagline']           = !empty($p['tagline'])? sanitize_text_field($p['tagline']) : '';
        $metas['_excerpt']           = !empty($p['excerpt'])? sanitize_text_field($p['excerpt']) : '';
        $metas['_atbdp_post_views_count']    = !empty($p['atbdp_post_views_count']) ? (int) $p['atbdp_post_views_count'] : '';
        $metas['_address']           = !empty($p['address'])? sanitize_text_field($p['address']) : '';
        $metas['_phone']             = !empty($p['phone'])? sanitize_text_field($p['phone']) : '';
        $metas['_phone2']             = !empty($p['phone2'])? sanitize_text_field($p['phone2']) : '';
        $metas['_fax']               = !empty($p['fax'])? sanitize_text_field($p['fax']) : '';
        $metas['_email']             = !empty($p['email'])? sanitize_text_field($p['email']) : '';
        $metas['_website']           = !empty($p['website'])? sanitize_text_field($p['website']) : '';
        $metas['_zip']               = !empty($p['zip'])? sanitize_text_field($p['zip']) : '';
        $metas['_social']            = !empty($p['social']) ? atbdp_sanitize_array($p['social']) : array(); // we are expecting array value
        $metas['_faqs']              = !empty($p['faqs']) ? ($p['faqs']) : array(); // we are expecting array value
        $metas['_enable247hour']     = !empty($p['enable247hour']) ? sanitize_text_field($p['enable247hour']) : ''; // we are expecting array value
        $metas['_disable_bz_hour_listing']     = !empty($p['disable_bz_hour_listing']) ? sanitize_text_field($p['disable_bz_hour_listing']) : ''; // we are expecting array value
        $metas['_bdbh']              = !empty($p['bdbh']) ? atbdp_sanitize_array($p['bdbh']) : array(); // we are expecting array value
        $metas['_manual_lat']        = !empty($p['manual_lat'])? sanitize_text_field($p['manual_lat']) : '';
        $metas['_manual_lng']        = !empty($p['manual_lng'])? sanitize_text_field($p['manual_lng']) : '';
        $metas['_hide_map']          = !empty($p['hide_map'])? sanitize_text_field($p['hide_map']) : '';
        $metas['_listing_img']       = !empty($p['listing_img'])? atbdp_sanitize_array($p['listing_img']) : array();
        $metas['_listing_prv_img']   = !empty($p['listing_prv_img'])? sanitize_text_field($p['listing_prv_img']) : '';
        $metas['_hide_contact_info'] = !empty($p['hide_contact_info'])? sanitize_text_field($p['hide_contact_info']) : 0;
        $metas['_hide_contact_owner'] = !empty($p['hide_contact_owner'])? sanitize_text_field($p['hide_contact_owner']) : 0;

        //$listing_info = (!empty($p['listing'])) ? aazztech_enc_serialize($p['listing']) : aazztech_enc_serialize(array());
        //prepare expiry date, if we receive complete expire date from the submitted post, then use it, else use the default data
        if (!is_empty_v($exp_dt) && !empty($exp_dt['aa'])){
            $exp_dt = array(
                'year'  => (int) $exp_dt['aa'],
                'month' => (int) $exp_dt['mm'],
                'day'   => (int) $exp_dt['jj'],
                'hour'  => (int) $exp_dt['hh'],
                'min'   => (int) $exp_dt['mn']
            );
            $exp_dt = get_date_in_mysql_format($exp_dt);
        }else{
            $exp_dt = calc_listing_expiry_date(); // get the expiry date in mysql date format using the default expiration date.
        }

        /*
         * send the custom field value to the database
         */
        if( isset( $custom_field ) ) {

            foreach( $custom_field as $key => $value ) {

                $type = get_post_meta( $key, 'type', true );

                switch( $type ) {
                    case 'text' :
                        $value = sanitize_text_field( $value );
                        break;
                    case 'textarea' :
                        $value = esc_textarea( $value );
                        break;
                    case 'select' :
                    case 'radio'  :
                        $value = sanitize_text_field( $value );
                        break;
                    case 'checkbox' :
                        $value = array_map( 'esc_attr', $value );
                        $value = implode( "\n", array_filter( $value ) );
                        break;
                    case 'url' :
                        $value = esc_url_raw( $value );
                        break;
                    default :
                        $value = sanitize_text_field( $value );
                        break;
                    case 'email' :
                        $value = sanitize_text_field( $value );
                        break;
                    case 'date' :
                        $value = sanitize_text_field( $value );

                }

                update_post_meta( $post_id, $key, $value );
            }

        }

        // save the meta data to the database
        //@todo need to adjust the meta for old user
        /*update_post_meta( $post_id, '_admin_category_select', $admin_category_select );
        wp_set_object_terms($post_id, $admin_category_select, ATBDP_CATEGORY);*/

        $metas['_expiry_date']              = $exp_dt;
        $metas = apply_filters('atbdp_listing_meta_admin_submission', $metas);
        // save the meta data to the database
        foreach ($metas as $meta_key => $meta_value) {
            update_post_meta($post_id, $meta_key, $meta_value); // array value will be serialize automatically by update post meta
        }
        if (!empty($p['listing_prv_img'])){
            set_post_thumbnail( $post_id, sanitize_text_field($p['listing_prv_img']) );
        }else{
            delete_post_thumbnail($post_id);
        }

        $listing_status = get_post_meta($post_id, '_listing_status', true);
        $post_status = get_post_status($post_id);
        $current_d = current_time('mysql');

        // let's check is listing need to update
        if (('expired' === $listing_status) && ('private' === $post_status)){
            // check is plans module active
            if (is_fee_manager_active()){
                $package_id = 'null' != $_POST['admin_plan'] ? esc_attr($_POST['admin_plan']):'';
                if (!empty($package_id)){
                    $package_length = get_post_meta($package_id, 'fm_length', true);
                    // Current time
                    // Calculate new date
                    $date = new DateTime($current_d);
                    $date->add(new DateInterval("P{$package_length}D")); // set the interval in days
                    $expired_date = $date->format('Y-m-d H:i:s');
                    $is_never_expaired = get_post_meta($package_id, 'fm_length_unl', true);
                    if (($expired_date > $current_d) || !empty($is_never_expaired)) {
                        wp_update_post( array(
                            'ID'           => $post_id,
                            'post_status' => 'publish', // update the status to private so that we do not run this func a second time
                            'meta_input' => array(
                                'listing_status' => 'post_status',
                            ), // insert all meta data once to reduce update meta query
                        ) );
                    }
                }
            }else{
                // no plans extension active so update the listing status if admin manually change the listing expire date
                if (($exp_dt > $current_d) || !empty($p['never_expire'])) {
                    wp_update_post( array(
                        'ID'           => $post_id,
                        'post_status' => 'publish', // update the status to private so that we do not run this func a second time
                        'meta_input' => array(
                            'listing_status' => 'post_status',
                        ), // insert all meta data once to reduce update meta query
                    ) );
                }
            }
        }
    }


    /**
     * Check if the the nonce, revision, auto_save are valid/true and the post type is ATBDP_POST_TYPE
     * @param int       $post_id    Post ID of the current post being saved
     * @param object    $post       Current post object being saved
     *
     * @return bool            Return true if all the above check passes the test.
     */
    private function passSecurity( $post_id, $post ) {
        if ( ATBDP_POST_TYPE == $post->post_type) {
        $is_autosave = wp_is_post_autosave($post_id);
        $is_revision = wp_is_post_revision($post_id);
        $nonce = !empty($_POST['listing_info_nonce']) ? $_POST['listing_info_nonce'] : '';
        $is_valid_nonce = wp_verify_nonce($nonce, 'listing_info_action');

        if ( $is_autosave || $is_revision || !$is_valid_nonce || ( !current_user_can( 'edit_'.ATBDP_POST_TYPE, $post_id )) ) return false;
        return true;
        }
        return false;
    }


    /**
     * It fetches all the information of the listing
     * @param int $id The id of the post whose meta we want to collect
     * @return array It returns the listing information of the given listing/post id.
     */
    public function get_listing_info($id=0)
    {
        global $post;
        $id = !empty($id) ? (int) $id : $post->ID;
        $listing_info['never_expire']           = get_post_meta($id, '_never_expire', true);
        $listing_info['featured']               = get_post_meta($id, '_featured', true);
        $listing_info['price']                  = get_post_meta($id, '_price', true);
        $listing_info['price_range']            = get_post_meta($id, '_price_range', true);
        $listing_info['videourl']               = get_post_meta($id, '_videourl', true);
        $listing_info['listing_status']         = get_post_meta($id, '_listing_status', true);
        $listing_info['tagline']                = get_post_meta($id, '_tagline', true);
        $listing_info['excerpt']                = get_post_meta($id, '_excerpt', true);
        $listing_info['atbdp_post_views_count'] = get_post_meta($id, '_atbdp_post_views_count', true);
        $listing_info['address']                = get_post_meta($id, '_address', true);
        $listing_info['phone']                  = get_post_meta($id, '_phone', true);
        $listing_info['phone2']                 = get_post_meta($id, '_phone2', true);
        $listing_info['fax']                    = get_post_meta($id, '_fax', true);
        $listing_info['email']                  = get_post_meta($id, '_email', true);
        $listing_info['website']                = get_post_meta($id, '_website', true);
        $listing_info['zip']                    = get_post_meta($id, '_zip', true);
        $listing_info['social']                 = get_post_meta($id, '_social', true);
        $listing_info['manual_lat']             = get_post_meta($id, '_manual_lat', true);
        $listing_info['manual_lng']             = get_post_meta($id, '_manual_lng', true);
        $listing_info['listing_img']            = get_post_meta($id, '_listing_img', true);
        $listing_info['hide_contact_info']      = get_post_meta($id, '_hide_contact_info', true);
        $listing_info['hide_contact_owner']     = get_post_meta($id, '_hide_contact_owner', true);
        $listing_info['expiry_date']            = get_post_meta($id, '_expiry_date', true);

        return apply_filters('atbdp_get_listing_info', $listing_info);

    }


}

endif;