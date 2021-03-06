<?php

/*
 * The main class, holds everything our Settings does,
 * initialized right after declaration
 */
class SellMediaSettings {

    /*
     * For easier overriding we declared the keys
     * here as well as our tabs array which is populated
     * when registering settings
     */
    private $general_settings_key = 'sell_media_general_settings';
    private $payment_settings_key = 'sell_media_payment_settings';
    private $email_settings_key = 'sell_media_email_settings';
    private $misc_settings_key = 'sell_media_misc_settings';
    private $plugin_options_key = 'sell_media_plugin_options';
    private $plugin_post_type_key = 'sell_media_item';
    private $plugin_settings_tabs = array();

    /*
     * Fired during plugins_loaded (very very early),
     * so don't miss-use this, only actions and filters,
     * current ones speak for themselves.
     */
    function __construct() {
        add_action( 'init', array( &$this, 'load_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_payment_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_email_settings' ) );
        add_action( 'admin_init', array( &$this, 'register_misc_settings' ) );
        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

        if ( ! empty( $_POST['option_page'] ) && $_POST['option_page'] == 'sell_media_misc_settings' ){
            do_action( 'sell_media_settings_init_hook' );
        }
    }

    /*
     * Loads both the settings from
     * the database into their respective arrays. Uses
     * array_merge to merge with default values if they're
     * missing.
     */
    function load_settings() {
        $this->general_settings = (array) get_option( $this->general_settings_key );
        $this->payment_settings = (array) get_option( $this->payment_settings_key );
        $this->email_settings = (array) get_option( $this->email_settings_key );

        // Merge with defaults
        $this->general_settings = array_merge( array(
            'test_mode' => false,
            'checkout_page' => '',
            'thanks_page' => ''
        ), $this->general_settings );

        $this->payment_settings = array_merge( array(
            'paypal_email' => '',
            'currency' => 'USD',
            'default_price' => '100'
        ), $this->payment_settings );

        $user = get_user_by('email', get_option('admin_email') );
        $msg = "Hi {first_name} {last_name},\nThanks for purchasing from my site. Here are your download links:\n{download_links}\nThanks!";

        $this->email_settings = array_merge( array(
            'from_name' => $user->first_name . ' ' . $user->last_name,
            'from_email' => get_option('admin_email'),
            'success_email_subject' => 'Your Purchase',
            'success_email_body' => $msg
        ), $this->email_settings );


        do_action( 'sell_media_load_settings_hook' );
    }

    /*
     * Registers the general settings via the Settings API,
     * appends the setting to the tabs array of the object.
     */
    function register_general_settings() {
        $this->plugin_settings_tabs[$this->general_settings_key] = 'General';

        register_setting( $this->general_settings_key, $this->general_settings_key );
        add_settings_section( 'section_general', 'General Settings', array( &$this, 'section_general_desc' ), $this->general_settings_key );
        add_settings_field( 'test_mode', 'Test Mode', array( &$this, 'field_general_test_mode' ), $this->general_settings_key, 'section_general' );
        add_settings_field( 'checkout_page', 'Checkout Page', array( &$this, 'field_general_checkout_page' ), $this->general_settings_key, 'section_general' );
        add_settings_field( 'thanks_page', 'Thanks Page', array( &$this, 'field_general_thanks_page' ), $this->general_settings_key, 'section_general' );
        add_settings_field( 'customer_notification', 'Customer Notification', array( &$this, 'field_general_customer_notification' ), $this->general_settings_key, 'section_general' );

        do_action( 'sell_media_general_settings_hook' );

    }

