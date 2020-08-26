<?php
defined('ABSPATH') || die('Direct access is not allowed.');
/**
 * @since 4.7.2
 * @package Directorist
 */
if (!class_exists('ATBDP_Tools')) :

    class ATBDP_Tools
    {


        /**
         * The path to the current file.
         *
         * @var string
         */
        protected $file = '';

        /**
         * Whether to skip existing products.
         *
         * @var bool
         */
        protected $update_existing = false;

        /**
         * The current delimiter for the file being read.
         *
         * @var string
         */
        protected $delimiter = ',';

        /**
         * The current delimiter for the file being read.
         *
         * @var string
         */
        protected $postilion = 0;


        public function __construct()
        {
            add_action('admin_menu', array($this, 'add_tools_submenu'), 10);
            add_action('admin_init', array($this, 'atbdp_csv_import_controller'));
            $this->file            = isset($_REQUEST['file']) ? wp_unslash($_REQUEST['file']) : '';
            $this->update_existing = isset($_REQUEST['update_existing']) ? (bool) $_REQUEST['update_existing'] : false;
            $this->delimiter       = !empty($_REQUEST['delimiter']) ? wp_unslash($_REQUEST['delimiter']) : ',';
            add_action('wp_ajax_atbdp_import_listing', array($this, 'atbdp_import_listing'));
        }


        public function atbdp_import_listing()
        {
            $data               = array();
            $imported           = 0;
            $failed             = 0;
            $count              = 0;
            $new_listing_status = get_directorist_option('new_listing_status', 'pending');
            $preview_image      = isset($_POST['_listing_prv_img']) ? sanitize_text_field($_POST['_listing_prv_img']) : '';
            $title              = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
            $delimiter          = isset($_POST['delimiter']) ? sanitize_text_field($_POST['delimiter']) : '';
            $description        = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '';
            $position           = isset($_POST['position']) ? sanitize_text_field($_POST['position']) : 0;
            $metas              = isset($_POST['meta']) ? atbdp_sanitize_array($_POST['meta']) : array();
            $tax_inputs         = isset($_POST['tax_input']) ? atbdp_sanitize_array($_POST['tax_input']) : array();
            $limit              = apply_filters('atbdp_listing_import_limit_per_cycle', 10);
            $all_posts          = csv_get_data($this->file, true, $delimiter);
            $posts              = array_slice($all_posts, $position);
            $total_length       = count($all_posts);
            $limit              = apply_filters('atbdp_listing_import_limit_per_cycle', ($total_length > 100) ? 20 : (($total_length < 35) ? 2 : 5));

            if ( ! $total_length ) {
                $data['error'] = __('No data found', 'directorist');
                die();
            }
            foreach ($posts as $index => $post) {
                    if ($count === $limit ) break;
                    // start importing listings
                    $args = array(
                        "post_title"   => isset($post[$title]) ? $post[$title] : '',
                        "post_content" => isset($post[$description]) ? $post[$description] : '',
                        "post_type"    => 'at_biz_dir',
                        "post_status"  => $new_listing_status,
                    );
                    $post_id = wp_insert_post($args);

                    if (!is_wp_error($post_id)) {
                        $imported++;
                    } else {
                        $failed++;
                    }

                    if ($tax_inputs) {
                        foreach ( $tax_inputs as $taxonomy => $term ) {
                            if ('category' == $taxonomy) {
                                $taxonomy = ATBDP_CATEGORY;
                            } elseif ('location' == $taxonomy) {
                                $taxonomy = ATBDP_LOCATION;
                            } else {
                                $taxonomy = ATBDP_TAGS;
                            }
                            
                            $final_term = isset($post[$term]) ? $post[$term] : '';
                            $term_exists = get_term_by( 'name', $final_term, $taxonomy );
                            if ( ! $term_exists ) { // @codingStandardsIgnoreLine.
                                $result = wp_insert_term( $final_term, $taxonomy );
                                if( !is_wp_error( $result ) ){
                                    $term_id = $result['term_id'];
                                    wp_set_object_terms($post_id, $term_id, $taxonomy);
                                }
                            }else{
                                wp_set_object_terms($post_id, $term_exists->term_id, $taxonomy);
                            }
                        }
                    }

                    foreach ($metas as $index => $value) {
                        $meta_value = $post[$value] ? $post[$value] : '';
                        if($meta_value){
                            update_post_meta($post_id, $index, $meta_value);
                        }
                    }
                    $exp_dt = calc_listing_expiry_date();
                    update_post_meta($post_id, '_expiry_date', $exp_dt);
                    update_post_meta($post_id, '_featured', 0);
                    update_post_meta($post_id, '_listing_status', 'post_status');
                    $preview_url = isset($post[$preview_image]) ? $post[$preview_image] : '';

                    if ( $preview_url ) {
                       $attachment_id = self::atbdp_insert_attachment_from_url($preview_url, $post_id);
                       update_post_meta($post_id, '_listing_prv_img', $attachment_id);
                    }

                    $count++;
            }
            $data['next_position'] = (int) $position + (int) $count;
            $data['percentage']    = absint(min(round((($data['next_position']) / $total_length) * 100), 100));
            $data['url']           = admin_url('edit.php?post_type=at_biz_dir&page=tools&step=3');
            $data['total']         = $total_length;
            $data['imported']      = $imported;
            $data['failed']        = $failed;

            wp_send_json($data);
            die();
        }


       public static function atbdp_insert_attachment_from_url( $file_url ) {

        if (!filter_var($file_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $contents = @file_get_contents($file_url);
        if ($contents === false) {
            return false;
        }
        $upload = wp_upload_bits(basename($file_url), null, $contents);
        if (isset($upload['error']) && $upload['error']) {
            return false;
        }
        $type = '';
        if (!empty($upload['type'])) {
            $type = $upload['type'];
        } else {
            $mime = wp_check_filetype($upload['file']);
            if ($mime) {
                $type = $mime['type'];
            }
        }
        $attachment = array('post_title' => basename($upload['file']), 'post_content' => '', 'post_type' => 'attachment', 'post_mime_type' => $type, 'guid' => $upload['url']);
        $id = wp_insert_attachment($attachment, $upload['file']);
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
        return $id;
        
        }

        public function atbdp_csv_import_controller()
        {
            // Displaying this page triggers Ajax action to run the import with a valid nonce,
            // therefore this page needs to be nonce protected as well.
            // step one

            // $post = new WP_Query(array(
            //     'post_type' => ATBDP_POST_TYPE,
            //     'posts_per_page' => -1
            // ));
            // foreach ($post->posts as $post) {
            //     wp_delete_post($post->ID, true);
            // }

            if (isset($_POST['atbdp_save_csv_step'])) {
                check_admin_referer('directorist-csv-importer');
                // redirect to step two || data mapping
                $file = wp_import_handle_upload();
                $file = $file['file'];
                $url = admin_url() . "edit.php?post_type=at_biz_dir&page=tools&step=2";
                $params = array(
                    'step'            => 2,
                    'file'            => str_replace(DIRECTORY_SEPARATOR, '/', $file),
                    'delimiter'       => $this->delimiter,
                    'update_existing' => $this->update_existing,
                );
                wp_safe_redirect(add_query_arg($params, $url));
            }
        }


        private function importable_fields()
        {
            return apply_filters('atbdp_csv_listing_import_mapping_default_columns', array(
                'title'                   => __('Title', 'directorist'),
                'description'             => __('Description', 'directorist'),
                '_tagline'                => __('Tagline', 'directorist'),
                '_price'                  => __('Price', 'directorist'),
                '_price_range'            => __('Price Range', 'directorist'),
                '_atbdp_post_views_count' => __('View Count', 'directorist'),
                '_excerpt'                => __('Excerpt', 'directorist'),
                'location'                => __('Location', 'directorist'),
                'tag'                     => __('Tag', 'directorist'),
                'category'                => __('Category', 'directorist'),
                '_hide_contact_info'      => __('Hide Contact Info', 'directorist'),
                '_address'                => __('Address', 'directorist'),
                '_manual_lat'             => __('Latitude', 'directorist'),
                '_manual_lng'             => __('Longitude', 'directorist'),
                '_hide_map'               => __('Hide Map', 'directorist'),
                '_zip'                    => __('Zip/Post Code', 'directorist'),
                '_phone'                  => __('Phone', 'directorist'),
                '_phone2'                 => __('Phone Two', 'directorist'),
                '_fax'                    => __('Fax', 'directorist'),
                '_email'                  => __('Email', 'directorist'),
                '_website'                => __('Website', 'directorist'),
                '_listing_prv_img'        => __('Preview Image', 'directorist'),
                '_videourl'               => __('Video', 'directorist'),
                '_fm_plans'               => __('Pricing Plan (Requires Pricing Plan Extension)', 'directorist'),
                '_claimed_by_admin'       => __('Claimed (Requires Claim Listing Extension)', 'directorist'),
            ));
        }

        /**
         * It adds a submenu for showing all the Tools and details support
         */
        public function add_tools_submenu()
        {
            add_submenu_page(null,
            __('Tools', 'directorist'),
            __('Tools', 'directorist'),
            'manage_options',
            'tools',
            array($this, 'render_tools_submenu_page'));
        }

        public function render_tools_submenu_page()
        {
            ATBDP()->load_template('tools',  array('data' => csv_get_data($this->file, false, $this->delimiter), 'fields' => $this->importable_fields()));
        }
    }

endif;
