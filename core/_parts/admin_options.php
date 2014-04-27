<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** @var $bprf array */
$checked = 'checked="checked"';
?>

<style>
    .option_desc{
        margin: 0 0 10px 30px !important;
    }
</style>

<table class="form-table">
    <!-- RSS Feeds For -->
    <tr valign="top">
        <th scope="row"><?php _e('Enable custom RSS feeds for:', 'bprf'); ?></th>
        <td>
            <label>
                <input name="bprf[rss_for][]" type="checkbox" value="members" <?php echo in_array('members', $bprf['rss_for']) ? $checked: ''; ?>>&nbsp;
                <?php _e('Members', 'bprf'); ?>
            </label><br/>
            <label>
                <input name="bprf[rss_for][]" type="checkbox" value="groups" <?php echo in_array('groups', $bprf['rss_for']) ? $checked: ''; ?>>&nbsp;
                <?php _e('Groups', 'bprf'); ?>
            </label>

            <?php do_action('bprf_admin_option_rss_for', $bprf); ?>
        </td>
    </tr>

    <!-- Profile RSS Label -->
    <tr valign="top">
        <th scope="row"><label for="bprf_tabs_members"><?php _e('User profile RSS tab label', 'bprf'); ?></label></th>
        <td>
            <input name="bprf[tabs][members]" id="bprf_tabs_members" type="text" required="required" value="<?php esc_attr_e($bprf['tabs']['members']); ?>">
        </td>
    </tr>

    <!-- Groups RSS Label -->
    <tr valign="top">
        <th scope="row"><label for="bprf_tabs_groups"><?php _e('Groups RSS tab label', 'bprf'); ?></label></th>
        <td>
            <input name="bprf[tabs][groups]" id="bprf_tabs_groups" type="text" required="required" value="<?php esc_attr_e($bprf['tabs']['groups']); ?>">
        </td>
    </tr>

    <!-- RSS first iamge -->
    <tr valign="top">
        <th scope="row"><?php _e('RSS item first image', 'bprf'); ?></th>
        <td>
            <label>
                <input name="bprf[rss][image]" type="radio" value="display_local" <?php checked('display_local', $bprf['rss']['image']); ?>>&nbsp;
                <?php _e('Grab, save locally and display', 'bprf'); ?>
            </label>
            <p class="description option_desc">
                <?php _e('This will create a copy of an image on your server. <br/>
                            If that image is deleted from the RSS source site, you will still be able to display it.', 'bprf'); ?>
            </p>
            <label>
                <input name="bprf[rss][image]" type="radio" value="display_remote" <?php checked('display_remote', $bprf['rss']['image']); ?>>&nbsp;
                <?php _e('Display using hotlinking', 'bprf'); ?> <a href="http://en.wikipedia.org/wiki/Inline_linking" target="_blank" title="<?php _e('What is hotlinking?', 'bprf'); ?>">#</a>
            </label>
            <p class="description option_desc">
                <?php _e('Image won\'t be downloaded to your server, saving you some bandwith. <br/>
                            If on RSS source site the image is deleted, it won\'t be displayed on your site. <br/>
                            Generally it\'s a bad practice and you should avoid doing this, because you area creating a server load for external site.', 'bprf'); ?>
            </p>
            <label>
                <input name="bprf[rss][image]" type="radio" value="none" <?php checked('none', $bprf['rss']['image']); ?> />&nbsp;
                <?php _e('Do not display image', 'bprf'); ?>
            </label>
            <p class="description option_desc"><?php _e('Only RSS post title and excerpt will be displayed.', 'bprf'); ?></p>
        </td>
    </tr>

    <!-- RSS Excerpt Length -->
    <tr valign="top">
        <th scope="row"><label for="bprf_rss_excerpts_length"><?php _e('RSS posts excerpt length', 'bprf'); ?></label></th>
        <td>
            <input name="bprf[rss][excerpt]" id="bprf_rss_excerpts_length" type="text" required="required" value="<?php esc_attr_e($bprf['rss']['excerpt']); ?>"> <?php _e('words', 'bprf'); ?>
            <p class="description"><?php _e('Three dots <code>...</code> will be used to identify the end of excerpt.', 'bprf'); ?></p>
            <p class="description"><?php _e('Words will stay intact, sentences may be cut in the middle.', 'bprf'); ?></p>
        </td>
    </tr>

    <!-- Cron frequency -->
    <tr valign="top">
        <th scope="row"><label for="bprf_rss_frequency"><?php _e('RSS feeds update frequency', 'bprf'); ?></label></th>
        <td>
            <input name="bprf[rss][frequency]" id="bprf_rss_frequency" type="text" required="required" value="<?php esc_attr_e($bprf['rss']['frequency']); ?>"> <?php _e('seconds', 'bprf'); ?>
            <p class="description"><?php _e('This value defines how often you want the site to check users/groups RSS feeds for new posts.', 'bprf'); ?></p>
            <p class="description"><?php _e('For reference: the bigger time is specified, the less overhead will be on your site.', 'bprf'); ?></p>
            <p class="description"><?php _e('Recommended value: 43200 sec, or 12 hours.', 'bprf'); ?></p>
        </td>
    </tr>

    <?php do_action('bprf_admin_options', $bprf); ?>

    <!-- Deactivation -->
    <tr valign="top">
        <th scope="row">
            <?php _e('What to do on plugin deactivation?', 'bprf'); ?><br/><br/>
            <?php
            $data = bprf_get_count_folder_size();
            if( !empty($data) ) {
                printf( __( 'More than %s of files stored', 'bprf' ), $data );
            } ?>
        </th>
        <td>
            <label>
                <input name="bprf[uninstall]" type="radio" value="leave" <?php checked('leave', $bprf['uninstall']); ?>>&nbsp;
                <?php _e('Do not delete anything. Leave all the data and options in the DB', 'bprf'); ?>
            </label>
            <p class="description option_desc"><?php _e('Good option if you want to reactivate the plugin later.', 'bprf'); ?></p>
            <label>
                <input name="bprf[uninstall]" type="radio" value="data" <?php checked('data', $bprf['uninstall']); ?>>&nbsp;
                <?php _e('RSS data will be deleted, options (admin, users, groups) will be preserved', 'bprf'); ?>
            </label>
            <p class="description option_desc"><?php _e('If you want to cleanup the plugin\'s data, but preserve all settings - use this option.', 'bprf'); ?></p>
            <label>
                <input name="bprf[uninstall]" type="radio" value="all" <?php checked('all', $bprf['uninstall']); ?>>&nbsp;
                <?php _e('Completely delete all plugin-related data and options', 'bprf'); ?>
            </label>
            <p class="description option_desc"><?php _e('If you decided not to use this plugin, then check this option.', 'bprf'); ?></p>
        </td>
    </tr>

</table>