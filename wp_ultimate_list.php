<?php
/*
  Plugin Name: WP Ultimate List
  Plugin URI: http://www.intensewp.com/wp-ultimate-list/
  Description: Create unlimited subscriber lists and send to the autoresponders on the fly.
  Author: Intense WP
  Version: 1.0
  Author URI: http://www.intensewp.com/
  Text Domain: wpulist
  Domain Path: /languages
 */

// Some important variables
$wpul_page = sanitize_text_field($_REQUEST['page']);
$wpul_do = sanitize_key($_POST['do']);

function wpulist_activation() {
    global $wpdb, $wpulist_tbl_emails, $wpulist_tbl_1, $wpulist_tbl_lists, $wpulist_tbl_2;

// Add options for plugin
// Add mailing list services

    $ML_Vendors = array(
        1 => 'Select Service',
        2 => 'MailChimp',
        3 => 'Aweber',
        4 => 'GetResponse',
    );
    update_option('wpulist_list_managers', serialize($ML_Vendors));
    $aw_link = plugins_url("/integration/aweber/get_access_tokens.php", __FILE__);

    $vr_redirect_link = plugins_url("/integration/verticalresponse/get_access_token.php", __FILE__);
    //change client_id to plugin client_id
    $vr_link = 'https://vrapi.verticalresponse.com/api/v1/oauth/authorize?client_id=xf7y8f7zc747brkvgctgddda&redirect_uri=' . $vr_redirect_link;
    //set access_link and help link.
    $ML_Vendors_details = array(
        1 => 'Select Service',
        2 => 'Get MailChimp API Key|https://admin.mailchimp.com|http://www.intensewp.com/kb/wp-ultimate-list/integrate-mailchimp-wp-ultimate-list/',
        3 => 'Get access token|' . $aw_link . '|http://www.intensewp.com/kb/wp-ultimate-list/integrate-aweber-wp-ultimate-list/',
        4 => 'Get API Key|https://www.getresponse.com|http://www.intensewp.com/kb/wp-ultimate-list/integrate-getresponse-wp-ultimate-list/',
        6 => 'Get access token|https://api.constantcontact.com/mashery/account/dmsav862cjuqendzzuey4p3e|http://www.intensewp.com/kb/wp-ultimate-list/integrate-constantcontact-wp-ultimate-list/',
        8 => 'Get API Key|https://madmimi.com/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-madmimi-wp-ultimate-list/',
        9 => 'Get access token|' . $vr_link . '|http://www.intensewp.com/kb/wp-ultimate-list/integrate-verticalresponse-wp-ultimate-list/',
        10 => 'Get API Key|https://app.freshmail.com/en/settings/integration/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-freshmail-wp-ultimate-list/',
        11 => 'Get API Key|http://app.vision6.com/integration/api_keys/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-vision6-wp-ultimate-list/',
        12 => 'Get API Key|https://fly.customer.io/account/customerio_integration|http://www.intensewp.com/kb/wp-ultimate-list/integrate-customer-io-wp-ultimate-list/',
        13 => 'Get API Key|https://account.sendinblue.com/advanced/api|http://www.intensewp.com/kb/wp-ultimate-list/integrate-sendinblue-wp-ultimate-list/',
        14 => 'Get API Key|https://app.sendgrid.com/settings/api_keys|http://www.intensewp.com/kb/wp-ultimate-list/integrate-sendgrid-wp-ultimate-list/',
        15 => 'Create API User|https://www.dotmailer.com/apac/|https://developer.dotmailer.com/docs/getting-started-with-the-api#section-setting-up-your-api-user',
        // 16 => 'BenchMarkEmail',
        17 => 'Get API Key|https://vivid16.createsend.com/account/apikeys|http://www.intensewp.com/kb/wp-ultimate-list/integrate-campaign-monitor-wp-ultimate-list/',
        18 => 'Get API Key|https://intensewp.activehosted.com/admin/main.php?action=settings#tab_api|http://www.intensewp.com/kb/wp-ultimate-list/integrate-active-campaign-wp-ultimate-list/',
        19 => 'none|https://www.icontact.com/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-icontact-wp-ultimate-list/',
        20 => 'Get API Key|https://www.infusionsoft.com/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-infusionsoft-wp-ultimate-list/',
        21 => 'Get API Key|https://app.hubspot.com/hapikeys/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-hubspot-wp-ultimate-list/',
        22 => '|http://www.sugarcrm.com/download|http://www.intensewp.com/kb/wp-ultimate-list/integrate-sugarcrm-wp-ultimate-list/',
        23 => 'Login|https://login.salesforce.com/|http://www.intensewp.com/kb/wp-ultimate-list/integrate-salesforce-wp-ultimate-list/',
    );
    update_option('wpulist_list_managers_details', serialize($ML_Vendors_details));

// call table creation process
    $wpulist_tbl_emails = $wpdb->prefix . "wpulist_emails";
    $wpulist_tbl_lists = $wpdb->prefix . "wpulist_emails_lists";
    $wpulist_tbl_forms = $wpdb->prefix . "wpulist_emails_forms";
    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $wpulist_tbl_1 = "CREATE TABLE IF NOT EXISTS `" . $wpulist_tbl_emails . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_address` varchar(255) NOT NULL,
  `email_list_id` int(11) NOT NULL,
  `email_usr_name` text NOT NULL,
  `email_frm_id` int(11) NOT NULL,
  `usr_ip` varchar(20) NOT NULL,
  `status` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY id (id)
) $charset_collate;";
    dbDelta($wpulist_tbl_1);

    $wpulist_tbl_2 = "CREATE TABLE IF NOT EXISTS `" . $wpulist_tbl_lists . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_list_name` varchar(255) DEFAULT NULL,
  `email_list_desc` text,
  `status` int(11) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY id (id)
  ) $charset_collate;";

    dbDelta($wpulist_tbl_2);

    // call mailing list check function
    wpulist_tbl_activation();


