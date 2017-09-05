<?php
/*
  Plugin Name: Sell Media File
  Version: 1.0.6
  Plugin URI: http://noorsplugin.com/sell-media-file-plugin-for-wordpress/
  Author: naa986
  Author URI: http://noorsplugin.com/
  Description: Sell media file in WordPress
  Text Domain: sell-media-file
  Domain Path: /languages
 */

if (!defined('ABSPATH'))
    exit;

class SELL_MEDIA_FILE {
    
    var $plugin_version = '1.0.6';
    var $plugin_url;
    var $plugin_path;
    
    function __construct() {
        define('SELL_MEDIA_FILE_VERSION', $this->plugin_version);
        define('SELL_MEDIA_FILE_SITE_URL', site_url());
        define('SELL_MEDIA_FILE_HOME_URL', home_url());
        define('SELL_MEDIA_FILE_URL', $this->plugin_url());
        define('SELL_MEDIA_FILE_PATH', $this->plugin_path());
        $options = sell_media_file_get_option();
        if (isset($options['enable_debug']) && $options['enable_debug']=="1") {
            define('SELL_MEDIA_FILE_DEBUG', true);
        } else {
            define('SELL_MEDIA_FILE_DEBUG', false);
        }
        if (isset($options['stripe_testmode']) && $options['stripe_testmode']=="1") {
            define('SELL_MEDIA_FILE_STRIPE_TESTMODE', true);
        } else {
            define('SELL_MEDIA_FILE_STRIPE_TESTMODE', false);
        }
        define('SELL_MEDIA_FILE_DEBUG_LOG_PATH', $this->debug_log_path());
        $this->plugin_includes();
        $this->loader_operations();
    }

    function plugin_includes() {
        include_once('sell-media-file-order.php');
        include_once('sell-media-file-process.php');
    }

