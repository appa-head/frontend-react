<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://designersuiteforwp.com
 * @since             1.0
 * @package           Wpd
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Products Designer by ORION
 * Plugin URI:        https://designersuiteforwp.com
 * Description:       The Interactive Way to Customize & Sell Your Products Online!
 * Version:           4.3.1
 * Author:            ORION
 * Author URI:        http://orionorigin.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpd
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('WPD_VERSION', '4.3.1');
define('WPD_URL', plugins_url('/', __FILE__));
define('WPD_DIR', dirname(__FILE__));
define('WPD_MAIN_FILE', 'woocommerce-product-designer/wpd.php');
define('WPD_PLUGIN_NAME', 'Woocommerce Products Designer by ORION');
define('WPD_PLUGIN_ID', 7818029);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpd-activator.php
 */
function activate_wpd() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wpd-activator.php';
    Wpd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpd-deactivator.php
 */
function deactivate_wpd() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wpd-deactivator.php';
    Wpd_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wpd');
register_deactivation_hook(__FILE__, 'deactivate_wpd');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if (!function_exists('o_admin_fields')) {
    require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
}

require plugin_dir_path(__FILE__) . 'includes/class-wpd.php';

require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-clipart.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-template.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-editor.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-design.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-wpd-retarded-actions.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-wpd-config.php';

// Default skins
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'class-wpd-skin-default.php';
require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . 'porto-novo' . DIRECTORY_SEPARATOR . 'class-wpd-skin-porto-novo.php';
if ( wp_is_mobile() ) {
    require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . 'porto-novo-mobile' . DIRECTORY_SEPARATOR . 'class-wpd-skin-porto-novo-mobile.php';
}
if (!class_exists('TCPDF')) {
    require_once WPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
}


require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0
 */
function run_wpd() {

    $plugin = new Wpd();
    $plugin->run();
}

run_wpd();