// Add aweber consumer key and secret in options table

    add_option('aweber_consumerKey', 'AkZGcUtTCntHtN6wdC0uHWGT');
    add_option('aweber_consumerSecret', 'mfrSfXBFGUhupexEmr8sZxfcfYO38omprwuLfCaG');
    $aweber_data = array(
        'consumerKey' => get_option('aweber_consumerKey'),
        'consumerSecret' => get_option('aweber_consumerSecret'),
    );

    add_option('wpulist_api_aweber', serialize($aweber_data));

    $wpulist_settings = array(
        'enable_geo_ip' => 1,
    );
    add_option('wpulist_settings', serialize($wpulist_settings));
}

// Create tables on activation
function wpulist_tbl_activation() {
    global $wpdb;

    // check for existing defult mailing list
    $wpulist_sql_check_entry = "SELECT email_list_name FROM " . $wpdb->prefix . "wpulist_emails_lists WHERE email_list_name = 'Default Mailing List' ";
    $wpulist_row_check = $wpdb->get_row($wpulist_sql_check_entry);

    if (!$wpulist_row_check) {
        // add default mailing list on activation
        $wpulist_insert_default_list = " INSERT INTO " . $wpdb->prefix . "wpulist_emails_lists (email_list_name,status, date_added) VALUES ('Default Mailing List','1',now())";
        $wpulist_insert_default = $wpdb->query($wpulist_insert_default_list);
    }

    // check for existing default email address
    $wpulist_sqlcheck_email = "Select email_address from " . $wpdb->prefix . "wpulist_emails where email_address = 'sales@intensewp.com' ";
    $wpulist_email_check = $wpdb->get_row($wpulist_sqlcheck_email);

    if (!$wpulist_email_check) {
        // add default email address on activation
        $wpulist_insert_default_email = " INSERT INTO " . $wpdb->prefix . "wpulist_emails (email_address, email_usr_name, status, date_added) VALUES ('sales@intensewp.com', 'IntenseWP', '1',now())";
        $wpulist_insert_defemail = $wpdb->query($wpulist_insert_default_email);
    }
}