    /*
     * Registers the advanced settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_payment_settings() {
        $this->plugin_settings_tabs[$this->payment_settings_key] = 'Payment';

        register_setting( $this->payment_settings_key, $this->payment_settings_key );
        add_settings_section( 'section_payment', 'Payment Settings', array( &$this, 'section_payment_desc' ), $this->payment_settings_key );
        add_settings_field( 'paypal_email', 'Paypal Email Address', array( &$this, 'field_payment_paypal_email' ), $this->payment_settings_key, 'section_payment' );
        add_settings_field( 'currency', 'Currency', array( &$this, 'field_payment_currency' ), $this->payment_settings_key, 'section_payment' );
        add_settings_field( 'default_price', 'Default Price', array( &$this, 'field_payment_default_price' ), $this->payment_settings_key, 'section_payment' );

        do_action( 'sell_media_payment_settings_hook' );

    }

    /*
     * Registers the email settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_email_settings() {
        $this->plugin_settings_tabs[$this->email_settings_key] = 'Email';

        register_setting( $this->email_settings_key, $this->email_settings_key );
        add_settings_section( 'section_email', 'Email Settings', array( &$this, 'section_email_desc' ), $this->email_settings_key );

        add_settings_field( 'from_name', 'From Name', array( &$this, 'field_email_from_name' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'from_email', 'From Email', array( &$this, 'field_email_from_email' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'success_email_subject', 'Email Subject', array( &$this, 'field_email_success_email_subject' ), $this->email_settings_key, 'section_email' );
        add_settings_field( 'success_email_body', 'Email Body', array( &$this, 'field_email_success_email_body' ), $this->email_settings_key, 'section_email' );

        do_action( 'sell_media_email_settings_hook' );

    }

    /*
     * Registers the misc settings and appends the
     * key to the plugin settings tabs array.
     */
    function register_misc_settings() {
        $this->plugin_settings_tabs[$this->misc_settings_key] = 'Misc';

        register_setting( $this->misc_settings_key, $this->misc_settings_key );
        add_settings_section( 'section_misc', 'Misc Settings', array( &$this, 'section_misc_desc' ), $this->misc_settings_key );

    }

    /*
     * The following methods provide descriptions
     * for their respective sections, used as callbacks
     * with add_settings_section
     */
    function section_general_desc() { echo ''; }
    function section_payment_desc() { echo ''; }
    function section_email_desc() { echo ''; }
    function section_misc_desc() {
        printf( __( 'Settings for Extensions are shown below. <a href="%s" class="button secondary" target="_blank">Download Extensions for Sell Media</a>', 'sell_media' ), sell_media_plugin_data( $field='AuthorURI' ) . '/downloads/category/extensions/' );
        do_action( 'sell_media_misc_settings_hook' );
    }

