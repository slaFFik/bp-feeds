<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'bp_init', 'bprf_admin_init' );
function bprf_admin_init() {
    add_action( bp_core_admin_hook(), 'bprf_admin_page', 99 );
}

function bprf_admin_page() {
    if ( ! is_super_admin() )
        return;

    bprf_admin_page_save();

    add_submenu_page(
        bp_core_do_network_admin() ? 'settings.php' : 'options-general.php',
        __( 'BuddyPress RSS Feeds', 'bprf' ),
        __( 'BP RSS Feeds', 'bprf' ),
        'manage_options',
        'bprf-admin',
        'bprf_admin_page_content'
    );
}


function bprf_admin_page_content() {
    $bprf = bp_get_option( 'bprf' ); ?>

    <div class="wrap">

        <h2><?php _e( 'BuddyPress RSS Feeds', 'bprf' ); ?> <sup>v<?php echo BPRF_VERSION ?></sup></h2>

        <?php
        if( isset($_GET['status']) && $_GET['status'] == 'saved') {
            echo '<div id="message" class="updated fade"><p>' . __('All options were successfully saved.', 'bprf') . '</p></div>';
        }
        ?>

        <form action="" method="post" id="bprf-admin-form">

            <p><?php _e( 'Below are several options that you can use to change the plugin behaviour.', 'bprf' ); ?></p>

            <?php
            bprf_the_template_part('admin_options', array(
                'bprf' => $bprf
            )); ?>

            <p class="submit">
                <input class="button-primary" type="submit" name="bprf-admin-submit" id="bprf-admin-submit" value="<?php esc_attr_e( 'Save', 'bprf' ); ?>" />
            </p>

            <?php wp_nonce_field( 'bprf-admin' ); ?>

        </form><!-- #bprf-admin-form -->
    </div><!-- .wrap -->
<?php
}

function bprf_admin_page_save(){

    if( isset( $_POST['bprf-admin-submit'] ) && isset( $_POST['bprf'] ) ) {
        $bprf = $_POST['bprf'];

        if ( !isset($bprf['rss_for']) ) {
            $bprf['rss_for'] = array();
        }

        $bprf['tabs']['members']  = trim(htmlentities(wp_strip_all_tags($bprf['tabs']['members'])));
        if ( empty($bprf['tabs']['members']) ) {
            $bprf['tabs']['members'] = __('RSS Feed', 'bprf');
        }

        $bprf['tabs']['groups']  = trim(htmlentities(wp_strip_all_tags($bprf['tabs']['groups'])));
        if ( empty($bprf['tabs']['groups']) ) {
            $bprf['tabs']['groups'] = __('RSS Feed', 'bprf');
        }

        $bprf['rss']['placeholder']  = trim(htmlentities(wp_strip_all_tags($bprf['rss']['placeholder'])));
        if ( empty($bprf['rss']['placeholder']) ) {
            $bprf['rss']['placeholder'] = 'http://buddypress.org/blog/feed';
        }

        $bprf['rss']['excerpt']   = (int) $bprf['rss']['excerpt'];
        if ( empty($bprf['rss']['excerpt']) ) {
            $bprf['rss']['excerpt'] = '45';
        }

        $bprf['rss']['posts']   = (int) $bprf['rss']['posts'];
        if ( empty($bprf['rss']['posts']) ) {
            $bprf['rss']['posts'] = '5';
        }

        $bprf['rss']['frequency'] = (int) $bprf['rss']['frequency'];
        if ( empty($bprf['rss']['frequency']) ) {
            $bprf['rss']['frequency'] = '43200';
        }

        $bprf = apply_filters('bprf_admin_page_save', $bprf);

        bp_update_option('bprf', $bprf);

        wp_redirect( add_query_arg( 'status', 'saved' ) );
    }

    return false;
}