register_activation_hook(__FILE__, 'wpulist_activation');

function wpulist_admin_menu() {

    $menu_head_name = 'WP Ultimate List';
    add_menu_page(__('WP Ultimate List Dashboard', 'wpulist'), __('WP Ultimate List', 'wpulist'), 'manage_options', "wpulist_home", 'wpulist_dashboard');
    add_submenu_page("wpulist_home", __('WP Ultimate List - Mailing List Manager (Local)', 'wpulist'), __('Local Mailing Lists', 'wpulist'), 'manage_options', "wpulist_mailing_lists", 'wpulist_local_lists');
    add_submenu_page("wpulist_home", __('WP Ultimate List - Mailing List Manager (3rd Party)', 'wpulist'), __('3rd Party Integration', 'wpulist'), 'manage_options', "wpulist_mailing_lists_integration", 'wpulist_list_integration_page');
    add_submenu_page("wpulist_home", __('WP Ultimate List - Subscribers', 'wpulist'), __('Subscribers', 'wpulist'), 'manage_options', "wpulist_subscribers", 'wpulist_list_subscribers');
    add_submenu_page("wpulist_home", __('WP Ultimate List - Pro', 'wpulist'), __('WP Ultimate Pro', 'wpulist'), 'manage_options', "wpulist_pro", 'wpulist_pro_page');
    add_submenu_page("null", __('WP Ultimate List - Set up Aweber', 'wpulist'), __('Aweber', 'wpulist'), 'manage_options', "wpulist_setup_aweber", 'wpulist_aweber_setup');
    add_submenu_page("null", __('WP Ultimate List - Debug Mode', 'wpulist'), __('Debug', 'wpulist'), 'manage_options', "wpulist_debug", 'wpulist_debug_page');
}

add_action('admin_menu', 'wpulist_admin_menu');

function wpulist_dashboard() {
    global $wpdb, $ML_Vendors;

    include 'wpulist_dashboard.php';
}

function wpulist_local_lists() {
    global $wpdb;
    include 'wpulist_local_lists.php';
}

function wpulist_pro_page() {
    global $wpdb;
    #include 'wpulist_pro.php';
    wp_redirect('https://goo.gl/J6DXR8');
}

function wpulist_list_integration_page() {
    global $wpdb;
    include 'wpulist_lists_integration.php';
}

function wpulist_aweber_setup() {
    global $wpdb;
    include 'integration/aweber/aweber_setup.php';
}

function wpulist_list_subscribers() {
    global $wpdb;
    include 'wpulist_subscribers_local.php';
}

function wpulist_debug_page() {
    global $wpdb;

    include 'wpulist_debug.php';
}

// load scripts for public pages
function wpulist_load_frot_scripts() {
    wp_enqueue_script('jquery');
}

add_action('enqueue_scripts', 'wpulist_load_frot_scripts');

// Register scripts for admin

function wpulist_admin_scripts() {

    wp_register_style('wpmulti_styles_bootstrap', plugins_url('/css/bootstrap.min.css', __FILE__), false, '1.0.0');
    wp_register_style('wpmulti_styles_bootstrap_theme', plugins_url('/css/bootstrap-theme.min.css', __FILE__), false, '1.0.0');
    wp_register_script('wpmulti_scripts_bootstrap', plugins_url('/js/bootstrap3.min.js', __FILE__), '', '1.0', true);
    wp_register_script('wpmulti_scripts_custom', plugins_url('/js/wpulist_scripts.js', __FILE__), '', time(), true);

    wp_enqueue_style('wpmulti_styles_bootstrap_theme');
    wp_enqueue_script('wpmulti_scripts_bootstrap');
    wp_enqueue_style('wpmulti_styles_bootstrap');
    wp_enqueue_script('wpmulti_scripts_custom');
}

