<?php
// GeoDirectory Plugin compatibility functions.

if (defined('GEODIRECTORY_VERSION') && version_compare(GEODIRECTORY_VERSION,'2.0.0','<')) {
    add_action( 'admin_notices', 'dt_gdv2_notice' );
}

/**
 * Warn about using GDv1 with DTv2
 */
function dt_gdv2_notice() {
    $class = 'notice notice-error';
    $message = __( 'Warning: Only GeoDirectory v2 should be used with the Directory Starter v2 theme.', 'directory-starter' );

    printf( '<div class="%1$s"><p><b>%2$s</b></p></div>', esc_attr( $class ), esc_html( $message ) );
}

// Actions to fire if GeoDirectory installed.
if (defined('GEODIRECTORY_VERSION') && version_compare(GEODIRECTORY_VERSION,'2.0.0','>')) {
    // Add mobile account menu
    add_action('dt_before_site_logo', 'dt_add_mobile_gd_account_menu');
}

// Change avatar size
function dt_comment_avatar_size()
{
    return 60;
}

add_filter('geodir_comment_avatar_size', 'dt_comment_avatar_size');

// Change bp integration avatar size
function dt_bp_comment_avatar_size()
{
    return 60;
}

add_filter('gdbuddypress_comment_avatar_size', 'dt_bp_comment_avatar_size');

// Change avatar size
function dt_geodir_buddypress_reviews_before_content()
{
    return '<div id="reviewsTab">';
}

add_filter('geodir_buddypress_reviews_before_content', 'dt_geodir_buddypress_reviews_before_content');

// Change avatar size
function dt_geodir_buddypress_reviews_after_content()
{
    return '</div>';
}

add_filter('geodir_buddypress_reviews_after_content', 'dt_geodir_buddypress_reviews_after_content');

function dt_geodir_reviews_g_size()
{
    return 60;
}

add_filter('geodir_recent_reviews_g_size', 'dt_geodir_reviews_g_size');

// If GeoDirectory Installed add account mobile menu

function dt_add_mobile_gd_account_menu()
{ ?>
    <div class="dt-mobile-account-wrap"><a href="#gd-account-nav"><i class="fas fa-user"></i></a></div>
    <div id="gd-account-nav" >
        <div >
            <?php

            if($user_id = get_current_user_id()){
                $my_dashbaord_text = __('My Dashboard','directory-starter');
                echo do_shortcode( '[gd_dashboard title="'.$my_dashbaord_text.'"]' );
                echo '<a href="'.wp_logout_url().'">'.__('Logout','directory-starter').'</a>';
            }else{

                wp_login_form();
                $login_link_separator = "|";

                ?>
                <br />
                <p id="nav">
                    <?php if ( ! isset( $_GET['checkemail'] ) || ! in_array( $_GET['checkemail'], array( 'confirm', 'newpass' ) ) ) :
                        if ( get_option( 'users_can_register' ) ) :
                            $registration_url = sprintf( '<a href="%s">%s</a>', esc_url( wp_registration_url() ), __( 'Register','directory-starter' ) );

                            /** This filter is documented in wp-includes/general-template.php */
                            echo apply_filters( 'register', $registration_url );

                            echo esc_html( $login_link_separator );
                        endif;
                        ?>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Lost your password?','directory-starter' ); ?></a>
                    <?php endif; ?>
                </p>
                <?php
            }

            ?>
        </div>
    </div>
<?php
}

function sd_gdbp_display_listing_link($comment) {
    printf( '<br/><a class="gdbp_display_listing_link" style="display: inline-block;margin-top: 12px;" href="%1$s">%2$s</a>', esc_url( get_comment_link( $comment->comment_ID )), get_the_title($comment->comment_post_ID));
}
add_action('gdbp_comment_meta_after', 'sd_gdbp_display_listing_link');