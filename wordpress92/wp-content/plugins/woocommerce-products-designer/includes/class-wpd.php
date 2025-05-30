<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0
 * @package    Wpd
 * @subpackage Wpd/includes
 * @author     ORION <support@orionorigin.com>
 */
class Wpd {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    3.0
     * @access   protected
     * @var      Wpd_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    3.0
     * @access   protected
     * @var      string    $wpd    The string used to uniquely identify this plugin.
     */
    protected $wpd;

    /**
     * The current version of the plugin.
     *
     * @since    3.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    3.0
     */
    public function __construct() {

        $this->wpd = 'wpd';
        $this->version = WPD_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Wpd_Loader. Orchestrates the hooks of the plugin.
     * - Wpd_i18n. Defines internationalization functionality.
     * - Wpd_Admin. Defines all hooks for the admin area.
     * - Wpd_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    3.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wpd-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wpd-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wpd-admin.php';

        /**
         * The class responsible for defining all actions that occur in the admin area and related to products.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wpd-product.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wpd-public.php';

        /**
         * The class responsible for duplicate a template.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wpd-duplicate.php';

        $this->loader = new Wpd_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Wpd_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    3.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Wpd_i18n();
        $plugin_i18n->set_domain($this->get_wpd());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    3.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new WPD_Admin($this->get_wpd(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_init', $plugin_admin, 'wpc_redirect');

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_woo_parts_submenu');

        // General
        $this->loader->add_action('init', $plugin_admin, 'init_sessions', 1);
        $this->loader->add_action('init', $plugin_admin, 'init_globals');
        $this->loader->add_action('wpc_admin_field_wpc-icon-select', $plugin_admin, 'get_icon_selector_field');
        $this->loader->add_action('admin_notices', $plugin_admin, 'notify_customization_page_missing');
        $this->loader->add_action('admin_notices', $plugin_admin, 'notify_minmimum_required_parameters');
      $this->loader->add_action('admin_notices', $plugin_admin, 'run_wpc_db_updates_requirements');
        $this->loader->add_action('admin_notices', $plugin_admin, 'get_help_notices');
        $this->loader->add_action('admin_notices', $plugin_admin, 'get_missing_parts_notice');
       $this->loader->add_action('wp_ajax_run_updater', $plugin_admin, 'run_wpd_updater');
        $this->loader->add_filter('upload_mimes', $plugin_admin, 'wpc_add_custom_mime_types');
        $this->loader->add_action('admin_notices', $plugin_admin, 'get_max_input_vars_php_ini');

        // $this->loader->add_action( 'admin_notices', $plugin_admin, 'get_license_activation_notice' );
        // $this->loader->add_action( 'wp_ajax_wpd_activate_license', $plugin_admin, 'activate_license');
        // $this->loader->add_action( 'wp_ajax_nopriv_wpd_activate_license', $plugin_admin, 'activate_license');
        // $this->loader->add_filter('pre_set_site_transient_update_plugins', $plugin_admin, 'o_verify_validity');
        // $this->loader->add_action('plugins_loaded', $plugin_admin, 'o_verify_validity');
        // Products
        $product_admin = new WPD_Product(false);
        //$this->loader->add_action('woocommerce_product_write_panel_tabs', $product_admin, 'get_product_tab_label');
        $this->loader->add_action('woocommerce_product_data_panels', $product_admin, 'get_product_tab_data');
        // $this->loader->add_action( 'wp_ajax_get_output_setting_tab_data_content', $product_admin, 'get_output_setting_tab_data_content_ajx' );
        // $this->loader->add_action( 'wp_ajax_get_product_tab_data_content', $product_admin, 'get_product_tab_data_content_ajx' );
        $this->loader->add_action('wp_ajax_get_related_products_content', $product_admin, 'get_related_products_content_ajx');
        // $this->loader->add_action('save_post_product', $product_admin, 'save_customizable_meta');
        $this->loader->add_filter('manage_edit-product_columns', $product_admin, 'get_product_columns');
        $this->loader->add_action('manage_product_posts_custom_column', $product_admin, 'get_products_columns_values', 5, 2);
        $this->loader->add_action('save_post_product', $product_admin, 'save_product_settings_fields');
        $this->loader->add_action('woocommerce_save_product_variation', $product_admin, 'save_product_settings_fields');
        //$this->loader->add_action('woocommerce_product_options_inventory_product_data', $product_admin, 'get_variable_product_details_location_notice');

        // custom line
//        $this->loader->add_action('woocommerce_after_add_to_cart_button', $product_admin, 'hide_cart_button');
        $this->loader->add_action('woocommerce_after_shop_loop_item', $product_admin, 'hide_cart_button');

        // Templates hooks
        $template = new WPD_Template();
        $this->loader->add_action('init', $template, 'register_cpt_template');
        $this->loader->add_action('init', $template, 'register_cpt_template_taxonomy', 0);
        $this->loader->add_filter('manage_edit-wpc-template_columns', $template, 'get_templates_columns');
        $this->loader->add_action('manage_wpc-template_posts_custom_column', $template, 'get_templates_columns_values', 5, 2);
        $this->loader->add_action('add_meta_boxes', $template, 'get_template_metabox');
        $this->loader->add_action('post_submitbox_misc_actions', $template, 'get_templates_post_status');
        $this->loader->add_action('save_post_wpc-template', $template, 'save_wpc_template');
        $this->loader->add_action('admin_footer', $template, 'get_product_selector');
        $this->loader->add_action('admin_init', $template, 'register_tmp_object_locking_options', 1);
        $this->loader->add_filter('screen_layout_columns', $template, 'get_wpd_template_screen_layout_columns');
        $this->loader->add_filter('get_user_option_screen_layout_wpc-template', $template, 'my_screen_layout_wpc_template');
        $this->loader->add_filter('get_user_option_meta-box-order_wpc-template', $template, 'metabox_order');

        // Cliparts hooks
        $clipart = new WPD_Clipart();
        $this->loader->add_action('init', $clipart, 'register_cpt_cliparts');
        $this->loader->add_action('add_meta_boxes', $clipart, 'get_cliparts_metabox');
        $this->loader->add_action('save_post_wpc-cliparts', $clipart, 'save_cliparts');

        $wpd_design = new WPD_Design();
        // Allow us to hide the wpc_data_upl meta from the meta list in the order details page
        $this->loader->add_filter('woocommerce_hidden_order_itemmeta', $wpd_design, 'unset_wpc_data_upl_meta');

        $wpd_config = new WPD_Config();
        // Allow us to hide the wpc_data_upl meta from the meta list in the order details page
        $this->loader->add_action('init', $wpd_config, 'register_cpt_config');
        $this->loader->add_action('save_post_wpd-config', $wpd_config, 'save_config');
        $this->loader->add_action('save_post_product', $wpd_config, 'save_config');
        $this->loader->add_action('add_meta_boxes', $wpd_config, 'get_config_metabox');
        $this->loader->add_action('woocommerce_product_options_general_product_data', $wpd_config, 'get_product_config_selector');
        $this->loader->add_action('woocommerce_product_after_variable_attributes', $wpd_config, 'get_variation_product_config_selector', 10, 3);
        $this->loader->add_filter('get_user_option_meta-box-order_wpd-config', $wpd_config, 'get_metabox_order');
        $this->loader->add_action('admin_action_wpd_duplicate_config', $wpd_config, 'wpd_duplicate_config');
        $this->loader->add_filter('post_row_actions', $wpd_config, 'get_duplicate_post_link', 10, 2);
        $this->loader->add_action('woocommerce_save_product_variation', $wpd_config, 'save_variation_settings_fields');
        $this->loader->add_filter('screen_layout_columns', $wpd_config, 'get_wpd_config_screen_layout_columns');
        $this->loader->add_filter('get_user_option_screen_layout_wpd-config', $wpd_config, 'get_wpd_config_config_screen_layout');

        // subscription
        $this->loader->add_action('admin_notices', $plugin_admin, 'wpd_get_subscription_notice');

        // subscription hook
        $this->loader->add_action('wp_ajax_wpd_subscribe', $plugin_admin, 'wpd_subscribe');
        $this->loader->add_action('wp_ajax_nopriv_wpd_subscribe', $plugin_admin, 'wpd_subscribe');

        // hide subscription hook
        $this->loader->add_action('wp_ajax_wpd_hide_notice', $plugin_admin, 'wpd_hide_notice');
        $this->loader->add_action('wp_ajax_nopriv_wpd_hide_notice', $plugin_admin, 'wpd_hide_notice');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    3.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new WPD_Public($this->get_wpd(), $this->get_version());
        $plugin_admin = new WPD_Admin($this->get_wpd(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
        $this->loader->add_action('woocommerce_after_add_to_cart_button', $plugin_public, 'get_customize_btn');
        $this->loader->add_action('wp_ajax_handle_picture_upload', $plugin_public, 'handle_picture_upload');
        $this->loader->add_action('wp_ajax_nopriv_handle_picture_upload', $plugin_public, 'handle_picture_upload');

        $this->loader->add_filter('woocommerce_loop_add_to_cart_link', $plugin_public, 'get_customize_btn_loop', 10, 2);

        // Add query vars and rewrite rules
        $this->loader->add_filter('query_vars', $plugin_public, 'wpd_add_query_vars');
        $this->loader->add_filter('init', $plugin_public, 'wpd_add_rewrite_rules', 99);

        // Products
        $wpd_product = new WPD_Product(false);
        $this->loader->add_action('woocommerce_add_to_cart', $wpd_product, 'set_custom_upl_cart_item_data', 99, 6);
        $this->loader->add_filter('body_class', $wpd_product, 'get_custom_products_body_class', 10, 2);
        $this->loader->add_action('woocommerce_product_duplicate', $wpd_product, 'duplicate_product_metas', 10, 2);

        // Variable filters
        $this->loader->add_action('init', $plugin_public, 'set_variable_action_filters', 99);

        // Social login
        $this->loader->add_action('init', $plugin_public, 'process_social_login', 99);

        // Body class
        $this->loader->add_filter('body_class', $plugin_public, 'add_class_to_body');

        // Shop loop item class
        $this->loader->add_filter('post_class', $plugin_public, 'get_item_class', 10, 3);

        // Sessions
        $this->loader->add_action('init', $plugin_admin, 'init_sessions', 1);

        // Design hooks
        $wpd_design = new WPD_Design();
        $this->loader->add_action('wp_ajax_handle-custom-design-upload', $wpd_design, 'handle_custom_design_upload');
        $this->loader->add_action('wp_ajax_nopriv_handle-custom-design-upload', $wpd_design, 'handle_custom_design_upload');
        $this->loader->add_action('wp_ajax_add_custom_design_to_cart', $wpd_design, 'add_custom_design_to_cart_ajax');
        $this->loader->add_action('wp_ajax_nopriv_add_custom_design_to_cart', $wpd_design, 'add_custom_design_to_cart_ajax');
        $this->loader->add_action('wp_ajax_save_custom_design_for_later', $wpd_design, 'save_custom_design_for_later_ajax');
        $this->loader->add_action('wp_ajax_nopriv_save_custom_design_for_later', $wpd_design, 'save_custom_design_for_later_ajax');
        $this->loader->add_action('wp_ajax_save_canvas_to_session', $wpd_design, 'save_canvas_to_session_ajax');
        $this->loader->add_action('wp_ajax_nopriv_save_canvas_to_session', $wpd_design, 'save_canvas_to_session_ajax');
        $this->loader->add_action('wp_ajax_delete_saved_design', $wpd_design, 'delete_saved_design_ajax');
        $this->loader->add_action('wp_ajax_nopriv_delete_saved_design', $wpd_design, 'delete_saved_design_ajax');
        // $this->loader->add_action( 'woocommerce_admin_order_item_values', $wpd_design, 'get_order_custom_admin_data',10,3);
        $this->loader->add_action('woocommerce_after_order_itemmeta', $wpd_design, 'get_order_custom_admin_data', 10, 3);
        $this->loader->add_action('woocommerce_new_order_item', $wpd_design, 'save_customized_item_meta', 10, 3);
        // $this->loader->add_action( 'woocommerce_before_cart_item_quantity_zero', $wpd_design, 'remove_wpc_customization');
        $this->loader->add_action('wp_ajax_get_watermarked_preview', $wpd_design, 'get_watermarked_preview');
        $this->loader->add_action('wp_ajax_nopriv_get_watermarked_preview', $wpd_design, 'get_watermarked_preview');
        // $this->loader->add_action( 'user_register', $wpd_design, 'save_user_designs', 10, 1 );
        $this->loader->add_action('wp_login', $wpd_design, 'save_user_temporary_designs', 10, 2);

        $this->loader->add_filter('woocommerce_email_attachments', $wpd_design, 'add_order_design_to_mail', 10, 3);
        $this->loader->add_action('wp_ajax_generate_downloadable_file', $wpd_design, 'generate_downloadable_file');
        $this->loader->add_action('wp_ajax_nopriv_generate_downloadable_file', $wpd_design, 'generate_downloadable_file');
        // User my account page
        $this->loader->add_action('woocommerce_order_item_meta_end', $wpd_design, 'get_user_account_products_meta', 11, 4);
        // $this->loader->add_action( 'woocommerce_after_single_product_summary', $wpd_design, 'get_custom_design_upload_form');
        $this->loader->add_action('woocommerce_before_calculate_totals', $wpd_design, 'get_cart_item_price', 10);
        $this->loader->add_action('wp_ajax_get_design_price', $wpd_design, 'get_design_price_ajax');
        $this->loader->add_action('wp_ajax_nopriv_get_design_price', $wpd_design, 'get_design_price_ajax');
        // Reload an order
        $this->loader->add_action('woocommerce_my_account_my_orders_actions', $wpd_design, 'get_user_account_load_order_button', 10, 2);
        $this->loader->add_action('woocommerce_before_my_account', $wpd_design, 'get_user_saved_designs');
        // Save data to reload
        $this->loader->add_action('wp_ajax_save_data_to_reload', $wpd_design, 'save_data_to_reload');
        $this->loader->add_action('wp_ajax_nopriv_save_data_to_reload', $wpd_design, 'save_data_to_reload');

        // Allow us to hide the wpc_data_upl meta from the meta list in the order details page
        $this->loader->add_filter('woocommerce_hidden_order_itemmeta', $wpd_design, 'unset_wpc_data_upl_meta');
        $this->loader->add_filter('woocommerce_add_cart_item_data', $wpd_design, 'force_individual_cart_items', 10, 2);
        // Cart
        // $this->loader->add_filter( 'woocommerce_get_price_excluding_tax', $wpd_design,'get_price_excluding_tax', 10, 3 );
        // Emails
        $this->loader->add_action('woocommerce_order_item_meta_start', $plugin_public, 'set_email_order_item_meta', 10, 3);

        // Save variation attributes in transients
        $this->loader->add_action('wp_ajax_wpd_store_variation_attributes', $plugin_public, 'wpd_store_variation_attributes');
        $this->loader->add_action('wp_ajax_nopriv_wpd_store_variation_attributes', $plugin_public, 'wpd_store_variation_attributes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    3.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     3.0
     * @return    string    The name of the plugin.
     */
    public function get_wpd() {
        return $this->wpd;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     3.0
     * @return    Wpd_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     3.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