if (isset($wpul_page) && ($wpul_page == 'wpulist_mailing_lists' || $wpul_page == 'wpulist_mailing_lists') || $wpul_page == 'wpulist_mailing_lists_integration') {
    add_action('admin_enqueue_scripts', 'wpulist_admin_scripts');
}

function wpulist_styles() {
    wp_enqueue_style('wpul-default', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_style('wpul-default');
}

add_action('admin_enqueue_scripts', 'wpulist_styles');

function wpulist_save_error() {
    file_put_contents(dirname(__file__) . '/wpulist_error_activation.txt', ob_get_contents());
}

add_action('activated_plugin', 'wpulist_save_error');

// Register Custom Post Type
function wpulist_reg_post_type() {

    $labels = array(
        'name' => _x('Forms', 'Post Type General Name', 'wpulist'),
        'singular_name' => _x('Form', 'Post Type Singular Name', 'wpulist'),
        'menu_name' => __('Forms', 'wpulist'),
        'name_admin_bar' => __('Form', 'wpulist'),
        'archives' => __('Form Archives', 'wpulist'),
        'parent_item_colon' => __('Parent Form:', 'wpulist'),
        'all_items' => __('All Forms', 'wpulist'),
        'add_new_item' => __('Add New Form', 'wpulist'),
        'add_new' => __('Add New', 'wpulist'),
        'new_item' => __('New Form', 'wpulist'),
        'edit_item' => __('Edit Form', 'wpulist'),
        'update_item' => __('Update Form', 'wpulist'),
        'view_item' => __('View Form', 'wpulist'),
        'search_items' => __('Search Form', 'wpulist'),
        'not_found' => __('Not found', 'wpulist'),
        'not_found_in_trash' => __('Not found in Trash', 'wpulist'),
        'featured_image' => __('Featured Image', 'wpulist'),
        'set_featured_image' => __('Set featured image', 'wpulist'),
        'remove_featured_image' => __('Remove featured image', 'wpulist'),
        'use_featured_image' => __('Use as featured image', 'wpulist'),
        'insert_into_item' => __('Insert into form', 'wpulist'),
        'uploaded_to_this_item' => __('Uploaded to this form', 'wpulist'),
        'items_list' => __('Forms list', 'wpulist'),
        'items_list_navigation' => __('Forms list navigation', 'wpulist'),
        'filter_items_list' => __('Filter forms list', 'wpulist'),
    );
    $args = array(
        'label' => __('Form', 'wpulist'),
        'description' => __('WPulist Forms', 'wpulist'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'author', 'revisions',),
        'hierarchical' => false,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'capability_type' => 'page',
    );
    register_post_type('wpulistform', $args);
}

add_action('init', 'wpulist_reg_post_type', 0);

add_action('edit_form_after_title', 'wpulist_editor_label');

function wpulist_editor_label() {
    if (isset($post) && get_post_type($post) == 'wpulistform') :
        echo '<h1>' . __('Your Form Body Text', 'wpulist') . '</h1>';
    endif;
}

// Add metabox file - Form detail
include plugin_dir_path(__FILE__) . '/wpulist_metabox_frm_detail.php';

add_shortcode('wpulist_forms', 'wpulist_generate_frm_tpl');

function wpulist_generate_frm_tpl($atts) {
    global $wpdb;

    include 'templates_list.php';

    $frm = shortcode_atts(array(
        'frm_id' => '',
        'tpl_id' => '2',
            ), $atts);

    $frm_info = get_post($frm['frm_id']);
    $wpulist_frm_items = get_post_meta($frm['frm_id'], 'wpulist_frm_items', true);

    $tpl_info = explode('|', $tpl_list[$frm['tpl_id']]);
    ob_start();

    include 'themes/' . $tpl_info[0];

    $sc = ob_get_contents();

    /* Clean buffer */
    ob_end_clean();

    /* Return the content as usual */
    return $sc;
}

function wpulist_start_session() {
    if (!session_id()) {
        session_start();
    }
}

function wpulist_generate_frm_fields($frm_id, $wpulist_frm_items, $email_only = 1, $css_name = '', $css_email = '') {
    $frm_val = '';
    if (isset($css_name)) {
        $css_name = $css_name;
    }
    if (isset($css_email)) {
        $css_email = $css_email;
    }
    if ($email_only == 1) {
        if ($wpulist_frm_items['fld_name'] == 1) {
            $frm_val = '<input type="text" name = "name_' . $frm_id . '" id="name_' . $frm_id . '" placeholder="' . __('Name', 'wpulist') . '" class="' . $css_name . '"  />';
        }
    }

    $frm_val .= '<input type="email" name = "email_' . $frm_id . '" id="email_' . $frm_id . '" required placeholder = "' . __('Email', 'wpulist') . '" class="' . $css_email . '" />';

    $frm_val .= '<input type="hidden" name="action" value="wpulist_process_frm" />';
    $frm_val .= '<input type="hidden" name="wpul_frm_id" value="' . $frm_id . '">';
    $frm_val .= wp_nonce_field('chk_nonce_' . $frm_id, 'wpulist_frm_' . $frm_id);
    return $frm_val;
}

// Enable shortcodes in text widgets
add_filter('widget_text', 'do_shortcode');

// Do form view count
function wpulist_setFormViews($postID) {
    $count_key = 'wpulist_frm_views';
    $count = get_post_meta($postID, $count_key, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    } else {
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

// Admin Forms columns

add_filter('manage_wpulistform_posts_columns', 'wpulist_form_columns'); //landing-page

function wpulist_form_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'wpulist_screen_shot' => __('Screenshot', 'wpulist'),
        'title' => __('Title', 'wpulist'),
        'wpulist_form_views' => __('Views', 'wpulist'),
        'wpulist_form_conv' => __('Conversions', 'wpulist'),
        'wpulist_form_conv_rate' => __('Conversion Rate', 'wpulist'),
        'date' => __('Date', 'wpulist'),
    );
}

// Show form column data
add_action('manage_wpulistform_posts_custom_column', 'wpulist_form_columns_data', 10, 2);

function wpulist_form_columns_data($column, $post_id) {
    global $wpdb;
    include plugin_dir_path(__FILE__) . '/templates_list.php';
    $wpulist_frm_items = get_post_meta($post_id, 'wpulist_frm_items', true);
    switch ($column) {

        case 'wpulist_form_views' :
            $wpul_count = get_post_meta($post_id, 'wpulist_frm_views', true);

            if ($wpul_count) {
                if ($wpul_count > 1) {
                    $wpul_view_txt = __('Views', 'wpulist');
                } else {
                    $wpul_view_txt = __('View', 'wpulist');
                }
                echo $wpul_count . ' ' . $wpul_view_txt;
            } else {
                _e('0 View', 'wpulist');
            }
            break;

        case 'wpulist_screen_shot' :
            $tpl_details = explode("|", $tpl_list[$wpulist_frm_items['tpl_id']]);
            #print_r($tpl_list);
            ?>
            <img src="<?php echo plugin_dir_url(__FILE__) . "/themes/" . $tpl_details[2]; ?>" style=" <?php if (!$tpl_details[2]) { ?>display:none;<?php } ?> max-width:100%;" id="theme_screenshot" />
            <?php
            break;

        case 'wpulist_form_conv' :
            $wpulist_sql_get_conv = "SELECT COUNT(email_frm_id) AS conv_num_forms FROM " . $wpdb->prefix . "wpulist_emails WHERE email_frm_id =  " . (int) $post_id;
            $wpulist_row_conv = $wpdb->get_row($wpulist_sql_get_conv);
            $GLOBALS['wpulist_num_conv'] = $wpulist_row_conv->conv_num_forms;
            if ($GLOBALS['wpulist_num_conv']) {
                if ($GLOBALS['wpulist_num_conv'] > 1) {
                    $wpul_conv_txt = __('Conversions', 'wpulist');
                } else {
                    $wpul_conv_txt = __('Conversion', 'wpulist');
                }

                echo '<a href=admin.php?page=wpulist_subscribers&frm=' . $post_id . '>' . $GLOBALS['wpulist_num_conv'] . ' ' . $wpul_conv_txt . '</a>';
            } else {
                _e('0 Conversion', 'wpulist');
            }
            break;

        case 'wpulist_form_conv_rate' :
            $wpul_count = get_post_meta($post_id, 'wpulist_frm_views', true);
            if ($wpul_count <> '' && $wpul_count > 0) {
                $wpul_calc_conv = $GLOBALS['wpulist_num_conv'] / $wpul_count * 100;
                echo number_format($wpul_calc_conv, 2) . '%';
            } else {
                echo '0.00%';
            }
            break;
    }
}

add_action('wp_ajax_wpul_process_ajax_stng', 'wpulist_process_ajx_settings');

// function to add local mailing list. It is called inside the ajax process function.
function wpulist_process_ajx_settings() {
    global $wpdb;
    $result = array(
        'ui' => '',
        'success' => ''
    );

    if (isset($wpul_do)) {
        $do = $wpul_do;
    }

    if ($do == 'update_mailing_list') {
        $_list_name = sanitize_text_field($_REQUEST['mailing_list_name']);

        $wpul_sql_save_ml = "INSERT INTO " . $wpdb->prefix . "wpulist_emails_lists (email_list_name,status, date_added) VALUES (%s,%s,%s) ";
        $wpdb->query($wpdb->prepare($wpul_sql_save_ml, array($_list_name, '1', date('Y-m-d'))));
        $id = $wpdb->insert_id;
        $result['ui'] = '';
        $result['ui'] .= '<tr id="mail_list_' . $id . '">';
        $result['ui'] .= '<td width="80%">' . $_list_name . '</td>';
        $result['ui'] .= '<td width="10%"><a id="p_delete" class="button" href="javascript:wpmulti_delete_email_list(\'' . $id . '\',\'' . __('Are you sure? This action cannot be undone!', 'wpulist') . '\');">' . __('Delete', 'wpulist') . '</a></td>';
        $result['ui'] .= '</tr>';
    }

    if ($do == 'delete_mailing_list') {
        $_list_id = sanitize_key($_REQUEST['mail_id']);

        $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "wpulist_emails_lists where id=" . (int) $_list_id));
        $result['ui'] = $_list_id;
    }

    echo json_encode($result);

    die();
}

