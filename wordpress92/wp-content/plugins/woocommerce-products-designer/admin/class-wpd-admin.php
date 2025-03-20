<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpd
 * @subpackage Wpd/admin
 * @author     ORION <support@orionorigin.com>
 */
class WPD_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $wpd    The ID of this plugin.
     */
    private $wpd;

    /**
     * The version of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    3.0
     * @param      string $wpd       The name of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct($wpd, $version) {

        $this->wpd = $wpd;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    3.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('wpd-global-admin-css', plugin_dir_url(__FILE__) . 'css/wpd-global-admin-css.css', array(), $this->version, 'all');
        if (is_wpd_admin_screen()) {
            wp_enqueue_style($this->wpd, plugin_dir_url(__FILE__) . 'css/wpd-admin.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-simplegrid', plugin_dir_url(__FILE__) . 'css/simplegrid.min.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-common', WPD_URL . 'public/css/wpd-common.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-tooltip-css', plugin_dir_url(__FILE__) . 'css/tooltip.min.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-colorpicker-css', plugin_dir_url(__FILE__) . 'js/colorpicker/css/colorpicker.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-o-ui', plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-bs-modal-css', WPD_URL . 'public/js/modal/modal.min.css', array(), $this->version, 'all');
            wp_enqueue_style('wpd-datatables-css', WPD_URL . 'admin/js/datatables/jquery.dataTables.min.css', array(), $this->version, 'all');
            wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
            wp_enqueue_style('o-flexgrid', plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    3.0
     */
    public function enqueue_scripts() {
        if (is_wpd_admin_screen()) {
            wp_enqueue_script('wpd-tabs-js', plugin_dir_url(__FILE__) . 'js/SpryTabbedPanels.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-tooltip-js', plugin_dir_url(__FILE__) . 'js/tooltip.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-colorpicker-js', plugin_dir_url(__FILE__) . 'js/colorpicker/js/colorpicker.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-modal-js', WPD_URL . 'public/js/modal/modal.js', array('jquery'), false, false);
            wp_enqueue_script($this->wpd, plugin_dir_url(__FILE__) . 'js/wpd-admin.js', array('jquery', 'select2-js'), $this->version, false);
            wp_localize_script($this->wpd, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_script('wpd-jquery-cookie-js', plugin_dir_url(__FILE__) . 'js/jquery.cookie.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('wpd-datatable-js', plugin_dir_url(__FILE__) . 'js/datatables/jquery.dataTables.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script('o-admin', plugin_dir_url(__FILE__) . 'js/o-admin.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);
            wp_localize_script('o-admin', 'home_url', array("ajax_url", home_url('/')));
            wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, 'all');
        }

        wp_enqueue_script('o-extra-js', plugin_dir_url(__FILE__) . 'js/extra.js', array('jquery'), $this->version, 'all');
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function add_woo_parts_submenu() {
        if (class_exists('WooCommerce')) {
            global $submenu;
            $icon = WPD_URL . 'admin/images/wpd-dashicon.png';
            add_menu_page('Ouidah Product Designer', 'WPD', 'manage_product_terms', 'wpc-manage-dashboard', array($this, 'get_fonts_page'), $icon);
            add_submenu_page('wpc-manage-dashboard', __('Fonts', 'wpd'), __('Fonts', 'wpd'), 'manage_product_terms', 'wpc-manage-fonts', array($this, 'get_fonts_page'));
            add_submenu_page('wpc-manage-dashboard', __('Cliparts', 'wpd'), __('Cliparts', 'wpd'), 'manage_product_terms', 'edit.php?post_type=wpc-cliparts', false);
            add_submenu_page('wpc-manage-dashboard', __('Configurations', 'wpd'), __('Configurations', 'wpd'), 'manage_product_terms', 'edit.php?post_type=wpd-config', false);
            add_submenu_page('wpc-manage-dashboard', __('Settings', 'wpd'), __('Settings', 'wpd'), 'manage_product_terms', 'wpc-manage-settings', array($this, 'get_settings_page'));
            add_submenu_page('wpc-manage-dashboard', __('Go Premium', 'wpd'), __('Go Premium', 'wpd'), 'manage_product_terms', 'wpd-premium-features', array($this, "get_premium_features_page"));
            
            $submenu['wpc-manage-dashboard'][] = array('<div id="user-manual">User Manual</div>', 'manage_product_terms', 'https://designersuiteforwp.com/documentation/ouidah-product-designer/getting-started/how-to-create-and-set-the-design-page/?utm_source=Ouidah%20free&utm_medium=user%20manual%20submenu&utm_campaign=Ouidah');
            $submenu['wpc-manage-dashboard'][] = array('<div id="submit-a-ticket">Submit a ticket</div>', 'manage_product_terms', 'https://designersuiteforwp.com/contact-us/?utm_source=Ouidah%20free&utm_medium=get%support%20submenu&utm_campaign=Ouidah');
        }
    }

    /**
     * Builds the fonts management page
     */
    function get_fonts_page() {
        include_once WPD_DIR . '/includes/wpd-add-fonts.php';
        woocommerce_add_fonts();
    }

    /**
     * Initialize the plugin sessions
     */
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION['wpd-data-to-load'])) {
            $_SESSION['wpd-data-to-load'] = '';
        }

        $_SESSION['wpd_calculated_totals'] = false;
        session_write_close();
    }
    
    /**
     * Redirects the plugin to the about page after the activation
     */
    function wpc_redirect() {
        if (class_exists('WooCommerce')) {
            if (get_option('wpc_do_activation_redirect', false)) {
                delete_option('wpc_do_activation_redirect');
                wp_redirect(admin_url('admin.php?page=wpc-manage-fonts'));
            }
        }
    }

    /**
     * Builds the about page
     */
    
    function get_premium_features_page() {
        ?>

        <div id="wpc-advanced-features">

            <div class="wrap">
                <div id="features-wrap">
                    <h2 class="feature-h2">Go Premium</h2>
                    <div class="list-posts-content">
                        <div class="o-wrap o-features xl-gutter-8">
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-02.svg">
                                <h3 class="wpd-title"><?php _e('TEMPLATING SYSTEM', 'wpd'); ?></h3>
                                <p><?php _e('Creating the perfect design from scratch can be exhausting. Ouidah Product Designer lets you create unique designs your clients can browse and start theirs from.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=templating%20system" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-features-Yellow-18.svg">
                                <h3 class="wpd-title"><?php _e('PRINT READY FILES', 'wpd'); ?></h3>
                                <p><?php _e('The product designer understand the value of a print ready PDF file and lets you generate up to 300dpi PDF files with entirely customizable crop and bleed marks.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=up%20to%2048%20inches%20outputs%20at%20300dpi" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>Pricesbasedonthedesign-19.svg">
                                <h3 class="wpd-title"><?php _e('PRICE BASED ON THE DESIGN', 'wpd'); ?></h3>
                                <p><?php _e('Increase the price based on the number of characters in the text elements, the number of vectors or pictures used in few clicks.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=price%20based%20on%20the%20design" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-03.svg">
                                <h3 class="wpd-title"><?php _e('SUPPORTS ANY FONT', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer lets you easily setup your own fonts, no matter if they are web fonts such as google fonts or custom TTF files.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=supports%20any%20font" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-13.svg">
                                <h3 class="wpd-title"><?php _e('USER UPLOADS CONTROL', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer gives you the entire control on your customers uploads by defining the minimum allowed dimensions and files extensions.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=user%20uploads%20control" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-11.svg">
                                <h3 class="wpd-title"><?php _e('MULTIPLE OUTPUT FORMATS', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer can generate multiple output formats such as PDF, PNG, JPEG and SVG and is able to handle up to 15000px wide outputs. CYMK also supported.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=multiple%20output%20formats" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-10.svg">
                                <h3 class="wpd-title"><?php _e('CUSTOM DESIGNS UPLOADS', 'wpd'); ?></h3>
                                <p><?php _e('Do you have customers who don’t necessarily need to go through the design phase? The Ouidah Product Designer got you covered by allowing them to send you files as attachments to their orders.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=custom%20designs%20uploads" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-07.svg">
                                <h3 class="wpd-title"><?php _e('VECTORS INTEGRATION', 'wpd'); ?></h3>
                                <p><?php _e('Vectors have become a standard in the web to print industry. Ouidah Product Designer includes a SVG file editor which allows your clients to use and modify their vectors right in the edition area.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=vectors%20integration" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-08.svg">
                                <h3 class="wpd-title"><?php _e('CUSTOM COLORS PALETTE', 'wpd'); ?></h3>
                                <p><?php _e('Do you need to limit the colors that can be used by the customers in their designs? Ouidah Product Designer allows you to define a custom color palette that can be used for any text, shape or vector.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=custom%20colors%20palette" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-14.svg">
                                <h3 class="wpd-title"><?php _e('SOCIAL NETWORK INTEGRATION', 'wpd'); ?></h3>
                                <p><?php _e('Social networks are today part of everything. Ouidah Product Designer knows it and let your clients extract and use pictures from their facebook and instagram accounts.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=social%20network%20integration" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-09.svg">
                                <h3 class="wpd-title"><?php _e('ADVANCED DESIGN PRICING', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer takes the product pricing to a whole new level by allowing you to define your own pricing rules based on the elements (pictures, text, shapes…) used by your clients in their designs.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=advanced%20design%20pricing" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-12.svg">
                                <h3 class="wpd-title"><?php _e('DESIGNS AND ORDERS HISTORY', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer let your clients either access their previous ordered designs and start new ones from them or save their design for later.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=designs%20and%20orders%20history" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-Yellow-04.svg">
                                <h3 class="wpd-title"><?php _e('HIGH QUALITY OUTPUTS', 'wpd'); ?></h3>
                                <p><?php _e('Ouidah Product Designer allows you to configure your output file dimensions and is able to generate up to 15000px wide files, including PDF in portrait or landscape.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=high%20quality%20outputs" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                            <div class="o-col col xl-1-3">
                                <img class="vc_single_image-img " src="<?php echo WPD_URL . "admin/images/features/" ?>designersuite4wp-features-Yellow-17.svg">
                                <h3 class="wpd-title"><?php _e('MULTIPLE PRODUCTS ORDERING OPTION', 'wpd'); ?></h3>
                                <p><?php _e('Easily order multiple sizes, colors or any variation attributes of the same custom design in few clicks.', 'wpd'); ?></p>
                                <a href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/pricing/?utm_source=WPD%20Free&utm_medium=cpc&utm_campaign=Designer%20Suite%20for%20WP&utm_term=multiple%20products%20ordering%20option" class="button" target="_blank"><?php _e('Click here to unlock', 'wpd'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    /**
     * Gets the settings and put them in a global variable
     *
     * @global array $wpd_settings Settings
     */
    function init_globals() {
        global $wpd_settings;
        $wpd_settings['wpc-general-options'] = get_option('wpc-general-options');
        $wpd_settings['wpc-texts-options'] = get_option('wpc-texts-options');
        $wpd_settings['wpc-shapes-options'] = get_option('wpc-shapes-options');
        $wpd_settings['wpc-images-options'] = get_option('wpc-images-options');
        $wpd_settings['wpc-designs-options'] = get_option('wpc-designs-options');
        $wpd_settings['wpc-colors-options'] = get_option('wpc-colors-options');
        $wpd_settings['wpc-output-options'] = get_option('wpc-output-options');
        $wpd_settings['wpc_social_networks'] = get_option('wpc_social_networks');
        $wpd_settings['wpc-upload-options'] = get_option('wpc-upload-options');
        $wpd_settings['wpc-licence'] = get_option('wpc-licence');
        $wpd_settings['wpc-ui-options'] = get_option('wpc-ui-options');
        $wpd_settings['wpc-toolbar-options'] = get_option('wpc-toolbar-options');
    }

    private function get_admin_option_field($title, $option_group, $field_name, $type, $default, $class, $css, $tip, $options_array) {
        $field = array(
            'title' => __($title, 'wpd'),
            'name' => $option_group . '[' . $field_name . ']',
            'type' => $type,
            'default' => $default,
            'class' => $class,
            'css' => $css,
            'desc' => __($tip, 'wpd'),
        );
        if (!empty($options_array)) {
            $field['options'] = $options_array;
        }
        return $field;
    }

    private function get_toolbar_settings() {
        $options = array();
        $toolbar_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Toolbar Settings', 'wpd'),
            'id' => 'wpc-toolbar-options',
            'table' => 'options',
        );
        $toolbar_options_end = array('type' => 'sectionend');

        $toolbar_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'name' => 'wpc-toolbar-options[visible-tab]',
            'type' => 'checkbox',
            'default' => 'yes',
            'value' => 'yes',
        );
        $toolbar_all_options = array(
            array(
                'title' => __('Grid', 'wpd'),
                'name' => 'wpc-toolbar-options[grid]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Grid setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
            ),
            array(
                'title' => __('Clear all', 'wpd'),
                'name' => 'wpc-toolbar-options[clear]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Clear all setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Delete', 'wpd'),
                'name' => 'wpc-toolbar-options[delete]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Delete setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Duplicate', 'wpd'),
                'name' => 'wpc-toolbar-options[duplicate]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Duplicate setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Send to back', 'wpd'),
                'name' => 'wpc-toolbar-options[send-to-back]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Send to back setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Bring to front', 'wpd'),
                'name' => 'wpc-toolbar-options[bring-to-front]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Bring to front setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Flip horizontally', 'wpd'),
                'name' => 'wpc-toolbar-options[flipH]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Flip horizontally setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Flip vertically', 'wpd'),
                'name' => 'wpc-toolbar-options[flipV]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Flip vertically setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Center horizontally', 'wpd'),
                'name' => 'wpc-toolbar-options[centerH]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Center horizontally setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Center vertically', 'wpd'),
                'name' => 'wpc-toolbar-options[centerV]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Center vertically setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Undo/Redo', 'wpd'),
                'name' => 'wpc-toolbar-options[undo-redo]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Undo Redo setting in the toolbar section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
        );

        array_push($options, $toolbar_options_begin);
        array_push($options, $toolbar_tab_visible);
        $options = array_merge($options, $toolbar_all_options);
        array_push($options, $toolbar_options_end);
        $options = apply_filters('wpd_toolbar_options', $options);
        echo o_admin_fields($options);
    }

    /**
     * Callbacks which prints the icon selector field
     *
     * @param type $field Field to print
     */
    public function get_icon_selector_field($field) {
        echo $field['value'];
    }

    private function get_admin_color_field($group_option, $prefix = '') {
        if (!empty($prefix)) {
            return array(
                'label-color' => get_proper_value($group_option, $prefix . '-label-color', ''),
                'normal-color' => get_proper_value($group_option, $prefix . '-normal-color', ''),
                'selected-color' => get_proper_value($group_option, $prefix . '-selected-color'),
            );
        } else {
            return array(
                'label-color' => get_proper_value($group_option, 'label-color', ''),
                'normal-color' => get_proper_value($group_option, 'normal-color', ''),
                'selected-color' => get_proper_value($group_option, 'selected-color', ''),
            );
        }
    }

    /**
     * Builds the general settings options
     *
     * @return array Settings
     */
    public function get_front_tools_settings() {

        $options = array();
        $defaults_text_fields = array();
        $defaults_shape_fields = array();

        $this->get_skins_settings();

        $this->get_toolbar_icons_settings($options);

        $this->get_toolbar_colors_settings($options);

        $this->get_features_icons_settings($options);

        $this->get_features_colors_settings($options);

        $this->get_separators_colors_settings($options);

        $this->get_buttons_colors_settings($options);

        $this->get_add_to_cart_buttons_colors_settings($options);

        $this->get_quantity_buttons_colors_settings($options);

        $actions_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Editor colors', 'wpd'),
            'id' => 'wpc-interface-colors',
            'table' => 'options',
        );
        $actions_options_end = array('type' => 'sectionend');

        $color_grouped_fields = $this->get_interface_color_fields();

        $text_default_color_field = $this->get_admin_option_field('Text', 'wpc-ui-options', 'default-text-color', 'text', '#4f71b9', 'wpc-color', '', '', '');
        $bg_default_color_field = $this->get_admin_option_field('Background', 'wpc-ui-options', 'default-background-color', 'text', '#4f71b9', 'wpc-color', '', '', '');
        $outline_bg_default_color_field = $this->get_admin_option_field('Outline', 'wpc-ui-options', 'default-outline-background-color', 'text', '#4f71b9', 'wpc-color', '', '', '');
        array_push($defaults_text_fields, $text_default_color_field);
        array_push($defaults_text_fields, $bg_default_color_field);
        array_push($defaults_text_fields, $outline_bg_default_color_field);
        $shape_default_color_field = $this->get_admin_option_field('Background', 'wpc-ui-options', 'default-shape-background-color', 'text', '#4f71b9', 'wpc-color', '', '', '');
        $shape_outline_bg_default_color_field = $this->get_admin_option_field('Outline', 'wpc-ui-options', 'default-shape-outline-background-color', 'text', '#4f71b9', 'wpc-color', '', '', '');
        array_push($defaults_shape_fields, $shape_default_color_field);
        array_push($defaults_shape_fields, $shape_outline_bg_default_color_field);
        $default_text_colors = array(
            'title' => __('Default Text Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $defaults_text_fields,
        );
        $default_shape_colors = array(
            'title' => __('Default Shape Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $defaults_shape_fields,
        );
        array_push($options, $actions_options_begin);
        array_push($options, $default_text_colors);
        array_push($options, $default_shape_colors);
        array_push($options, $actions_options_end);

        echo o_admin_fields($options);
    }

    private function get_toolbar_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_toolbar_colors_options',
            'table' => 'options',
            'title' => __('Toolbar colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $globals_icons[] = array(
            'type' => 'text',
            'title' => 'Background Color',
            'name' => 'wpc-ui-options[toolbar-background-color]',
            'label_class' => 'o-o-col xl-1-2',
            'class' => 'wpc-color',
        );

        $globals_icons[] = array(
            'type' => 'text',
            'title' => 'Background Color on hover',
            'name' => 'wpc-ui-options[toolbar-background-color-hover]',
            'label_class' => 'o-o-col xl-1-2',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Toolbar Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $globals_icons,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_features_icons_settings(&$options) {
        $fields = wpd_get_ui_options_fields();

        $icons_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_features_options',
            'table' => 'options',
            'title' => __('Features icons', 'wpd'),
        );

        $icons_options_end = array('type' => 'sectionend');

        $globals_icons[] = array();

        foreach ($fields as $key => $field) {

            if (!isset($field['icon'])) {
                continue;
            }

            $icon_field_name = $key . '-icon';
            $globals_icons[] = array(
                'type' => 'image',
                'title' => get_proper_value($field, 'title', ''),
                'name' => "wpc-ui-options[$icon_field_name]",
                'set' => 'Set',
                'remove' => 'Remove',
                'label_class' => 'o-o-col xl-1-4 mg-bot-15',
            );
        }

        $toolbar_icons = array(
            'title' => __('Features Icons', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $globals_icons,
        );
        array_push($options, $icons_options_begin);
        array_push($options, $toolbar_icons);
        array_push($options, $icons_options_end);
    }

    private function get_features_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_features_colors_options',
            'table' => 'options',
            'title' => __('Features colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $fields[] = array(
            'type' => 'text',
            'title' => __('Column Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[features-col-background-color]',
            'label_class' => 'o-o-col xl-1-3 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Feature Title Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[feature-title-background-color]',
            'label_class' => 'o-col xl-1-3 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Feature Title Bg Color on hover', 'wpd'),
            'name' => 'wpc-ui-options[feature-title-background-color-hover]',
            'label_class' => 'o-col xl-1-3 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Feature Interior Bg', 'wpd'),
            'name' => 'wpc-ui-options[features-interior-background-color]',
            'label_class' => 'o-col xl-1-3 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Text Color', 'wpd'),
            'name' => 'wpc-ui-options[feature-title-text-color]',
            'label_class' => 'o-col xl-1-3 mg-bot-15',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Features Column Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $fields,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_separators_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_separators_colors_options',
            'table' => 'options',
            'title' => __('Separators colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[separators-background-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Left Border Color', 'wpd'),
            'name' => 'wpc-ui-options[separators-left-border-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Text Color', 'wpd'),
            'name' => 'wpc-ui-options[separators-text-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Separators Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $fields,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_buttons_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_separators_colors_options',
            'table' => 'options',
            'title' => __('Separators colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[buttons-background-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color on hover', 'wpd'),
            'name' => 'wpc-ui-options[buttons-background-color-hover]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Text Color', 'wpd'),
            'name' => 'wpc-ui-options[buttons-text-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Buttons Colors', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $fields,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_add_to_cart_buttons_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_add_to_cart_buttons_colors_options',
            'table' => 'options',
            'title' => __('Separators colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[ac-buttons-background-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color on hover', 'wpd'),
            'name' => 'wpc-ui-options[ac-buttons-background-color-hover]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Text Color', 'wpd'),
            'name' => 'wpc-ui-options[ac-buttons-text-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Add to cart button', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $fields,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_quantity_buttons_colors_settings(&$options) {
        $options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_add_to_cart_buttons_colors_options',
            'table' => 'options',
            'title' => __('Separators colors', 'wpd'),
        );

        $options_end = array('type' => 'sectionend');

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color', 'wpd'),
            'name' => 'wpc-ui-options[qty-buttons-background-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Bg Color on hover', 'wpd'),
            'name' => 'wpc-ui-options[qty-buttons-background-color-hover]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $fields[] = array(
            'type' => 'text',
            'title' => __('Text Color', 'wpd'),
            'name' => 'wpc-ui-options[qty-buttons-text-color]',
            'label_class' => 'o-col xl-1-4 mg-bot-15',
            'class' => 'wpc-color',
        );

        $toolbar = array(
            'title' => __('Add to cart quantity buttons', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $fields,
        );
        array_push($options, $options_begin);
        array_push($options, $toolbar);
        array_push($options, $options_end);
    }

    private function get_toolbar_icons_settings(&$options) {
        $icons_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_toolbar_icons_options',
            'table' => 'options',
            'title' => __('Toolbar icons', 'wpd'),
        );
        $icons_array = array(
            'grid' => __('Grid', 'wpd'),
            'clear' => __('Clear', 'wpd'),
            'delete' => __('Delete', 'wpd'),
            'duplicate' => __('Duplicate', 'wpd'),
            'send-to-back' => __('Send to back', 'wpd'),
            'bring-to-front' => __('Bring to front', 'wpd'),
            'flipV' => __('Vertical flip', 'wpd'),
            'flipH' => __('Horizontal flip', 'wpd'),
            'centerH' => __('Horizontal center', 'wpd'),
            'centerV' => __('Vertical center', 'wpd'),
            'undo' => __('Undo', 'wpd'),
            'redo' => __('Redo', 'wpd'),
        );

        $icons_options_end = array('type' => 'sectionend');

        foreach ($icons_array as $name => $label) {

            $id = "wpc-ui-options[$name]";
            $globals_icons[] = array(
                'type' => 'image',
                'title' => $label,
                'name' => $id,
                'set' => 'Set',
                'remove' => 'Remove',
                'label_class' => 'o-col xl-1-4 mg-bot-15',
            );
        }

        $toolbar_icons = array(
            'title' => __('Toolbar Icons', 'wpd'),
            'type' => 'groupedfields',
            'fields' => $globals_icons,
        );
        array_push($options, $icons_options_begin);
        array_push($options, $toolbar_icons);
        array_push($options, $icons_options_end);
    }

    private function get_skins_settings() {
        $skin_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-skin-container',
            'table' => 'options',
        );
        $skins_arr = apply_filters(
                'wpd_configuration_skins', array(
            'WPD_Skin_Porto_Novo' => __('Porto-Novo', 'wpd'),
            'WPD_Skin_Default' => __('Default', 'wpd'),
                )
        );

        $skins = array(
            'title' => __('Skin', 'wpd'),
            'name' => 'wpc-ui-options[skin]',
            'type' => 'select',
            'options' => $skins_arr,
            'class' => 'chosen_select_nostd wpd-config-skin',
            'desc' => __('Editor look and feel.', 'wpd'),
        );

        $skin_end = array('type' => 'sectionend');
        $skin_settings = apply_filters(
                'vpc_skins_settings', array(
            $skin_begin,
            $skins,
            $skin_end,
                )
        );

        echo o_admin_fields($skin_settings);
    }

    private function get_interface_color_fields() {
        $fields = wpd_get_ui_options_fields();

        $color_grouped_fields = array();
        foreach ($fields as $key => $field) {
            $grouped_field = array(
                'title' => get_proper_value($field, 'title', ''),
                'type' => 'groupedfields',
                'fields' => array(),
            );
            if (isset($field['icon'])) {
                $icon_field_name = $key . '-icon';
                $icon = array(
                    'type' => 'image',
                    'title' => __('Icon', 'wpd'),
                    'name' => "wpc-ui-options[$icon_field_name]",
                    'set' => 'Set',
                    'remove' => 'Remove',
                );
                array_push($grouped_field['fields'], $icon);
            }
            $colors_options = array(
                'text-color' => __('Text color', 'wpd'),
                'background-color' => __('Background color', 'wpd'),
                'background-color-hover' => __('Background color on hover', 'wpd'),
            );
            foreach ($colors_options as $option_name => $option_label) {
                $color_field = $this->get_admin_option_field($option_label, 'wpc-ui-options', "$key-$option_name", 'text', '', 'wpc-color', '', '', '');
                array_push($grouped_field['fields'], $color_field);
            }
            array_push($color_grouped_fields, $grouped_field);
        }

        return $color_grouped_fields;
    }

    private function get_general_settings() {
        $options = array();

        $general_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-general-options',
            'table' => 'options',
            'title' => __('General Settings', 'wpd'),
        );

        $args = array(
            'post_type' => 'page',
            'nopaging' => true,
        );

        $customizer_page = array(
            'title' => __('Design Page', 'wpd'),
            'desc' => __('This setting allows the plugin to locate the page where customizations are made. Please note that this page can only be accessed by our plugin and should not appear in any menu.', 'wpd'),
            'name' => 'wpc-general-options[wpc_page_id]',
            'type' => 'post-type',
            'default' => '',
            'class' => 'chosen_select_nostd',
            'args' => $args,
        );

        $content_filter = array(
            'title' => __('Automatically append canvas to the design page', 'wpd'),
            'name' => 'wpc-general-options[wpc-content-filter]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to use a shortcode to display the the customizer in the selected page.', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $customizer_cart_display = array(
            'title' => __('Parts position in cart', 'wpd'),
            'name' => 'wpc-general-options[wpc-parts-position-cart]',
            'default' => 'thumbnail',
            'type' => 'radio',
            'desc' => __('This option allows you to set where to show your customized products parts on the cart page', 'wpd'),
            'options' => array(
                'thumbnail' => __('Thumbnail column', 'wpd'),
                'name' => __('Name column', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $download_button = array(
            'title' => __('Download design', 'wpd'),
            'name' => 'wpc-general-options[wpc-download-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the download button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $preview_button = array(
            'title' => __('Preview design', 'wpd'),
            'name' => 'wpc-general-options[wpc-preview-btn]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the preview button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $hide_design_buttons_cart_page = array(
            'title' => __('Hide design buttons on shop page', 'wpd'),
            'name' => 'wpc-general-options[wpc-hide-btn-shop-pages]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to show/hide the cart button on the customization page', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );
        $add_to_cart_action = array(
            'title' => __('Redirect after adding a custom design to the cart?', 'wpd'),
            'name' => 'wpc-general-options[wpc-redirect-after-cart]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define what to do after adding a design to the cart', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $responsive_canvas = array(
            'title' => __('Responsive behaviour', 'wpd'),
            'name' => 'wpc-general-options[responsive]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to enable the canvas responsive behaviour.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $disable_keyboard_shortcuts = array(
            'title' => __('Disable keyword shortcuts', 'wpd'),
            'name' => 'wpc-general-options[disable-keyboard-shortcuts]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to disable the keyboard shortcuts if needed.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $hide_requirements_notices = array(
            'title' => __('Hide requirements notices', 'wpd'),
            'name' => 'wpc-general-options[hide-requirements-notices]',
            'default' => '0',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to hide the requirement notice.', 'wpd'),
            'options' => array(
                '0' => __('No', 'wpd'),
                '1' => __('Yes', 'wpd'),
            ),
            'row_class' => 'wpd_hide_requirements',
            'class' => 'chosen_select_nostd',
        );
        
        $hide_cart_button_for_custom_products = array(
            'title' => __('Hide Add to cart button for custom products', 'wpd'),
            'name' => 'wpc-general-options[wpd-hide-cart-button]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to define whether or not you want to hide the add to cart button for custom products on the products page.', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $general_options_end = array('type' => 'sectionend');

        $conflicts_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_conflicts_options',
            'title' => __('Scripts management', 'wpd'),
            'table' => 'options',
        );

        $load_bs_modal = array(
            'title' => __('Load bootsrap modal', 'wpd'),
            'name' => 'wpc-general-options[wpc-load-bs-modal]',
            'default' => '1',
            'type' => 'radio',
            'desc' => __('This option allows you to enable/disable twitter\'s bootstrap modal script', 'wpd'),
            'options' => array(
                '1' => __('Yes', 'wpd'),
                '0' => __('No', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );
        $conflicts_options_end = array('type' => 'sectionend');

        array_push($options, $general_options_begin);
        array_push($options, $customizer_page);
        array_push($options, $content_filter);
        array_push($options, $customizer_cart_display);
        array_push($options, $preview_button);
        array_push($options, $download_button);
        array_push($options, $add_to_cart_action);
        array_push($options, $responsive_canvas);
        array_push($options, $disable_keyboard_shortcuts);
        array_push($options, $hide_cart_button_for_custom_products);
        array_push($options, $hide_design_buttons_cart_page);
        array_push($options, $hide_requirements_notices);
        array_push($options, $general_options_end);
        array_push($options, $conflicts_options_begin);
        array_push($options, $load_bs_modal);
        array_push($options, $conflicts_options_end);
        $options = apply_filters('wpd_general_options', $options);
        echo o_admin_fields($options);
    }

    /**
     * Builds the uploads settings options
     *
     * @return array Settings
     * @return array
     */
    private function get_uploads_settings() {

        $uploader_type = array(
            'title' => __('File upload script', 'wpd'),
            'name' => 'wpc-upload-options[wpc-uploader]',
            'default' => 'custom',
            'type' => 'radio',
            'desc' => __('This option allows you to set which file upload script you would like to use', 'wpd'),
            'options' => array(
                'custom' => __('Custom with graphical enhancements', 'wpd'),
                'native' => __('Normal', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );
        $min_upload_w = array(
            'title' => __('Uploads min width (px)', 'wpd'),
            'desc' => __('Uploaded images minimum width', 'wpd'),
            'name' => 'wpc-upload-options[wpc-min-upload-width]',
            'type' => 'text',
            'default' => '',
        );
        $min_upload_h = array(
            'title' => __('Uploads min height (px)', 'wpd'),
            'desc' => __('Uploaded images minimum height', 'wpd'),
            'name' => 'wpc-upload-options[wpc-min-upload-height]',
            'type' => 'text',
            'default' => '',
        );
        $upl_extensions = array(
            'title' => __('Allowed uploads extensions', 'wpd'),
            'name' => 'wpc-upload-options[wpc-upl-extensions]',
            'default' => array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'psd', 'eps', 'pdf', 'webp'),
            'type' => 'multiselect',
            'desc' => __('Allowed extensions for uploads', 'wpd'),
            'options' => array(
                'jpg' => __('jpg', 'wpd'),
                'jpeg' => __('jpeg', 'wpd'),
                'png' => __('png', 'wpd'),
                'gif' => __('gif', 'wpd'),
                'bmp' => __('bmp', 'wpd'),
                'svg' => __('svg', 'wpd'),
                'psd' => __('psd', 'wpd'),
                'eps' => __('eps', 'wpd'),
                'pdf' => __('pdf', 'wpd'),
                'webp' => __('webp', 'wpd'),
            ),
        );

        $custom_designs_extensions = array(
            'title' => __('Custom designs allowed extensions (separated by commas)', 'wpd'),
            'desc' => __('Allowed extensions for custom designs. If not set, all extensions will be accepted.', 'wpd'),
            'name' => 'wpc-upload-options[wpc-custom-designs-extensions]',
            'type' => 'text',
            'default' => '',
        );
        $uploads_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'label' => __('Show this tab', 'wpd'),
            'desc' => __('Whether or not to display this tab on the designer.', 'wpd'),
            'name' => 'wpc-upload-options[visible-tab]',
            'default' => 'yes',
            'value' => 'yes',
            'type' => 'checkbox',
        );

        $uploads_all_options = array(
            array(
                'title' => __('Grayscale', 'wpd'),
                'name' => 'wpc-upload-options[grayscale]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Grayscale filter button in the uploads section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'start',
            ),
            array(
                'title' => __('Invert', 'wpd'),
                'desc' => __('Enable/Disable the Invert filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[invert]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sepia1', 'wpd'),
                'desc' => __('Enable/Disable the Sepia 1 filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[sepia1]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sepia2', 'wpd'),
                'desc' => __('Enable/Disable the Sepia 2 filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[sepia2]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Blur', 'wpd'),
                'desc' => __('Enable/Disable the Blur filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[blur]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sharpen', 'wpd'),
                'desc' => __('Enable/Disable the Sharpen filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[sharpen]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Opacity', 'wpd'),
                'desc' => __('Enable/Disable the opacity control field in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[opacity]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Emboss', 'wpd'),
                'desc' => __('Enable/Disable the emboss filter button in the uploads section.', 'wpd'),
                'label' => __('Enable', 'wpd'),
                'name' => 'wpc-upload-options[emboss]',
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
        );
        $upload_settings_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-upload-options',
            'title' => __('Uploads Settings', 'wpd'),
            'table' => 'options',
        );

        $upload_settings_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-upload-options',
        );

        $options = array();
        array_push($options, $upload_settings_begin);
        array_push($options, $uploader_type);
        array_push($options, $upl_extensions);
        array_push($options, $upload_settings_end);
        $options = apply_filters('wpd_uploads_options', $options);
        echo o_admin_fields($options);
    }

    /**
     * Builds the social networks settings options
     *
     * @return array Settings
     */
    private function get_social_networks_settings() {
        $options = array();

        $social_networks_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc_social_networks',
            'title' => __('Social Networks Settings', 'wpd'),
            'table' => 'options',
        );

        $social_networks_end = array(
            'type' => 'sectionend',
            'id' => 'wpc_social_networks',
        );
        $facebook_app_id = array(
            'title' => __('Facebook APP ID', 'wpd'),
            'desc' => __('This setting is mandatory so the user can use facebook connect', 'wpd'),
            'name' => 'wpc_social_networks[wpc-facebook-app-id]',
            'type' => 'text',
            'default' => '',
        );
        $facebook_app_secret = array(
            'title' => __('Facebook APP secret', 'wpd'),
            'desc' => __('This setting is mandatory so the user can use facebook connect', 'wpd'),
            'name' => 'wpc_social_networks[wpc-facebook-app-secret]',
            'type' => 'text',
            'default' => '',
        );

        $instagram_app_id = array(
            'title' => __('Instagram APP ID', 'wpd'),
            'desc' => __('This setting is mandatory so the user can use instagram connect', 'wpd'),
            'name' => 'wpc_social_networks[wpc-instagram-app-id]',
            'type' => 'text',
            'default' => '',
        );

        $instagram_app_secret = array(
            'title' => __('Instagram APP secret', 'wpd'),
            'desc' => __('This setting is mandatory so the user can use instagram connect', 'wpd'),
            'name' => 'wpc_social_networks[wpc-instagram-app-secret]',
            'type' => 'text',
            'default' => '',
        );

        $facebook_redirect_URL = array(
            'title' => __('Facebook Redirect URI', 'wpd'),
            'desc' => __('Valid OAuth redirection URI when setting up the Facebook app.', 'wpd'),
            'type' => 'custom',
            'callback' => array($this, 'get_facebook_redirection_url'),
        );
        $instragram_redirect_URL = array(
            'title' => __('Instagram Redirect URI', 'wpd'),
            'desc' => __('Valid OAuth redirection URI when setting up the Instagram app.', 'wpd'),
            'type' => 'custom',
            'callback' => array($this, 'get_instagram_redirection_url'),
        );

        array_push($options, $social_networks_begin);
        array_push($options, $facebook_app_id);
        array_push($options, $facebook_app_secret);
        array_push($options, $facebook_redirect_URL);
        array_push($options, $instagram_app_id);
        array_push($options, $instagram_app_secret);
        array_push($options, $instragram_redirect_URL);

        array_push($options, $social_networks_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the output settings options
     *
     * @return array Settings
     */
    private function get_output_settings() {
        $options = array();

        $cmyk_attr = array();
        if (!class_exists('Imagick')) {
            $cmyk_attr = array(
                'disabled' => '',
            );
        }
        $cmyk_profil = array(
            'title' => __('CMYK profile (when CMYK mode is enabled)', 'wpd'),
            'name' => 'wpc-output-options[wpc-cmyk-profil]',
            'type' => 'file',
            'desc' => __('This option allows you to set your own CMYK profile to use during the output conversion.', 'wpd') . '<br>' .
            __('Disabled if the Imagemagick extension is not installed and active.', 'wpd'),
            'custom_attributes' => array_merge($cmyk_attr, array('alt' => 'color profil')),
            'set' => __('Set profile', 'wpd'),
            'remove' => __('Remove profile', 'wpd'),
        );
        $design_composition = array(
            'title' => __('Design Composition', 'wpd'),
            'name' => 'wpc-output-options[design-composition]',
            'default' => 'no',
            'type' => 'radio',
            'desc' => __('This option allows you to display or not design composition in the order ', 'wpd'),
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $use_order_id = array(
            'title' => __('Use order ID as zip name', 'wpd'),
            'name' => 'wpc-output-options[use-order-id-as-zip-name]',
            'default' => 'no',
            'type' => 'radio',
            'desc' => __('This option will name each output archive using the order ID.', 'wpd'),
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $use_retina_calc = array(
            'title' => __('Calculate retina output', 'wpd'),
            'name' => 'wpc-output-options[calc-retina-output]',
            'default' => 'yes',
            'type' => 'radio',
            'desc' => __('When this option is set to "yes", the output width is divided by 2 on retina screen.', 'wpd'),
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );

        $output_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-output-options',
            'title' => __('Output Settings', 'wpd'),
            'table' => 'options',
        );

        $output_options_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-output-options',
        );

        array_push($options, $output_options_begin);
        array_push($options, $cmyk_profil);
        array_push($options, $design_composition);
        array_push($options, $use_order_id);
        array_push($options, $use_retina_calc);
        array_push($options, $output_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the colors settings options
     *
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_colors_settings() {
        $options = array();

        $svg_colors = array(
            'title' => __('SVG colorization', 'wpd'),
            'name' => 'wpc-colors-options[wpc-svg-colorization]',
            'default' => 'by-path',
            'type' => 'radio',
            'desc' => __('This option allows you to set how you would like the SVG files to be colorized', 'wpd'),
            'options' => array(
                'by-path' => __('Path by path', 'wpd'),
                'by-colors' => __('Color by color', 'wpd'),
                'none' => __('None', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );
        $colors_palette = array(
            'title' => __('Colors palette', 'wpd'),
            'name' => 'wpc-colors-options[wpc-color-palette]',
            'default' => 'unlimited',
            'type' => 'radio',
            'desc' => __('This option allows you would like your clients to use in their designs', 'wpd'),
            'options' => array(
                'unlimited' => __('Unlimited', 'wpd'),
                'custom' => __('Custom', 'wpd'),
            ),
            'class' => 'chosen_select_nostd',
        );
        $line_color = array(
            'title' => __('Line Color', 'wpd'),
            'name' => 'wpc-colors-options[line-color]',
            'type' => 'text',
            'class' => 'wpc-color',
        );
        $colors_options_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-colors-options',
            'title' => __('Colors Settings', 'wpd'),
            'table' => 'options',
        );

        $colors_options_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-colors-options',
        );
        array_push($options, $colors_options_begin);
        array_push($options, $line_color);
        array_push($options, $svg_colors);
        array_push($options, $colors_palette);
        array_push($options, $colors_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the text settings options
     *
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_text_settings() {
        $options = array();

        $text_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Text Settings', 'wpd'),
            'id' => 'wpc-texts-options',
            'table' => 'options',
        );
        $text_options_end = array('type' => 'sectionend');

        $text_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'name' => 'wpc-texts-options[visible-tab]',
            'type' => 'checkbox',
            'default' => 'yes',
            'value' => 'yes',
        );
        $text_all_options = array(
            array(
                'title' => __('Underline', 'wpd'),
                'name' => 'wpc-texts-options[underline]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Underline setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'start',
            ),
            array(
                'title' => __('Bold', 'wpd'),
                'name' => 'wpc-texts-options[bold]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Bold setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Italic', 'wpd'),
                'name' => 'wpc-texts-options[italic]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Italic setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Text Color', 'wpd'),
                'name' => 'wpc-texts-options[text-color]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Text Color setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Background Color', 'wpd'),
                'name' => 'wpc-texts-options[background-color]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Background Color setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Outline', 'wpd'),
                'name' => 'wpc-texts-options[outline]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Outline setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Curved', 'wpd'),
                'name' => 'wpc-texts-options[curved]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Curved Text setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Font Family', 'wpd'),
                'name' => 'wpc-texts-options[font-family]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Font Family setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Font Size', 'wpd'),
                'name' => 'wpc-texts-options[font-size]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Font Size setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Outline Width', 'wpd'),
                'name' => 'wpc-texts-options[outline-width]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Outline Width setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Opacity', 'wpd'),
                'name' => 'wpc-texts-options[opacity]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Opacity setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Alignment', 'wpd'),
                'name' => 'wpc-texts-options[text-alignment]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Text Alignment setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Strikethrough', 'wpd'),
                'name' => 'wpc-texts-options[text-strikethrough]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Strikethrough setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Overline', 'wpd'),
                'name' => 'wpc-texts-options[text-overline]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Overline setting in the text section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'end',
            ),
            array(
                'title' => __('Left Position', 'wpd'),
                'name' => 'wpc-texts-options[origin-x]',
                'default' => 'left',
                'type' => 'radio',
                'desc' => __('This option allows you to set the text origin on the canvas', 'wpd'),
                'options' => array(
                    'left' => __('Left', 'wpd'),
                    'center' => __('Center', 'wpd'),
                ),
            ),
            array(
                'title' => __('Minimum font size', 'wpd'),
                'name' => 'wpc-texts-options[min-font-size]',
                'desc' => __('Minimum font size in the text section.', 'wpd'),
                'type' => 'number',
                'default' => 8,
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Maximum font size', 'wpd'),
                'name' => 'wpc-texts-options[max-font-size]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Maximum font size in the text section.', 'wpd'),
                'type' => 'number',
                'default' => 30,
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Default font size', 'wpd'),
                'name' => 'wpc-texts-options[default-font-size]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Default font size in the text section.', 'wpd'),
                'type' => 'number',
                'default' => 15,
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
        );

        array_push($options, $text_options_begin);
        array_push($options, $text_tab_visible);
        $options = array_merge($options, $text_all_options);
        array_push($options, $text_options_end);
        $options = apply_filters('wpd_text_options', $options);

        echo o_admin_fields($options);
    }

    /**
     * Builds the shapes settings options
     *
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_shapes_settings() {
        $options = array();

        $shapes_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Shapes Settings', 'wpd'),
            'id' => 'wpc-shapes-options',
            'table' => 'options',
        );

        $shapes_options_end = array('type' => 'sectionend');

        $shapes_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'name' => 'wpc-shapes-options[visible-tab]',
            'type' => 'checkbox',
            'value' => 'yes',
            'default' => 'yes',
        );
        $shapes_all_options = array(
            array(
                'title' => __('Square', 'wpd'),
                'name' => 'wpc-shapes-options[square]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Square shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'start',
            ),
            array(
                'title' => __('Rounded square', 'wpd'),
                'name' => 'wpc-shapes-options[r-square]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Rounded Square shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Circle', 'wpd'),
                'name' => 'wpc-shapes-options[circle]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Circle shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Triangle', 'wpd'),
                'name' => 'wpc-shapes-options[triangle]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Triangle shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Polygon', 'wpd'),
                'name' => 'wpc-shapes-options[polygon]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Polygon shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Star', 'wpd'),
                'name' => 'wpc-shapes-options[star]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Star shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Heart', 'wpd'),
                'name' => 'wpc-shapes-options[heart]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Heart shape in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Outline', 'wpd'),
                'name' => 'wpc-shapes-options[outline]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Outline setting in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Background Color', 'wpd'),
                'name' => 'wpc-shapes-options[background-color]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Background Color setting in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Outline Width', 'wpd'),
                'name' => 'wpc-shapes-options[outline-width]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Outline Width setting in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Opacity', 'wpd'),
                'name' => 'wpc-shapes-options[opacity]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Opacity setting in the shapes section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'end',
            ),
        );

        array_push($options, $shapes_options_begin);
        array_push($options, $shapes_tab_visible);
        $options = array_merge($options, $shapes_all_options);
        array_push($options, $shapes_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the images settings options
     *
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_images_settings() {
        global $wpd_settings;
        $options = array();

        $images_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Image Settings', 'wpd'),
            'table' => 'options',
            'id' => 'wpc-images-options',
        );

        $images_options_end = array('type' => 'sectionend');
        $images_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'name' => 'wpc-images-options[visible-tab]',
            'default' => 'yes',
            'value' => 'yes',
            'type' => 'checkbox',
        );
        $images_all_options = array(
            array(
                'title' => __('Grayscale', 'wpd'),
                'name' => 'wpc-images-options[grayscale]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Grayscale filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'start',
            ),
            array(
                'title' => __('Invert', 'wpd'),
                'name' => 'wpc-images-options[invert]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Invert filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sepia1', 'wpd'),
                'name' => 'wpc-images-options[sepia1]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Sepia 1 filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sepia2', 'wpd'),
                'name' => 'wpc-images-options[sepia2]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Sepia 2 filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Blur', 'wpd'),
                'name' => 'wpc-images-options[blur]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Blur filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Sharpen', 'wpd'),
                'name' => 'wpc-images-options[sharpen]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Sharpen filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Emboss', 'wpd'),
                'name' => 'wpc-images-options[emboss]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Emboss filter button in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Opacity', 'wpd'),
                'name' => 'wpc-images-options[opacity]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Opacity setting in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
            array(
                'title' => __('Enable lazyload for cliparts galleries', 'wpd'),
                'name' => 'wpc-images-options[lazy]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the lazyload behavior in the cliparts section.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => '',
            ),
        );

        array_push($options, $images_options_begin);
        array_push($options, $images_tab_visible);
        $options = array_merge($options, $images_all_options);
        array_push($options, $images_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the user design settings options
     *
     * @global array $wpd_settings
     * @return array Settings
     */
    private function get_my_design_settings() {
        global $wpd_settings;
        $options = array();

        $colors_array = array(
            'label-color' => __('Title section text color'),
            'normal-color' => __('Title section background color'),
            'selected-color' => __('Title section background color on hover'),
        );

        $design_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Designs Settings', 'wpd'),
            'table' => 'options',
            'id' => 'wpc-designs-options',
        );

        $design_options_end = array('type' => 'sectionend');

        $design_tab_visible = array(
            'title' => __('Active controls', 'wpd'),
            'desc' => __('Show this tab', 'wpd'),
            'name' => 'wpc-designs-options[visible-tab]',
            'type' => 'checkbox',
            'value' => 'yes',
            'default' => 'yes',
        );
        $design_all_options = array(
            array(
                'title' => __('Saved Designs', 'wpd'),
                'name' => 'wpc-designs-options[saved]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable the Saved Designs feature.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'start',
            ),
            array(
                'title' => __('Orders Designs', 'wpd'),
                'name' => 'wpc-designs-options[orders]',
                'label' => __('Enable', 'wpd'),
                'desc' => __('Enable/Disable access to the previous Orders Designs feature.', 'wpd'),
                'type' => 'checkbox',
                'default' => 'yes',
                'value' => 'yes',
                'checkboxgroup' => 'end',
            ),
        );

        array_push($options, $design_options_begin);
        array_push($options, $design_tab_visible);
        $options = array_merge($options, $design_all_options);
        array_push($options, $design_options_end);
        echo o_admin_fields($options);
    }

    /**
     * Builds the social networks settings options
     *
     * @return array Settings
     */
    private function get_licence_settings() {
        $options = array();
        $licence_begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-licence',
            'title' => __('Licence Settings', 'wpd'),
            'table' => 'options',
        );

        $licence_end = array(
            'type' => 'sectionend',
            'id' => 'wpc-licence',
        );
        $envato_username = array(
            'title' => __('Envato Username', 'wpd'),
            'desc' => __('Your Envato username', 'wpd'),
            'name' => 'wpc-licence[envato-username]',
            'type' => 'text',
            'default' => '',
        );
        $envato_api_secret = array(
            'title' => __('Secret API Key', 'wpd'),
            'desc' => __("You can find API key by visiting your Envato Account page, then clicking the My Settings tab. At the bottom of the page you'll find your account's API key.", 'wpd'),
            'name' => 'wpc-licence[envato-api-key]',
            'type' => 'text',
            'default' => '',
        );
        $purchase_code = array(
            'title' => __('Purchase Code', 'wpd'),
            'desc' => ' ' . __(' You can find your purchase code by following the instructions <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="blank">here</a>.', 'wpd'),
            'name' => 'wpc-licence[purchase-code]',
            'type' => 'text',
            'default' => '',
        );

        array_push($options, $licence_begin);
        array_push($options, $envato_username);
        array_push($options, $envato_api_secret);
        array_push($options, $purchase_code);
        array_push($options, $licence_end);
        echo o_admin_fields($options);
    }

    private function get_data_upgraders() {
        $options = array();
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpc-licence',
            'title' => __('Licence Settings', 'wpd'),
            'table' => 'options',
        );

        $end = array(
            'type' => 'sectionend',
            'id' => 'wpc-licence',
        );
        $envato_username = array(
            'title' => __('V5.x', 'wpd'),
            'desc' => __('This will run the data migration from previous versions to 5.0.', 'wpd'),
            'type' => 'custom',
            'callback' => array($this, 'get_v5_upgrader_buttons'),
        );

        array_push($options, $begin);
        array_push($options, $envato_username);
        array_push($options, $end);
        echo o_admin_fields($options);
    }

    function get_v5_upgrader_buttons() {
        ?>
        <input type="button" class="button button-primary run-wpd-upgrader" data-version="5" value="Run" style="float: left;"> 
        <div class="wpd-migrate-loading loading" style="display:none;float: left;"></div>
        <br>
        <br>
        <ul style="list-style: circle;margin-left: 20px;">
            <li><?php _e('Products configurations were introduced from the version v5.0 of the product designer', 'wpd'); ?></li>
            <li><?php _e('This process will extract all parameters from custom products and create the configurations accordingly.', 'wpd'); ?></li>
            <li><?php _e('If you already have configurations assigned to custom products, this process will create new configurations and assign them to existing products.', 'wpd'); ?></li>
        </ul>
        <?php
    }

    function get_facebook_redirection_url() {

        echo WPD_URL . 'includes/hybridauth/?hauth.done=Facebook';
    }

    function get_instagram_redirection_url() {

        echo WPD_URL . 'includes/hybridauth/?hauth.done=Instagram';
    }

    /**
     * Builds the settings page
     */
    function get_settings_page() {
        wpd_remove_transients();

        if (isset($_POST) && !empty($_POST)) {
            $this->save_wpc_tab_options();
            global $wp_rewrite;
            $wp_rewrite->flush_rules(false);
        }
        wp_enqueue_media();
        ?>
        <form method="POST">
            <div id="wpc-settings">
                <div class="wrap">
                    <h2><?php _e('Woocommerce Products Designer Settings', 'wpd'); ?></h2>
                </div>
                <div id="TabbedPanels1" class="TabbedPanels">
                    <ul class="TabbedPanelsTabGroup ">
                        <li class="TabbedPanelsTab " tabindex="1"><span><?php _e('General', 'wpd'); ?></span> </li>
                        <li class="TabbedPanelsTab" tabindex="2"><span><?php _e('Uploads', 'wpd'); ?> </span></li>
                        <li class="TabbedPanelsTab" tabindex="11"><span><?php _e('User Interface', 'wpd'); ?></span></li>
                    </ul>
                    <div class="TabbedPanelsContentGroup">
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
                                <?php
                                $this->get_general_settings();
                                ?>

                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
                                <?php
                                $this->get_uploads_settings();
                                ?>
                            </div>
                        </div>
                        <div class="TabbedPanelsContent">
                            <div class='wpc-grid wpc-grid-pad'>
                                <?php
                                $this->get_front_tools_settings();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="submit" value="<?php _e('Save', 'wpd'); ?>" class="button button-primary button-large mg-top-10-i">
        </form>
        <?php
    }

    /**
     * Save the settings
     */
    private function save_wpc_tab_options() {
        if (isset($_POST) && !empty($_POST)) {
            $checkboxes_map = array(
                'wpc-output-options' => array('wpc-generate-layers', 'wpc-generate-pdf', 'wpc-generate-zip', 'wpc-generate-svg'),
                'wpc-texts-options' => array('visible-tab', 'underline', 'text-spacing', 'bold', 'italic', 'text-color', 'background-color', 'outline', 'curved', 'font-family', 'font-size', 'outline-width', 'opacity', 'text-alignment', 'text-strikethrough', 'text-overline'),
                'wpc-shapes-options' => array('visible-tab', 'square', 'r-square', 'circle', 'triangle', 'polygon', 'star', 'heart', 'background-color', 'outline', 'outline-width', 'opacity'),
                'wpc-images-options' => array('visible-tab', 'lazy', 'emboss', 'opacity', 'sharpen', 'blur', 'sepia1', 'sepia2', 'invert', 'grayscale'),
                'wpc-designs-options' => array('visible-tab', 'saved', 'orders'),
                'wpc-upload-options' => array('visible-tab', 'grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'emboss', 'opacity', 'sharpen'),
                'wpc-toolbar-options' => array('visible-tab', 'grid', 'clear', 'delete', 'duplicate', 'send-to-back', 'bring-to-front', 'flipH', 'flipV', 'centerH', 'centerV', 'undo-redo'),
            );
            foreach ($checkboxes_map as $key_map => $values) {
                if (isset($_POST[$key_map])) {
                    $this->transform_checkbox_value($key_map, $checkboxes_map[$key_map]);
                } else {
                    foreach ($checkboxes_map[$key_map] as $option) {
                        $_POST[$key_map][$option] = 'no';
                    }
                }
            }

            foreach ($_POST as $key => $values) {
                update_option($key, $_POST[$key]);
            }

            $this->init_globals();
            ?>
            <div id="message" class="updated below-h2"><p><?php echo __('Settings successfully saved.', 'wpd'); ?></p></div>
            <?php
        }
    }

    private function get_custom_palette() {
        global $wpd_settings;
        $colors_options = $wpd_settings['wpc-colors-options'];
        $wpc_palette_type = get_proper_value($colors_options, 'wpc-color-palette', '');
        $palette_style = '';
        if (isset($wpc_palette_type) && $wpc_palette_type != 'custom') {
            $palette_style = "style='display:none;'";
        }
        $palette = get_proper_value($colors_options, 'wpc-custom-palette', '');
        $custom_palette = '<table class="form-table widefat" id="wpd-predefined-colors-options" ' . $palette_style . '>
                <tbody>
                    <tr valign="top">
                <th scope="row" class="titledesc"></th>
                    <td class="forminp">
                    <div class="wpc-colors">';
        if (isset($palette) && is_array($palette)) {
            foreach ($palette as $color) {
                $custom_palette .= '<div>
                                    <input type="text" name="wpc-colors-options[wpc-custom-palette][]"style="background-color: ' . $color . '" value="' . $color . '" class="wpc-color">
                                        <button class="button wpc-remove-color">Remove</button>
                                </div>';
            }
        }
        $custom_palette .= '</div>
                        <button class="button mg-top-10" id="wpc-add-color">Add color</button>
                    </td>
                    </tr>
                </tbody>
   </table>';
        return $custom_palette;
    }

    /**
     * Format the checkbox option in the settings
     *
     * @param type $option_name
     * @param type $option_array
     */
    private function transform_checkbox_value($option_name, $option_array) {
        foreach ($option_array as $option) {
            if (!isset($_POST[$option_name][$option])) {
                $_POST[$option_name][$option] = 'no';
            }
        }
    }

    /**
     * Alerts the administrator if the customization page is missing
     *
     * @global array $wpd_settings
     */
    function notify_customization_page_missing() {
        global $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        $hide_notices = get_proper_value($options, 'hide-requirements-notices', false);

        $wpc_page_id = $options['wpc_page_id'];
        $settings_url = get_bloginfo('url') . '/wp-admin/admin.php?page=wpc-manage-settings';
        if (!class_exists('WooCommerce')) {
            echo '<div class="error">
                   <p><b>Ouidah Product Designer: </b>WooCommerce is not installed on your website. You will not be able to use the features of the plugin.</p>
                </div>';
        } else {
            if (empty($wpc_page_id)) {
                echo '<div class="error">
                   <p><b>Ouidah Product Designer: </b>The design page is not defined. Please configure it in <a href="' . $settings_url . '">plugin settings page</a>: .</p>
                </div>';
            }
            if (!extension_loaded('zip') && !$hide_notices) {
                echo '<div class="error">
                   <p><b>Ouidah Product Designer: </b>ZIP extension not loaded on this server. You won\'t be able to generate zip outputs.</p>
                </div>';
            }
        }
    }

    function get_help_notices() {
        $screen = get_current_screen();
        if (isset($screen->base) && ( $screen->base == 'wpd_page_wpc-manage-fonts' )) {
            echo '<div class="wpd-info">
                   <p><b>' . __('Ouidah Product Designer: </b>Learn more about fonts management', 'wpd') . ' <a class="button" href="https://goo.gl/wZhfqv" target="_blank">' . __('here', 'wad') . '</a></p>
                </div>';
        }
    }

    function get_missing_parts_notice() {
        $screen = get_current_screen();

        if (isset($screen->post_type) && ( $screen->post_type == 'wpd-config' ) && isset($_GET['action']) && ( $_GET['action'] == 'edit' )) {
            $config_id = $_GET['post'];
            $metas = get_post_meta($config_id, 'wpd-metas', true);
            $parts = get_proper_value($metas, 'parts');
            if (!$parts) {
                echo '<div class="error wpd-error">
                   <p>' . __('This configuration has no part. Please set at least one in the parts section of this page.', 'wpd') . '</p>
                </div>';
            } else {
                foreach ($parts as $part) {
                    if (empty($part['name'])) {
                        echo '<div class="error wpd-error">
                            <p>' . __('There is at least one part with no name in your configuration. A part need a name to work properly.', 'wpd') . '</p>
                         </div>';
                        break;
                    }
                }
            }
        }
    }

    /**
     * Alerts the administrator if the minimum requirements are not met
     */
    function notify_minmimum_required_parameters() {
        global $wpd_settings;
        $general_options = get_proper_value($wpd_settings, 'wpc-general-options');
        $hide_notices = get_proper_value($general_options, 'hide-requirements-notices', false);
        if ($hide_notices) {
            return;
        }
        $message = '';
        $minimum_required_parameters = array(
            'memory_limit' => array(128, 'M'),
            'post_max_size' => array(128, 'M'),
            'upload_max_filesize' => array(128, 'M'),
        );
        foreach ($minimum_required_parameters as $key => $min_arr) {
            $defined_value = ini_get($key);
            $defined_value_int = str_replace($min_arr[1], '', $defined_value);
            if ($defined_value_int < $min_arr[0]) {
                $message .= "Your PHP setting <b>$key</b> is currently set to <b>$defined_value</b>. We recommand to set this value at least to <b>" . implode('', $min_arr) . '</b> to avoid any issue with our plugin.<br>';
            }
        }

        $edit_msg = __('How to fix this: You can edit your php.ini file to increase the specified variables to the recommanded values or you can ask your hosting company to make the changes for you.', 'wpd');

        if (!empty($message)) {
            echo '<div class="error">
                   <p><b>Ouidah Product Designer: </b><br>' . $message . '<br>
                       <b>' . $edit_msg . '</b></p>
                </div>';
        }

        $message = '';
        $permalinks_structure = get_option('permalink_structure');
        if (strpos($permalinks_structure, 'index.php') !== false) {
            $message .= 'Your permalinks structure is currently set to <b>custom</b> with index.php present in the structure. We recommand to set this value to <b>Post name</b> to avoid any issue with our plugin.<br>';
        }
        if (!empty($message)) {
            echo '<div class="error">
                   <p><b>Ouidah Product Designer: </b><br>' . $message . '</p>
                </div>';
        }
    }

    /**
     * Returns the number of occurences corresponding to a post meta key
     *
     * @global type $wpdb Database object
     * @param type $meta Meta to check
     * @param type $meta_key Is the meta a meta_key of a meta_value
     * @return int Number of occurences
     */
    private function get_meta_count($meta, $meta_key = true) {
        global $wpdb;
        if ($meta_key) {
            $sql_result = $wpdb->get_var(
                    "
                                SELECT count(*)
                                FROM $wpdb->posts p
                                JOIN $wpdb->postmeta pm on pm.post_id = p.id
                                WHERE p.post_type = 'product'
                                AND pm.meta_key = '" . $meta . "' 
                                AND p.post_status = 'publish'
                          "
            );
        } else {
            $sql_result = $wpdb->get_var(
                    "
                                SELECT count(*)
                                FROM $wpdb->posts p
                                JOIN $wpdb->postmeta pm on pm.post_id = p.id
                                WHERE p.post_type = 'product'
                                AND pm.meta_value like '%" . $meta . "%' 
                                AND p.post_status = 'publish'
                          "
            );
        }
        return $sql_result;
    }

    function wpc_add_custom_mime_types($mimes) {
        return array_merge(
                $mimes, array(
            'svg' => 'image/svg+xml',
            'ttf' => 'application/x-font-ttf',
            'icc' => 'application/vnd.iccprofile',
                )
        );
    }

    public
            function get_max_input_vars_php_ini() {
        $total_max_normal = ini_get('max_input_vars');
        $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", 'wpd');
        ?>

        <script type="text/javascript">
            var o_max_input_vars = <?php echo $total_max_normal; ?>;
            var o_max_input_msg = "<?php echo $msg; ?>";
        </script>         
        <?php
    }

    function get_license_activation_notice() {
        if (!get_option('wpd-license-key')) {
            ?>
            <div class="notice notice-error">
                <p><b>Ouidah Product Designer: </b><?php _e('You have not activated your license yet. Please activate it in order to get the plugin working.', 'sample-text-domain'); ?></p>
                <a class="button" id="wpd-activate"><?php _e('Activate', 'wpd'); ?></a><img style="display:none; width: 30px; height: 30px;" id="spinner" src="<?php echo plugin_dir_url(__FILE__) . 'images/spinner.gif'; ?> ">
                <p></p>
                <div id="license-message"></div>
            </div>
            <?php
        }
    }

    function activate_license() {
        global $wpd_settings;
        $licence_settings = get_proper_value($wpd_settings, 'wpc-licence');
        if (isset($licence_settings['purchase-code'])) {
            $purchase_code = get_proper_value($licence_settings, 'purchase-code');
            $site_url = get_site_url();
            $code = $_POST['code'];
            $plugin_id = WPD_PLUGIN_ID;
            $url = 'https://designersuiteforwp.com/service/olicenses/v1/license/?purchase-code=' . $purchase_code . '&siteurl=' . urlencode($site_url) . '&id=' . $plugin_id . '&code=' . $code;
            $args = array('timeout' => 60);
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
                die();
            }
            if (isset($response['body'])) {
                $answer = $response['body'];
            }

            if (is_array(json_decode($answer, true))) {
                $data = json_decode($answer, true);
                update_option('wpd-license-key', $data['key']);
                echo '200';
            } else {
                echo $answer;
            }
        } else {
            echo ( "Purchase code not found. Please, set your purchase code in the plugin's settings. " );
        }

        die();
    }

    function o_verify_validity() {
        $licence_settings = get_option('wpc-licence');
        if (isset($licence_settings['purchase-code']) && $licence_settings['purchase-code'] != '') {
            $purchase_code = get_proper_value($licence_settings, 'purchase-code');
            $site_url = get_site_url();
            $plugin_id = WPD_PLUGIN_ID;
            $url = 'https://designersuiteforwp.com/service/olicenses/v1/checking/?purchase-code=' . $purchase_code . '&siteurl=' . urlencode($site_url) . '&id=' . $plugin_id;
            $args = array('timeout' => 60);
            $response = wp_remote_get($url, $args);

            if (!is_wp_error($response)) {
                if (isset($response['body']) && intval($response['body']) != 200) {
                    delete_option('wpd-license-key');
                }
            }
        } else {
            if (get_option('wpd-license-key')) {
                delete_option('wpd-license-key');
            }
        }
    }

    function wpd_subscribe() {
        $email = $_POST['email'];

        if (preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $email)) {
            $url = 'https://designersuiteforwp.com/service/osubscribe/v1/subscribe/?email=' . $email;
            $args = array('timeout' => 60);
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
                die();
            }
            if (isset($response['body'])) {
                $answer = $response['body'];
                if ($answer == 'true') {
                    update_option('o-wpd-subscribe', 'subscribed');
                    echo $answer;
                } else {
                    echo $answer;
                }

                die();
            }
        } else {
            echo 'Please enter a valid email address';
            die();
        }
    }

    function wpd_get_subscription_notice() {

        if (is_wpd_admin_screen()) {

            if (!get_option('o-wpd-subscribe') && get_transient('wpd-hide-notice') != 'hide') {
                ?>
                <div id="subscription-notice" class="notice notice-info">

                    <div id="plug-logo-text" >
                        <img id="plug-logo" style="height:50px; width: 50px"src="<?php echo WPD_URL; ?>/admin/images/wpd-logo.png">
                        <p> 
                            <?php _e('<strong>Woocommerce Products Designer</strong>: Sign up now to receive new releases notices and important bugs fixes <br> directly into your inbox! ', 'wpd'); ?>
                        </p>

                    </div>

                    <div id="plug-sucribe-form">
                        <input type="email" id="o_user_email" name="usermail" placeholder="<?php _e('Your email here', 'wpd'); ?>">
                        <img id="wpd-subscribe-loader" style="display:none;" src="<?php echo WPD_URL; ?>/admin/images/loader.gif" >
                        <button id="wpd-subscribe" class="button button-primary"><?php _e('Subscribe', 'wpd'); ?></button>
                        <a id="wpd-dismiss"><?php _e('Not now', 'wpd'); ?></a>
                    </div>
                </div>
                <?php
            }
            ?>
            <div id="subscription-success-notice" class="notice notice-info is-dismissible" style="display:none;">
                <img src="<?php echo WPD_URL; ?>/admin/images/wpd-logo.png">
                <div> <?php _e('<strong>Woocommerce Products Designer</strong>: Thank you for subscribing! ', 'wpd'); ?></div>
            </div>
            <?php
        }
    }

    function wpd_hide_notice() {
        set_transient('wpd-hide-notice', 'hide', 2 * WEEK_IN_SECONDS);
        echo 'ok';
        die();
    }
    
    function run_wpc_db_updates_requirements() {
	$v7_upgrade = get_option( 'wpd-db-version' );
	if ( ( version_compare( $v7_upgrade, '7.0', '<' ) || $v7_upgrade == false )
	) {
	    ?>
	    <div class="updated" id="wpc-updater-container">
	        <strong><?php echo _e( 'Ouidah Product Designer database update required', 'wpd' ); ?></strong>
	        <div>
		    <?php
		    echo _e(
		    "Hi! This version of the Ouidah Product Designer made some changes in the way it's data are stored. <br>In order to work properly, we just need you to:"
		    . '<ol>'
		    . "<li><span style='font-weight: bold; color: red;'>Backup</span> your entire website in case something goes wrong during the process.</li>"
		    . '<li>Click on the "Run Updater" button to move your old settings to the new structure. </li>'
		    . '</ol>  ', 'wpd'
		    );
		    ?>

	    	<input type="button" value="<?php echo _e( 'Run the updater', 'wpd' ); ?>" id="wpc-run-updater" class="button button-primary"/>
	    	<br>
	    	<strong style='color: red;'><?php echo _e( 'Note:', 'wpd' ); ?> </strong> <?php echo _e( 'If something goes wrong, this updater will still be available in <strong>WPD > Settings > Updaters</strong>', 'wpd' ); ?>
	    	<div class="loading" style="display:none;"></div>
	        </div>
	    </div>
	    <style>
	        #wpc-updater-container
	        {
	    	padding: 3px 17px;
	    	font-size: 15px;
	    	line-height: 36px;
	    	margin-left: 0px;
	    	border-left: 5px solid #e14d43 !important;
	        }
	        #wpc-updater-container.done
	        {
	    	border-color: #7ad03a !important;
	        }
	        #wpc-run-updater {
	    	background: #e14d43;
	    	border-color: #d02a21;
	    	color: #fff;
	    	-webkit-box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
	    	box-shadow: inset 0 1px 0 #ec8a85,0 1px 0 rgba(0,0,0,.15);
	    	text-shadow: none;
	        }

	        #wpc-run-updater:focus, #wpc-run-updater:hover {
	    	background: #dd362d;
	    	border-color: #ba251e;
	    	color: #fff;
	    	-webkit-box-shadow: inset 0 1px 0 #e8756f;
	    	box-shadow: inset 0 1px 0 #e8756f;
	        }
	        .loading
	        {
	    	background: url("<?php echo WPD_URL; ?>/admin/images/spinner.gif") 10% 10% no-repeat transparent;
	    	background-size: 111%;
	    	width: 32px;
	    	height: 40px;
	    	display: inline-block;
	        }
	    </style>
	    <script>
	        //jQuery('.loading').hide();
	        jQuery('#wpc-run-updater').click('click', function () {
	    	var ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
	    	if (confirm("It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?")) {
	    	    jQuery('.loading').show();
	    	    jQuery.post(
	    		    ajax_url,
	    		    {
	    			action: 'run_updater',
	    			version: 7
	    		    },
	    		    function (data) {
	    			jQuery('.loading').hide();
	    			jQuery('#wpc-updater-container').html(data);
	    			jQuery('#wpc-updater-container').addClass("done");
	    		    }
	    	    );
	    	}

	        });
	    </script>
	    <?php
	}
    }
    /**
     * Runs the database upgrade
     */
    function run_wpd_updater() {
	$target = filter_input( INPUT_POST, 'version' );
	switch ( $target ) {
	    case 7:
		$config	 = new WPD_Config();
		$config->migrate_metas_to_v7();
		$message = __( 'Your database has been successfully updated.', 'wpd' );
		break;
	}

	echo $message;
	die();
    }

}