    /*
     * General Option field callback, renders a
     * text input, note the name and value.
     */
    function field_general_test_mode() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[test_mode]" id="<?php echo $this->general_settings_key; ?>[test_mode]">
            <option value="0" <?php selected( $this->general_settings['test_mode'], 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="1" <?php selected( $this->general_settings['test_mode'], 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php printf(__('To accept real payments, select No. To fully use test mode, you must have %1$s.'), '<a href="https://developer.paypal.com/" target="_blank">Paypal sandbox (test) account</a>' ); ?></span>

        <?php
    }

    /*
     * Checkout Page Option field callback
     */
    function field_general_checkout_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[checkout_page]" id="<?php echo $this->general_settings_key; ?>[checkout_page]">
            <?php $this->build_field_pages_select( 'checkout_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'What page contains the <code>[sell_media_checkout]</code> shortcode? This shortcode generates the checkout cart.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Thanks Page Option field callback
     */
    function field_general_thanks_page() {
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[thanks_page]" id="<?php echo $this->general_settings_key; ?>[thanks_page]">
            <?php $this->build_field_pages_select( 'thanks_page' ); ?>
        </select>
        <span class="desc"><?php _e( 'What page contains the <code>[sell_media_thanks]</code> shortcode?', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Customer Notification field callback
     */
    function field_general_customer_notification(){
        ?>
        <select name="<?php echo $this->general_settings_key; ?>[customer_notification]" id="<?php echo $this->general_settings_key; ?>[customer_notification]">
            <option value="0" <?php selected( $this->general_settings['customer_notification'], 0 ); ?>><?php _e( 'No', 'sell_media' ); ?></option>
            <option value="1" <?php selected( $this->general_settings['customer_notification'], 1 ); ?>><?php _e( 'Yes', 'sell_media' ); ?></option>
        </select>
        <span class="desc"><?php _e( 'Notify the customer of their site registration.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Paypal Email Option field callback
     */
    function field_payment_paypal_email() {
        ?>
        <input type="text" name="<?php echo $this->payment_settings_key; ?>[paypal_email]" value="<?php echo esc_attr( $this->payment_settings['paypal_email'] ); ?>" />
        <p class="desc"><?php printf(__('The email address used to collect Paypal payments. <strong>IMPORTANT:</strong> You must setup IPN Notifications in Paypal to process transactions. %1$s. Here is the listener URL you need to add in Paypal: %2$s'), '<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup#id089EG030E5Z" target="_blank">Read Paypal instructions</a>', '<code>' . home_url( '?sell_media-listener=IPN' ) . '</code>' ); ?></p>
        <?php
    }

    /*
     * Currency Option field callback
     */
    function field_payment_currency() {
        ?>
        <select name="<?php echo $this->payment_settings_key; ?>[currency]" id="<?php echo $this->payment_settings_key; ?>[currency]">
            <option value="USD" <?php selected( $this->payment_settings['currency'], 'USD' ); ?>>US Dollars ($)</option>
            <option value="EUR" <?php selected( $this->payment_settings['currency'], 'EUR' ); ?>>Euros (€)</option>
            <option value="GBP" <?php selected( $this->payment_settings['currency'], 'GBP' ); ?>>Pounds Sterling (£)</option>
            <option value="AUD" <?php selected( $this->payment_settings['currency'], 'AUD' ); ?>>Australian Dollars ($)</option>
            <option value="BRL" <?php selected( $this->payment_settings['currency'], 'BRL' ); ?>>Brazilian Real ($)</option>
            <option value="CAD" <?php selected( $this->payment_settings['currency'], 'CAD' ); ?>>Canadian Dollars ($)</option>
            <option value="CZK" <?php selected( $this->payment_settings['currency'], 'CZK' ); ?>>Czech Koruna (Kč)</option>
            <option value="DKK" <?php selected( $this->payment_settings['currency'], 'DKK' ); ?>>Danish Krone</option>
            <option value="HKD" <?php selected( $this->payment_settings['currency'], 'HKD' ); ?>>Hong Kong Dollar ($)</option>
            <option value="HUF" <?php selected( $this->payment_settings['currency'], 'HUF' ); ?>>Hungarian Forint</option>
            <option value="ILS" <?php selected( $this->payment_settings['currency'], 'ILS' ); ?>>Israeli Shekel</option>
            <option value="JPY" <?php selected( $this->payment_settings['currency'], 'JPY' ); ?>>Japanese Yen (¥)</option>
            <option value="MYR" <?php selected( $this->payment_settings['currency'], 'MYR' ); ?>>Malaysian Ringgits</option>
            <option value="MXN" <?php selected( $this->payment_settings['currency'], 'MXN' ); ?>>Mexican Peso ($)</option>
            <option value="NZD" <?php selected( $this->payment_settings['currency'], 'NZD' ); ?>>New Zealand Dollar ($)</option>
            <option value="NOK" <?php selected( $this->payment_settings['currency'], 'NOK' ); ?>>Norwegian Krone</option>
            <option value="PHP" <?php selected( $this->payment_settings['currency'], 'PHP' ); ?>>Philippine Pesos</option>
            <option value="PLN" <?php selected( $this->payment_settings['currency'], 'PLN' ); ?>>Polish Zloty</option>
            <option value="SGD" <?php selected( $this->payment_settings['currency'], 'SGD' ); ?>>Singapore Dollar ($)</option>
            <option value="SEK" <?php selected( $this->payment_settings['currency'], 'SEK' ); ?>>Swedish Krona</option>
            <option value="CHF" <?php selected( $this->payment_settings['currency'], 'CHF' ); ?>>Swiss Franc</option>
            <option value="TWD" <?php selected( $this->payment_settings['currency'], 'TWD' ); ?>>Taiwan New Dollars</option>
            <option value="THB" <?php selected( $this->payment_settings['currency'], 'THB' ); ?>>Thai Baht</option>
            <option value="TRY" <?php selected( $this->payment_settings['currency'], 'TRY' ); ?>>Turkish Lira (TL)</option>
            <option value="ZAR" <?php selected( $this->payment_settings['currency'], 'ZAR' ); ?>>South African rand (R)</option>
        </select>
        <span class="desc"><?php _e( 'The currency in which you accept payment.', 'sell_media' ); ?></span>

        <?php
    }

    /*
     * Default Price Option field callback
     */
    function field_payment_default_price() {
        ?>
        <input type="text" name="<?php echo $this->payment_settings_key; ?>[default_price]" value="<?php echo esc_attr( $this->payment_settings['default_price'] ); ?>" />
        <span class="desc"><?php _e( 'The default price of new items and bulk uploads. You can set unique prices by editing each individual item.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * From Name Option field callback
     */
    function field_email_from_name() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[from_name]" value="<?php echo esc_attr( $this->email_settings['from_name'] ); ?>" />
        <span class="desc"><?php _e( 'The name associated with all outgoing email.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * From Email Option field callback
     */
    function field_email_from_email() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[from_email]" value="<?php echo esc_attr( $this->email_settings['from_email'] ); ?>" />
        <span class="desc"><?php _e( 'The email address used for all outgoing email.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Success Email Subject Option field callback
     */
    function field_email_success_email_subject() {
        ?>
        <input type="text" name="<?php echo $this->email_settings_key; ?>[success_email_subject]" value="<?php echo esc_attr( $this->email_settings['success_email_subject'] ); ?>" />
        <span class="desc"><?php _e( 'The email subject on successful purchase emails.', 'sell_media' ); ?></span>
        <?php
    }

    /*
     * Success Email Body Option field callback
     */
    function field_email_success_email_body() {
        ?>
        <textarea name="<?php echo $this->email_settings_key; ?>[success_email_body]" id="<?php echo $this->email_settings_key; ?>[success_email_body]" style="width:50%;height:150px;"><?php echo esc_attr( $this->email_settings['success_email_body'] ); ?></textarea>
        <p class="desc"><?php _e( 'This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following keywords: {first_name}, {last_name}, {payer_email}, {download_links}. Be sure to include the {download_links} tag, otherwise your buyers won\'t receive their download purchases.', 'sell_media' ); ?></p>
         <?php
    }


    /*
     * Helper for building select options for Pages
     */
    function build_field_pages_select( $option ) {
        $pages = get_pages();
        foreach ( $pages as $page ) { ?>
            <option value="<?php echo $page->ID; ?>" <?php selected( $this->general_settings[''. $option .''], $page->ID ); ?>><?php echo $page->post_title; ?></option>
        <?php }
    }

    /*
     * Called during admin_menu, adds an options
     * page under plugin, rendered
     * using the plugin_options_page method.
     */
    function add_admin_menus() {
        add_submenu_page( 'edit.php?post_type=sell_media_item', __('Settings', 'sell_media'), __('Settings', 'sell_media'),  'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
    }

    /*
     * Plugin Options page rendering goes here, checks
     * for active tab and replaces key with the related
     * settings key. Uses the plugin_options_tabs method
     * to render the tabs.
     */
    function plugin_options_page() {
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php wp_nonce_field( 'update-options' ); ?>
                <?php settings_fields( $tab ); ?>
                <?php do_settings_sections( $tab ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /*
     * Renders our tabs in the plugin options page,
     * walks through the object's tabs array and prints
     * them one by one. Provides the heading for the
     * plugin_options_page method.
     */
    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

        screen_icon( 'options-general' );
        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?post_type=' . $this->plugin_post_type_key . '&page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
    }

};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$sell_media_settings = new SellMediaSettings;' ) );