include plugin_dir_path(__FILE__) . 'functions_integration.php';
include 'wpulist_store_data_api_service.php';
require_once (plugin_dir_path(__FILE__) . 'integration/functions.php');

function wpulist_get_ip_location($ip) {
    $url = 'http://freegeoip.net/json/' . $ip;

    $ip_response = wp_remote_get($url);
    $ip_body = json_decode(wp_remote_retrieve_body($ip_response), true);

// Will dump a beauty json :3
    return json_decode($ip_body, true);
}

// clean array data for subscriber export
function wpulist_cleanData(&$str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"'))
        $str = '"' . str_replace('"', '""', $str) . '"';
}

// generate excel and download with subscribers data
function wpulist_export_subscribers2($frm_id = false) {
    global $wpdb;

    $filename = "website_data_sadf.xls";

    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: application/vnd.ms-excel");

    $flag = false;

    $wpulist_sql_subscribers = "SELECT id, email_address, email_usr_name, email_frm_id, date_added, usr_ip FROM " . $wpdb->prefix . "wpulist_emails ";
    if ($frm_id) {
        $wpulist_sql_subscribers .= " WHERE email_frm_id = " . (int) $frm_id;
    }

    $wpulist_rows_subscribers = $wpdb->get_results($wpulist_sql_subscribers);
    if ($wpulist_rows_subscribers) {
        foreach ($wpulist_rows_subscribers as $subinfo) {
            echo implode("\t", array_keys($subinfo)) . "\r\n";
            $flag = true;
            array_walk($subinfo, __NAMESPACE__ . '\wpulist_cleanData');
            echo implode("\t", array_values($subinfo)) . "\r\n";
        }
    }
}