    function loader_operations() {
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
        if (is_admin()) {
            add_filter('plugin_action_links', array($this, 'add_plugin_action_links'), 10, 2);
        }
        add_action('admin_notices', array($this, 'admin_notice'));
        add_action('wp_enqueue_scripts', array($this, 'plugin_scripts'));
        add_action('admin_menu', array($this, 'add_options_menu'));
        add_action('init', array($this, 'plugin_init'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_filter('manage_sellmediafile_order_posts_columns', 'sell_media_file_order_columns');
        add_action('manage_sellmediafile_order_posts_custom_column', 'sell_media_file_custom_column', 10, 2);
        add_shortcode('sell_media_file', 'sell_media_file_button_handler');
    }

    function plugins_loaded_handler() {  //Runs when plugins_loaded action gets fired
        load_plugin_textdomain( 'sell-media-file', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }

    function admin_notice() {
        if (SELL_MEDIA_FILE_DEBUG) {  //debug is enabled. Check to make sure log file is writable
            $real_file = SELL_MEDIA_FILE_DEBUG_LOG_PATH;
            if (!is_writeable($real_file)) {
                echo '<div class="updated"><p>' . __('Sell Media File Debug log file is not writable. Please check to make sure that it has the correct file permission (ideally 644). Otherwise the plugin will not be able to write to the log file. The log file (log.txt) can be found in the root directory of the plugin - ', 'sell-media-file') . '<code>' . SELL_MEDIA_FILE_URL . '</code></p></div>';
            }
        }
    }

    function plugin_init() {
        //register orders
        sell_media_file_order_page();
        //process PayPal IPN
        sell_media_file_process_ipn();
    }

    function add_meta_boxes() {
        //add_meta_box('sell-media-file-order-box', __('Edit PayPal Order', 'sell-media-file'), 'sell_media_file_order_meta_box', 'sell_media_file_order', 'normal', 'high');
    }

    function plugin_scripts() {
        if (!is_admin()) {
            
        }
    }

    function plugin_url() {
        if ($this->plugin_url)
            return $this->plugin_url;
        return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
    }

    function plugin_path() {
        if ($this->plugin_path)
            return $this->plugin_path;
        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }

    function debug_log_path() {
        return SELL_MEDIA_FILE_PATH . '/log.txt';
    }

    function add_plugin_action_links($links, $file) {
        if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
            $links[] = '<a href="options-general.php?page=sell-media-file-settings">'.__('Settings', 'sell-media-file').'</a>';
        }
        return $links;
    }

    function add_options_menu() {
        if (is_admin()) {
            add_submenu_page('edit.php?post_type=sellmediafile_order', __('Settings', 'sell-media-file'), __('Settings', 'sell-media-file'), 'manage_options', 'sell-media-file-settings', array($this, 'options_page'));
            add_submenu_page('edit.php?post_type=sellmediafile_order', __('Debug', 'sell-media-file'), __('Debug', 'sell-media-file'), 'manage_options', 'sell-media-file-debug', array($this, 'debug_page'));
        }
    }

    function options_page() {
        $plugin_tabs = array(
            'sell-media-file-settings' => __('General', 'sell-media-file')
        );
        echo '<div class="wrap">' . screen_icon() . '<h2>'.__('Sell Media File', 'sell-media-file').' v' . SELL_MEDIA_FILE_VERSION . '</h2>';
        $url = 'http://noorsplugin.com/sell-media-file-plugin-for-wordpress/';
        $link_msg = sprintf( wp_kses( __( 'Please visit the <a target="_blank" href="%s">Sell Media File</a> documentation page for usage instructions.', 'sell-media-file' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $url ) );
        echo '<div class="update-nag">'.$link_msg.'</div>';
        echo '<div id="poststuff"><div id="post-body">';

        if (isset($_GET['page'])) {
            $current = $_GET['page'];
            if (isset($_GET['action'])) {
                $current .= "&action=" . $_GET['action'];
            }
        }
        $content = '';
        $content .= '<h2 class="nav-tab-wrapper">';
        foreach ($plugin_tabs as $location => $tabname) {
            if ($current == $location) {
                $class = ' nav-tab-active';
            } else {
                $class = '';
            }
            $content .= '<a class="nav-tab' . $class . '" href="?post_type=sellmediafile_order&page=' . $location . '">' . $tabname . '</a>';
        }
        $content .= '</h2>';
        echo $content;

        $this->general_settings();

        echo '</div></div>';
        echo '</div>';
    }

    function general_settings() {
        if (isset($_POST['sell_media_file_update_settings'])) {
            $nonce = $_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'sell_media_file_general_settings')) {
                wp_die('Error! Nonce Security Check Failed! please save the settings again.');
            }
            $stripe_testmode = (isset($_POST["stripe_testmode"]) && $_POST["stripe_testmode"] == '1') ? '1' : '';
            $stripe_test_secret_key = '';
            if(isset($_POST['stripe_test_secret_key']) && !empty($_POST['stripe_test_secret_key'])){
                $stripe_test_secret_key = sanitize_text_field($_POST['stripe_test_secret_key']);
            }
            $stripe_test_publishable_key = '';
            if(isset($_POST['stripe_test_publishable_key']) && !empty($_POST['stripe_test_publishable_key'])){
                $stripe_test_publishable_key = sanitize_text_field($_POST['stripe_test_publishable_key']);
            }
            $stripe_secret_key = '';
            if(isset($_POST['stripe_secret_key']) && !empty($_POST['stripe_secret_key'])){
                $stripe_secret_key = sanitize_text_field($_POST['stripe_secret_key']);
            }
            $stripe_publishable_key = '';
            if(isset($_POST['stripe_publishable_key']) && !empty($_POST['stripe_publishable_key'])){
                $stripe_publishable_key = sanitize_text_field($_POST['stripe_publishable_key']);
            }
            $stripe_currency_code = '';
            if(isset($_POST['stripe_currency_code']) && !empty($_POST['stripe_currency_code'])){
                $stripe_currency_code = sanitize_text_field($_POST['stripe_currency_code']);
            }
            $stripe_options = array();
            $stripe_options['stripe_testmode'] = $stripe_testmode;
            $stripe_options['stripe_test_secret_key'] = $stripe_test_secret_key;
            $stripe_options['stripe_test_publishable_key'] = $stripe_test_publishable_key;
            $stripe_options['stripe_secret_key'] = $stripe_secret_key;
            $stripe_options['stripe_publishable_key'] = $stripe_publishable_key;
            $stripe_options['stripe_currency_code'] = $stripe_currency_code;
            sell_media_file_update_option($stripe_options);
            echo '<div id="message" class="updated fade"><p><strong>';
            echo __('Settings Saved', 'sell-media-file').'!';
            echo '</strong></p></div>';
        }
        
        $stripe_options = sell_media_file_get_option();
        $api_keys_url = "https://dashboard.stripe.com/account/apikeys";
        $api_keys_link = sprintf(wp_kses(__('You can get it from your <a target="_blank" href="%s">stripe account</a>.', 'sell-media-file'), array('a' => array('href' => array(), 'target' => array()))), esc_url($api_keys_url));
        
        $currency_check_url = "https://support.stripe.com/questions/which-currencies-does-stripe-support";
        $currency_check_link = sprintf(wp_kses(__('See <a target="_blank" href="%s">which currencies are supported by stripe</a> for details.', 'sell-media-file'), array('a' => array('href' => array(), 'target' => array()))), esc_url($currency_check_url));
        ?>
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
            <?php wp_nonce_field('sell_media_file_general_settings'); ?>

            <table class="form-table">

                <tbody>

                    <tr valign="top">
                        <th scope="row"><?Php _e('Test Mode', 'sell-media-file');?></th>
                        <td> <fieldset><legend class="screen-reader-text"><span>Test Mode</span></legend><label for="stripe_testmode">
                                    <input name="stripe_testmode" type="checkbox" id="stripe_testmode" <?php if ($stripe_options['stripe_testmode'] == '1') echo ' checked="checked"'; ?> value="1">
                                    <?Php _e('Check this option if you want to place the Stripe payment gateway in test mode using test API keys.', 'sell-media-file');?></label>
                            </fieldset></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_test_secret_key"><?Php _e('Test Secret Key', 'sell-media-file');?></label></th>
                        <td><input name="stripe_test_secret_key" type="text" id="stripe_test_secret_key" value="<?php echo $stripe_options['stripe_test_secret_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Test Secret Key.', 'sell-media-file').' '.$api_keys_link;?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_test_publishable_key"><?Php _e('Test Publishable Key', 'sell-media-file');?></label></th>
                        <td><input name="stripe_test_publishable_key" type="text" id="stripe_test_publishable_key" value="<?php echo $stripe_options['stripe_test_publishable_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Test Publishable Key.', 'sell-media-file').' '.$api_keys_link;?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="stripe_secret_key"><?Php _e('Live Secret Key', 'sell-media-file');?></label></th>
                        <td><input name="stripe_secret_key" type="text" id="stripe_secret_key" value="<?php echo $stripe_options['stripe_secret_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Secret Key.', 'sell-media-file').' '.$api_keys_link;?></p></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="stripe_publishable_key"><?Php _e('Live Publishable Key', 'sell-media-file');?></label></th>
                        <td><input name="stripe_publishable_key" type="text" id="stripe_publishable_key" value="<?php echo $stripe_options['stripe_publishable_key']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('Your Live Publishable Key.', 'sell-media-file').' '.$api_keys_link;?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="stripe_currency_code"><?Php _e('Currency Code', 'sell-media-file');?></label></th>
                        <td><input name="stripe_currency_code" type="text" id="stripe_currency_code" value="<?php echo $stripe_options['stripe_currency_code']; ?>" class="regular-text">
                            <p class="description"><?Php echo __('The currency of the payment.', 'sell-media-file').' '.$currency_check_link;?></p></td>
                    </tr>

                </tbody>

            </table>

            <p class="submit"><input type="submit" name="sell_media_file_update_settings" id="sell_media_file_update_settings" class="button button-primary" value="<?Php _e('Save Changes', 'sell-media-file');?>"></p></form>

        <?php
    }

    function debug_page() {
        ?>
        <div class="wrap">
            <h2><?Php _e('Sell Media File Debug Log', 'sell-media-file');?></h2>
            <div id="poststuff">
                <div id="post-body">
                    <?php
                    if (isset($_POST['sell_media_file_update_log_settings'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'sell_media_file_debug_log_settings')) {
                            wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                        }
                        $options = array();
                        $options['enable_debug'] = (isset($_POST["enable_debug"]) && $_POST["enable_debug"] == '1') ? '1' : '';
                        sell_media_file_update_option($options);
                        echo '<div id="message" class="updated fade"><p>'.__('Settings Saved', 'sell-media-file').'!</p></div>';
                    }
                    if (isset($_POST['sell_media_file_reset_log'])) {
                        $nonce = $_REQUEST['_wpnonce'];
                        if (!wp_verify_nonce($nonce, 'sell_media_file_reset_log_settings')) {
                            wp_die('Error! Nonce Security Check Failed! please save the settings again.');
                        }
                        if (sell_media_file_reset_log()) {
                            echo '<div id="message" class="updated fade"><p>'.__('Debug log file has been reset', 'sell-media-file').'!</p></div>';
                        } else {
                            echo '<div id="message" class="error"><p>'.__('Debug log file could not be reset', 'sell-media-file').'!</p></div>';
                        }
                    }
                    $real_file = SELL_MEDIA_FILE_DEBUG_LOG_PATH;
                    $content = file_get_contents($real_file);
                    $content = esc_textarea($content);
                    $options = sell_media_file_get_option();
                    ?>
                    <div id="template"><textarea cols="70" rows="25" name="sell_media_file_log" id="sell_media_file_log"><?php echo $content; ?></textarea></div>                     
                    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                        <?php wp_nonce_field('sell_media_file_debug_log_settings'); ?>
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><?Php _e('Enable Debug', 'sell-media-file');?></th>
                                    <td> <fieldset><legend class="screen-reader-text"><span>Enable Debug</span></legend><label for="enable_debug">
                                                <input name="enable_debug" type="checkbox" id="enable_debug" <?php if ($options['enable_debug'] == '1') echo ' checked="checked"'; ?> value="1">
                                                <?Php _e('Check this option if you want to enable debug', 'sell-media-file');?></label>
                                        </fieldset></td>
                                </tr>

                            </tbody>

                        </table>
                        <p class="submit"><input type="submit" name="sell_media_file_update_log_settings" id="sell_media_file_update_log_settings" class="button button-primary" value="<?Php _e('Save Changes', 'sell-media-file');?>"></p>
                    </form>
                    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                        <?php wp_nonce_field('sell_media_file_reset_log_settings'); ?>                            
                        <p class="submit"><input type="submit" name="sell_media_file_reset_log" id="sell_media_file_reset_log" class="button" value="<?Php _e('Reset Log', 'sell-media-file');?>"></p>
                    </form>
                </div>         
            </div>
        </div>
        <?php
    }

}

$GLOBALS['sell_media_file'] = new SELL_MEDIA_FILE();

function sell_media_file_button_handler($atts) {
    
    if(!isset($atts['item_name']) || empty($atts['item_name'])){
        return __('item_name cannot be left empty', 'sell-media-file');
    }
    if(!isset($atts['amount']) || !is_numeric($atts['amount'])){
        return __('You need to provide a valid price amount for your item', 'sell-media-file');
    }
    $description = '';
    if(isset($atts['description']) && !empty($atts['description'])){
        $description = $atts['description'];
    }
    $options = sell_media_file_get_option();
    $key = $options['stripe_publishable_key'];
    if(SELL_MEDIA_FILE_STRIPE_TESTMODE){
        $key = $options['stripe_test_publishable_key'];
    }
    $atts['key'] = $key;
    //$atts['image'] = "https://stripe.com/img/documentation/checkout/marketplace.png";
    $currency = $options['stripe_currency_code'];
    if(!isset($atts['currency']) || empty($atts['currency'])){
        $atts['currency'] = $currency;
    }
    $download_link = '';
    if(isset($atts['download_link']) && !empty($atts['download_link'])){
        $download_link = esc_url_raw($atts['download_link']);
        $atts['download_link'] = '';
    }
    $transient_name = 'sellmediafile-link-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $download_link, 4 * 3600 );
    $transient_name = 'sellmediafile-amount-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['amount'], 4 * 3600 );
    $transient_name = 'sellmediafile-currency-' . sanitize_title_with_dashes($atts['item_name']);
    set_transient( $transient_name, $atts['currency'], 4 * 3600 );
    $atts['amount'] = $atts['amount'] * 100;
    $button_code = '<form action="" method="POST">';
    $button_code .= '<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"';
    foreach ($atts as $key => $value) {
        $button_code .= 'data-' . $key . '="' . $value . '"';
    }
    $button_code .= '></script>';
    $button_code .= wp_nonce_field('sell_media_file', '_wpnonce', true, false);
    $button_code .= '<input type="hidden" value="'.$atts['item_name'].'" name="item_name" />';
    $button_code .= '<input type="hidden" value="'.$atts['amount'].'" name="item_amount" />';
    $button_code .= '<input type="hidden" value="'.$atts['currency'].'" name="item_currency" />';
    $button_code .= '<input type="hidden" value="'.$description.'" name="item_description" />';
    $button_code .= '</form>';
    return $button_code;
}

function sell_media_file_get_option(){
    $options = get_option('sell_media_file_options');
    if(!is_array($options)){
        $options = sell_media_file_get_empty_options_array();
    }
    return $options;
}

function sell_media_file_update_option($new_options){
    $empty_options = sell_media_file_get_empty_options_array();
    $options = sell_media_file_get_option();
    if(is_array($options)){
        $current_options = array_merge($empty_options, $options);
        $updated_options = array_merge($current_options, $new_options);
        update_option('sell_media_file_options', $updated_options);
    }
    else{
        $updated_options = array_merge($empty_options, $new_options);
        update_option('sell_media_file_options', $updated_options);
    }
}

function sell_media_file_get_empty_options_array(){
    $options = array();
    $options['stripe_testmode'] = '';
    $options['stripe_test_secret_key'] = '';
    $options['stripe_test_publishable_key'] = '';
    $options['stripe_secret_key'] = '';
    $options['stripe_publishable_key'] = '';
    $options['stripe_currency_code'] = '';
    $options['enable_debug'] = '';
    return $options;
}

function sell_media_file_debug_log($msg, $success, $end = false) {
    if (!SELL_MEDIA_FILE_DEBUG) {
        return;
    }
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - ' . (($success) ? 'SUCCESS :' : 'FAILURE :') . $msg . "\n";
    if ($end) {
        $text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log.txt file
    $fp = fopen(SELL_MEDIA_FILE_DEBUG_LOG_PATH, 'a');
    fwrite($fp, $text);
    fclose($fp);  // close file
}

function sell_media_file_debug_log_array($array_msg, $success, $end = false) {
    if (!SELL_MEDIA_FILE_DEBUG) {
        return;
    }
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - ' . (($success) ? 'SUCCESS :' : 'FAILURE :') . "\n";
    ob_start();
    print_r($array_msg);
    $var = ob_get_contents();
    ob_end_clean();
    $text .= $var;
    if ($end) {
        $text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log.txt file
    $fp = fopen(SELL_MEDIA_FILE_DEBUG_LOG_PATH, 'a');
    fwrite($fp, $text);
    fclose($fp);  // close filee
}

function sell_media_file_reset_log() {
    $log_reset = true;
    $date_time = date('F j, Y g:i a');//the_date('F j, Y g:i a', '', '', FALSE);
    $text = '[' . $date_time . '] - SUCCESS : Log reset';
    $text .= "\n------------------------------------------------------------------\n\n";
    $fp = fopen(SELL_MEDIA_FILE_DEBUG_LOG_PATH, 'w');
    if ($fp != FALSE) {
        @fwrite($fp, $text);
        @fclose($fp);
    } else {
        $log_reset = false;
    }
    return $log_reset;
}