function wpulist_export_subscribers($frm_id = false) {


// create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

// output the column headings
    fputcsv($output, array('ID', 'Email Address', 'Name', 'Form ID', 'Date Added'));

// fetch the data
    $wpulist_sql_subscribers = "SELECT id, email_address, email_usr_name, email_frm_id, date_added FROM " . $wpdb->prefix . "wpulist_emails ";
    if ($frm_id) {
        $wpulist_sql_subscribers .= " WHERE email_frm_id = " . (int) $frm_id;
    }

    if ($wpulist_rows_subscribers) {
        foreach ($wpulist_rows_subscribers as $subinfo) {
            fputcsv($output, $subinfo);
        }
    }
    fclose($output);
    // output headers so that the file is downloaded rather than displayed
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=data.csv');
    exit();
}

function wpulist_export_excel_subscribers($wpulist_settings) {
    ob_end_clean();
    global $wpdb;
    $export_file = "subscriber_list";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=' . $export_file . '.xls');
    echo __('Name', 'wpulist') . "\t" . __('Email', 'wpulist') . "\t" . __('Form', 'wpulist');
    if ($wpulist_settings['enable_geo_ip'] == 1) {
        echo "\t" . __('Location', 'wpulist');
    }
    echo "\t" . __('Name', 'wpulist') . "\r\n";

    $wpulist_sql_subscribers = "SELECT id, email_address, email_usr_name, email_frm_id, date_added, usr_ip FROM " . $wpdb->prefix . "wpulist_emails ";
    if ($frm_id) {
        $wpulist_sql_subscribers .= " WHERE email_frm_id = " . (int) $frm_id;
    }
    if (isset($num_list)) {
        $wpulist_sql_subscribers .= " LIMIT  $start, $num_list";
    }
    $wpulist_rows_subscribers = $wpdb->get_results($wpulist_sql_subscribers);
    $c = true;
    if ($wpulist_rows_subscribers) {
        foreach ($wpulist_rows_subscribers as $subinfo) {
            echo esc_html($subinfo->email_usr_name) . "\t" . esc_html($subinfo->email_address);
            if ($subinfo->email_frm_id != '' OR $subinfo->email_frm_id >= 1) {
                $form_data = get_post($subinfo->email_frm_id);
                echo "\t" . esc_html($form_data->post_title);
            }

            if ($wpulist_settings['enable_geo_ip'] == 1) {
                $geo_ip_data = wpulist_get_ip_location($subinfo->usr_ip);
                if ($geo_ip_data) {
                    if ($geo_ip_data['city']) {
                        $output_location = $geo_ip_data['city'] . ', ';
                    }
                    if ($geo_ip_data['region_name']) {
                        $output_location = $output_location . $geo_ip_data['region_name'] . ', ';
                    }
                    if ($geo_ip_data['region_name']) {
                        $output_location = $output_location . $geo_ip_data['country_name'];
                    }
                    echo "\t" . $output_location;
                }
            }
            $sub_date = $subinfo->date_added;
            $date = date_create($sub_date);
            echo "\t" . date_format($date, get_option('date_format')) . "\r\n";
        }
    }
    exit;
}

// get form image from post_meta
function wpulist_form_image($post_id, $default_image) {

    $form_image = get_post_meta($post_id, 'wpulist_upload_img', true);

    if ($form_image == null OR '') {
        return $default_image;
    } else {
        return $form_image;
    }
}
