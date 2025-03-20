<?php
/*
Plugin Name: Incassomachtigen voor Woocommerce via PayIBAN
Plugin URI: http://vansteinengroentjes.nl
Description: Payment gateway for Woocommerce, enables payments by SEPA mandate. – powered by PayIBAN. 
Version: 1.3.7
Author: van Stein en Groentjes
Author URI: https://vansteinengroentjes.nl
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



//Cron part to check payments made in PayIBAN
load_plugin_textdomain( 'machtiging-free-payiban', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 


/**
 * Adds a box to the main column on the Post and Page edit screens to let the user select the recurrent period
 */
add_action( 'add_meta_boxes', 'payiban_product_period_mb' );
function payiban_product_period_mb() {
    add_meta_box(
        'wc_product_period_mb',
        __( 'Digital Mandate: Recurring payment option.', 'machtiging-free-payiban' ),
        'payiban_product_period_inner_mb',
        'product',
        'normal',
        'high'
    );
}


function payiban_change_cart($price,$cart_item,$cart_item_key) {
	$_productID= $cart_item['data']->get_id(); 
	$_real_price = $cart_item['data']->get_price()* $cart_item['quantity'];
	$all_values = array("eenmalig"=>""
					,"dag" => __("per day", 'machtiging-free-payiban')
					,"week" => __("per week", 'machtiging-free-payiban')
					,"maand" => __("per month", 'machtiging-free-payiban')
					,"twee maanden" => __("bimonthly", 'machtiging-free-payiban')
					,"kwartaal" => __("quarterly", 'machtiging-free-payiban')
					,"halfjaar" => __("semi-annualy", 'machtiging-free-payiban')
					,"jaar" => __("annualy", 'machtiging-free-payiban')
					);
	$period_number = get_post_meta($_productID, "product_numberofperiod", true);
	
	$price_ori = $_real_price;
	$initial_price = "";
	
	$perc = get_post_meta( $_productID, 'product_discountperc', true );
	$fixed = get_post_meta( $_productID, 'product_pricefixed', true );
	if ($perc != "" && $perc != 0){
		$price = $_real_price;
		if ($perc > 0 && $perc <= 100){
			$price = $price * (1.0- floatval($perc)/100.0);
		}
		$initial_price = " (first term)";
	}elseif ($fixed != "" && $fixed > 0 ){
		$price = $fixed;
		$initial_price = " (first term)";
	}else{
		$price = $_real_price;
	}
	$price = wc_price($price);
	
	


	if ($period_number > 0){
	  echo $price.$initial_price.'
			'.$all_values[get_post_meta($_productID, "product_period", true)].'
			';
	}else{
		echo $price.$initial_price;
	}
}
add_action( 'woocommerce_cart_item_price', 'payiban_change_cart', 1, 3 );


function payiban_change_summary() {
	$_productID= get_the_ID();  
	$all_values = array("eenmalig"=>""
					,"dag" => __("per day", 'machtiging-free-payiban')
					,"week" => __("per week", 'machtiging-free-payiban')
					,"maand" => __("per month", 'machtiging-free-payiban')
					,"twee maanden" => __("per two months", 'machtiging-free-payiban')
					,"kwartaal" => __("per quarter", 'machtiging-free-payiban')
					,"halfjaar" => __("per six months", 'machtiging-free-payiban')
					,"jaar" => __("per year", 'machtiging-free-payiban')
					);
	$period_number = get_post_meta($_productID, "product_numberofperiod", true);
	if ($period_number > 0){
		$initial_price = "";
		$perc = get_post_meta( $_productID, 'product_discountperc', true );
    	$fixed = get_post_meta( $_productID, 'product_pricefixed', true );
    	if ($perc > 0 && $perc <= 100){
    		$initial_price = __(" first term with ","machtiging-free-payiban").$perc.__("% discount.","machtiging-free-payiban");
    	}
    	if ($fixed > 0 ){
    		$initial_price = __(" first term price: ","machtiging-free-payiban").wc_price($fixed).__(".","machtiging-free-payiban");
    	}
					
		echo '
		<div class="product-period">
			'.$all_values[get_post_meta($_productID, "product_period", true)];
			if ($period_number != "" && $period_number > 0){
				echo ' ('.$period_number.' times)'.$initial_price;
			}
			echo '
		</div>
		';
	}else{
		echo '
		<div class="product-period">'.$initial_price.'
		</div>
		';
	}

}
add_action( 'woocommerce_single_product_summary', 'payiban_change_summary', 10 );

function payiban_change_summary_checkout(){
  global $woocommerce;
  $cart = WC()->cart;
  $discount_fixed = 0;
  $price_fixed = -1;
  $total_price = $cart->get_total();
  foreach ( $cart->cart_contents as $product ) {
		$value3 = get_post_meta( $product["product_id"], 'product_discountperc', true );
		$value4 = get_post_meta( $product["product_id"], 'product_pricefixed', true );
		if ($value3 > 0 && $value3 <= 100){
			$discount_fixed += $product["data"]->get_price() * ($value3/100.0);
		}
		if ($value4 != "" && $value4 != 0){
			$price_fixed = $value4;
		}
        
    }
    if ($price_fixed >= 0){
		echo __("The first term price will be ","machtiging-free-payiban").wc_price( $price_fixed ).".";
    }
    elseif ($discount_fixed>0){
    	echo __("A first term discount will be applied of ","machtiging-free-payiban").wc_price( -$discount_fixed ).".";
    }
 }

add_action( 'woocommerce_review_order_before_payment', 'payiban_change_summary_checkout', 1 );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function payiban_product_period_inner_mb( $post ) {

	  // Add an nonce field so we can check for it later.
	  wp_nonce_field( plugin_basename( __FILE__ ), 'wc_product_period_inner_mb_nonce' ); 
	  
	  /*
	   * Use get_post_meta() to retrieve an existing value
	   * from the database and use the value for the form.
	   */
	  $value = get_post_meta( $post->ID, 'product_period', true );

	  $value2 = get_post_meta( $post->ID, 'product_numberofperiod', true );

	  $value3 = get_post_meta( $post->ID, 'product_discountperc', true );

      $value4 = get_post_meta( $post->ID, 'product_pricefixed', true );
      if ($value4 == ""){
      	$value4 = 0;
      }
	  

		/*	‘eenmalig’ = one off
		‘maand’ = monthly
		‘twee maanden’ = two monthly’
		‘kwartaal’ = quarterly
		‘halfjaar’ = every six months
		‘jaar’ = yearly*/
	  $all_values = array("eenmalig","dag","week","maand","twee maanden","kwartaal","halfjaar","jaar");
	  $all_names = array(__("one off", 'machtiging-free-payiban'),__("every day", 'machtiging-free-payiban'),__("every week", 'machtiging-free-payiban'),__("every month", 'machtiging-free-payiban'),__("two monthly", 'machtiging-free-payiban'),__("quarterly", 'machtiging-free-payiban'),__("every six months", 'machtiging-free-payiban'),__("yearly", 'machtiging-free-payiban'));

	  echo '<p>'.__( 'Specify the billing period of this product.<br/>You can use this option to use periodic billing with the Digital Mandate by PayIBAN payment gateway, without the need of the plugin: Woocommerce Subscriptions.', 'machtiging-free-payiban' ).' </p>';
	  echo '<label for="product_period_field">';
	  echo __( "Recurring period:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<select id="product_period_field"  name="product_period_field">';
	  for ($i=0;$i<count($all_values);$i++){
	  	if ($value == $all_values[$i]){
	  		echo '<option value="'.$all_values[$i].'" selected>'.$all_names[$i].'</option>';
	  	}else{
	  		echo '<option value="'.$all_values[$i].'">'.$all_names[$i].'</option>';
	  	}
			  
	  }
	  			
	  echo '</select>';

	  echo '<p>'.__( 'Specify the number of terms that will be billed, put 0 for infinite.', 'machtiging-free-payiban' ).' </p>';
	  echo '<label for="product_numberofperiod_field">';
	  echo __( "Number of periods:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<input type="text" value="'.$value2.'" id="product_numberofperiod_field"  name="product_numberofperiod_field">';
	  

	  echo '<p>'.__( 'Specify an optional discount for the first term in percentage or set a fixed price for the first period (only valid for PayIBAN payment gateway). Leave at 0 or -1 if you do not want to use this function, specifying both results in percentage having precedence over the fixed amount.', 'machtiging-free-payiban' ).' </p>';
	  echo '<label for="product_discountperc_field">';
	  echo __( "First term discount in %:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<input type="number" value="'.$value3.'" id="product_discountperc_field"  name="product_discountperc_field">';
	  echo '<br/><label for="product_discountperc_field">';
	  echo __( "Fixed first term price:", 'machtiging-free-payiban' );
	  echo '</label> ';
	  echo '<input type="number" value="'.$value4.'" id="product_pricefixed_field"  name="product_pricefixed_field">';
	  
}//function



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function payiban_product_period_save_postdata( $post_id ) {
  /*
   * We need to verify this came from the our screen and with proper authorization,
   * because save_post can be triggered at other times.
   */
  // Check if our nonce is set.
  if ( ! isset( $_POST['wc_product_period_inner_mb_nonce'] ) )
    return;
  $nonce = $_POST['wc_product_period_inner_mb_nonce'];
  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) )
      return;
  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;
  // Check the user's permissions.
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }
  /* OK, its safe for us to save the data now. */
  // Sanitize user input.
  $mydata = sanitize_text_field( $_POST['product_period_field'] );
  // Update the meta field in the database.
  update_post_meta( $post_id, 'product_period', $mydata );

  $mydata = sanitize_text_field( $_POST['product_numberofperiod_field'] );
  // Update the meta field in the database.
  update_post_meta( $post_id, 'product_numberofperiod', $mydata );

  $mydata = floatval(sanitize_text_field( $_POST['product_discountperc_field'] ));
  update_post_meta( $post_id, 'product_discountperc', $mydata );

  $mydata = floatval(sanitize_text_field( $_POST['product_pricefixed_field'] ));
  update_post_meta( $post_id, 'product_pricefixed', $mydata );
}
add_action( 'save_post', 'payiban_product_period_save_postdata' );



//pre_post_update
/**
 * Check if WooCommerce is active
 **/
if ( true || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	/**
	* Add welcome screen
	**/
	add_action('admin_menu', 'machtigingfree_welcome_screen_page');
	function machtigingfree_welcome_screen_page(){
		add_dashboard_page('Welcome', 'Welcome', 'read', 'machtiging-free-plugin-welcome', 'machtiging_free_welcome_page');
	}
	function machtiging_free_welcome_page(){
		echo '<iframe src="'.__("https://www.payiban.com/welcome/",'machtiging-free-payiban' ).'" style="position: absolute; height: 1000px; width:100%; border: none"></iframe>';
	}
	add_action('activated_plugin','machtiging_free_welcome_redirect');
	function machtiging_free_welcome_redirect($plugin)
	{
		if($plugin=='payiban-digital-mandate-woocommerce/ditigal-mandate-payiban.php') {
			//create new account
			$checkurl = "https://api.payiban.com/webservice/index.php?user=12416&password=9kdk&request=check_account&email=".get_option( 'admin_email' );

			$xmlresponse = file_get_contents( $checkurl );

			//de response verwerken

			$result = simplexml_load_string( $xmlresponse );
			$error_message=$result->error;
			if ($error_message != '' || $result->account=="yes"){
				//fail
				wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_payibanmachtigingfree'));
				die();
			}else{

				$acronym = "";
				//account does not exist yet, create one
				$url = 'https://api.payiban.com/webservice/index.php'; // basis url van elke xmlstring request
				$xml = '<?xml version="1.0" encoding="UTF-8"?> <webservice> <user>12416</user> <password>9kdk</password> <request>add_account</request> <test>nee</test> <output>xml</output> <data> <item> <bedrijfsnaam></bedrijfsnaam> 
            <geslacht>man</geslacht> 
            <voorletters></voorletters>
            <tussenvoegsel></tussenvoegsel> 
            <achternaam></achternaam>
            <straat></straat> 
            <postcode></postcode>
            <woonplaats></woonplaats>
            <emailadres>'.get_option( 'admin_email' ).'</emailadres>
            <mobielnummer>31612345678</mobielnummer> </item> </data> </webservice>';

				$xmlresponse = file_get_contents( $url.'?xmlstring='.urlencode( $xml ) );
				$result = simplexml_load_string( $xmlresponse );
				if (empty($result->error) ){
					//we get back: <subaccount_api_username>12345</subaccount_api_username> <subaccount_api_password>67890</subaccount_api_password>
					
					//no clue if this works
					$welcome_message = "Dear user,

Thank you for using the PayIBAN plugin. Your free PayIBAN account is created.
Your credentials for the plugin are available at your PayIBAN account.
You can login at your PayIBAN account at: https://www.payiban.com/login/ 

With your email address and please set your password here:  https://www.payiban.com/password-recovery/

Kind regards,
PayIBAN

---- Dutch below ---

Beste gebruiker,

Bedankt voor het gebruiken van de PayIBAN plugin. Uw gratis PayIBAN account is aangemaakt.
U kunt de API gegevens voor de plugin vinden op uw PayIBAN account pagina.

U kunt hier inloggen op uw PayIBAN account: https://www.payiban.com/login/ 

Met uw email adres en maak hier uw wachtwoord aan: https://www.payiban.com/nl/wachtwoord-aanvragen/

Vriendelijke groet,
PayIBAN
";
					
					wp_mail(get_option( 'admin_email' ), "Welcome to PayIBAN", $welcome_message);
					wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_payibanmachtigingfree'));
					die();
					//update_option( "payiban_api_user", $api_user);
					//update_option( "payiban_api_pass", $api_pass );
				}
			}



			wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_payibanmachtigingfree'));
			die();
		}
	}

	add_action( 'admin_head', 'payibanmachtiging_remove_menu_entry' );
	function payibanmachtiging_remove_menu_entry(){
		remove_submenu_page( 'index.php', 'machtiging-plugin-welcome' );
	}


	add_action( 'plugins_loaded', 'machtiging_payibanfree_init', 0 );
	function machtiging_payibanfree_init() {
		

		if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
		if ( !function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH.'/wp-admin/includes/plugin.php';
		}


		class WC_payIBANmachtigingfree extends WC_Payment_Gateway{

			public function __construct() {
				global $woocommerce;

				$this -> id = 'payibanmachtigingfree';
				$this -> method_title = 'PayIBAN Digital Mandate for Woocommerce';
				$this -> icon = plugins_url( plugin_basename( dirname( __FILE__ ) ).'/payibanlogo.png', dirname( __FILE__ ) );
				$this -> has_fields = false;

				$this -> init_form_fields();
				$this -> init_settings();

				$this -> title = $this -> settings['title'];
				$this -> description = $this -> settings['description'];

				$this -> test_mode = 'no';
				$this -> merchant_id = $this -> settings['merchant_id'];
				$this -> merchant_pass = $this -> settings['merchant_pass'];
				$this -> incassant = $this -> settings['incassant'];
				$this -> liveurl = 'not needed';
				$this -> merchant_mail = $this -> settings['merchant_mail'];
				$this -> returnurl = $this -> settings['returnurl'];
				
				$this -> msg['message'] = '';
				$this -> msg['class'] = '';

				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
				} else {
					add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
				}
				add_action('woocommerce_api_wc_payibanmachtigingfree', array( $this, 'payiban_check_response'));
			}

			/*Function to set the options for the admin, generates the fields for the form*/
			function init_form_fields() {
				$this -> form_fields = array(
					
					'enabled' => array(
						'title' => __( 'Enable/Disable', 'machtiging-free-payiban' ),
						'type' => 'checkbox',
						'label' => __( 'Enable payIBAN Mandate Payment Module.', 'machtiging-free-payiban' ),
						'default' => 'no' ),
					'title' => array(
						'title' => __( 'Title:', 'machtiging-free-payiban' ),
						'type'=> 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'machtiging-free-payiban' ),
						'default' => __( 'Digital Mandate', 'machtiging-free-payiban' ) ),
					'description' => array(
						'title' => __( 'Description:', 'machtiging-free-payiban' ),
						'type' => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'machtiging-free-payiban' ),
						'default' => __( 'Pay securely by using your IBAN bank account with PayIBAN.', 'machtiging-free-payiban' ) ),
					'returnurl' => array(
						'title' => __( 'Return url:', 'machtiging-free-payiban' ),
						'type' => 'text',
						'description' => __( 'Set the url for the thank you page, leave empty to use the default woocommerce return url.', 'machtiging-free-payiban' ),
						'default' => __( '', 'machtiging-free-payiban' ) ),
					'merchant_id' => array(
						'title' => __( 'Username', 'machtiging-free-payiban' ),
						'type' => 'text',
						'description' => __( 'Check username and password within section API and plugin of your account.  <a href="https://www.payiban.com/register/">Click to register your free account.</a>', 'woocommerce-payiban' ),
						'default' => get_option( "payiban_api_user", ""),
						 ),
					'merchant_pass' => array(
						'title' => __( 'Password', 'machtiging-free-payiban' ),
						'type' => 'text',
						'description' =>  __( 'API password given to you by payIBAN.<br/>Need support: <a href="mailto:support@payiban.com">support@payiban.com</a>.', 'woocommerce-payiban' ),
						'default' => get_option( "payiban_api_pass", "" ),
						),
					'incassant' => array(
						'title' => __( 'Mandate form name', 'machtiging-free-payiban' ),
						'type' => 'text',
						'description' => __( 'Check username within section Mandate form of your payiban account.  <a href="https://www.payiban.com/register/">Click to register your free account.</a>', 'machtiging-free-payiban' ),
						'default' => get_option( "payiban_api_user", ""),
						 ),
					'merchant_mail' => array(
						'title' => __( 'Admin Email', 'machtiging-free-payiban' ),
						'type' => 'text',
						'description' =>  __( '!Important: Email adress for notices about cancelled subscriptions', 'machtiging-free-payiban' ),
						'default' => get_option( 'admin_email' ) )
				);
			}

			public function create_url( $args ) {
		        $base_url = 'https://www.payiban.com/nl/';

		        $base_url = add_query_arg( 'wc-api', 'software-api', $base_url );
		        return $base_url . '&' . http_build_query( $args );
			}

			public function admin_options() {
				if (!current_user_can('manage_options')) {
		            return;
		        }

				echo '<h3>'.__( 'PayIBAN Digital Mandate for Woocommerce', 'machtiging-free-payiban' ).'</h3>';
				echo '<table class="form-table">';
				
				// Generate the HTML For the settings form.
				$this -> generate_settings_html();
				echo '</table>';
			}



			/**
			 * Process the payment and return the result
			 * */
			function process_payment( $order_id ) {
				global $woocommerce;

				$order = new WC_Order( $order_id );

				//Mark as on-hold we are waiting for the payment
				$order->update_status('on-hold',__('Awaiting SEPA payment.', 'machtiging-free-payiban' ));

				//redirect to the machtiging form

				// API account settings
				$api_username = $this ->merchant_id;
				$api_test = 'nee';
				$api_aantal_termen = 0;
				if ( $this->test_mode == 'no' ) {
					$api_test = 'nee';
				}else {
					$api_test = 'ja';
				}
				$api_periode = 'eenmalig';
				$price = $order->get_total();
				$initial_price = $price;

				//check cart
				$period_check = "";
				$period_number = 0;
				$first_discount_perc = 0;
				$product_pricefixed = -1;
				foreach ( $woocommerce->cart->cart_contents as $product ) {
					$temp_p = get_post_meta($product['product_id'], "product_period", true);
					$temp_n = get_post_meta($product['product_id'], "product_numberofperiod", true);

					$value3 = get_post_meta( $product['product_id'], 'product_discountperc', true );
      				$value4 = get_post_meta( $product['product_id'], 'product_pricefixed', true );
					
					if ($period_check == ""){
						$period_check = $temp_p;
						$period_number = $temp_n;
					}
					if ($first_discount_perc == 0 || $first_discount_perc==""){
						$first_discount_perc = $value3;
						if ($value4 != ""){
							$product_pricefixed = $value4;
						}
					}
		            if ($period_check != $temp_p || $period_number != $temp_n){
		            	wc_add_notice( __( 'Only one type of recurring product can be bought at once. Please remove some of the items from the cart.', 'woocommerce-payiban-free' ) ,'error');
						return false;
		            }
		        }
		        

				//check special field
				$api_periode = $period_check;
				if (intval($period_number) > 0){
					$api_aantal_termen = $period_number;
				}
				$all_values = array("eenmalig","dag","week","maand","twee maanden","kwartaal","halfjaar","jaar");
				if (! in_array($api_periode, $all_values)) {
					//One time payment
					$api_periode = 'eenmalig';
				}
				

				// Het bedrag van de machtiging
				$api_bedrag = $price;
				$api_first_bedrag = $initial_price;
				if ($first_discount_perc > 0 && $first_discount_perc <= 100){
					$api_first_bedrag = $api_first_bedrag * (1.0- $first_discount_perc/100.0);
				}if ($product_pricefixed > 0 ){
					$api_first_bedrag = $product_pricefixed;
				}
				


				$year = date( 'Y' );
				$month = date( 'm' );
				$monthint = intval( date( 'n' ) );
				$datum = date( 'Y-m-d' );
				$day = intval( date( 'j' ) );

				//get voorletters from first_name
				$words = preg_split("/[\s,_-]+/", sanitize_text_field($_POST['billing_first_name']));
				$acronym = "";

				foreach ($words as $w) {
					$acronym .= $w[0]. ".";
				}
				$acronym = trim($acronym);

				$first_name = sanitize_text_field($_POST['billing_first_name']);
				// Posts vanuit het formulier
				$api_toevoegdatum   = urlencode($datum);
				$api_geslacht       = urlencode("man"); //$_POST['geslacht'];
				$api_voorletters    = urlencode($acronym); //$_POST['billing_first_name'];
				$api_tussenvoegsel  = urlencode(""); //$_POST['tussenvoegsel'];
				$api_achternaam     = urlencode($_POST['billing_last_name']);
				$api_straat         = urlencode($_POST['billing_address_1']);
				$api_postcode       = urlencode($_POST['billing_postcode']);
				$api_woonplaats     = urlencode($_POST['billing_city']);
				$api_emailadres     = urlencode($_POST['billing_email']);
				//$api_iban           = urlencode($_POST['iban']);
				$api_mobielnummer   = urlencode($_POST['mobilephone']);
				$api_referentie     = urlencode($order_id);
				$api_incassant      = urlencode($this->incassant);

				$api_kenmerk        = urlencode($first_name. ', Thank you for your order. Order-number: '. $order_id);
				$callbackurl 		= urlencode(str_replace('https:','http:',home_url('/')."wc-api/wc_payibanmachtigingfree/")."?status={0}&mandaatid={1}&referentie={2}");
				$returnurl = $this->returnurl;
				if ($this->returnurl == ""){
					$returnurl 		= urlencode($this->get_return_url( $order )."&status={0}&mandaatid={1}&referentie={2}");
				}

				

				//build url
				$payment_url = 	"https://www.payiban.com/machtigen/".$api_incassant."/?periode=".urlencode($api_periode)."&std_bedrag=".urlencode($api_bedrag)."&bedrijfsnaam=&geslacht=man&voorletters=".$api_voorletters."&tussenvoegsel=&achternaam=".$api_achternaam."&straat=".$api_straat."&postcode=".$api_postcode."&woonplaats=".$api_woonplaats."&emailadres=".$api_emailadres."&mobielnummer=".$api_mobielnummer."&ingangsdatum=".$api_toevoegdatum."&bedrag=".urlencode($api_first_bedrag)."&referentie=".$api_referentie."&incassokenmerk=".$api_kenmerk."&callbackurl=".$callbackurl."&returnurl=".$returnurl;

				update_post_meta( $order_id, 'Payment_url', $payment_url);

				return array(
					'result'=>'success',
					'redirect' => $payment_url
				);
				
			}

			
			function payiban_check_response(){
				//global $woocommerce;
				//echo "hoi";
				if (isset($_GET['status'])){
					//echo $_GET['status'];
					//if ($_REQUEST['status'] == "SUCCESS"){
						$referentie = $_GET['referentie'];
						$mandaatid = $_GET['mandaatid'];
						update_post_meta( $referentie, 'Mandaatid', $mandaatid);
						//update_post_meta( $referentie, 'IBAN', $api_iban);
						$order = new WC_Order( $referentie );
						$order->payment_complete();
						//print_r($order);
						// Return thank you page redirect
						//$redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url()."/":get_permalink($this->redirect_page_id);
						
						$api_username = $this ->merchant_id;
						$api_password = $this ->merchant_pass;
						//here we can get the other information from the API
						$url = 'https://api.payiban.com/webservice/index.php?user='.$api_username.'&password='.$api_password.'&request=machtiging&kolom=mandateid&waarde='.$mandaatid; // basis url van elke xmlstring request
					
						$xmlresponse = file_get_contents( $url );
						//de response verwerken
						$result = simplexml_load_string( $xmlresponse );
						$error_message=$result->error;
						
						if ($error_message == ''){
							$current_iban = $result->machtiging->iban;
							$current_bic = $result->machtiging->bic;
							$current_gebruikers_id = $result->machtiging->gebruikers_id;
							$current_rekening = $result->machtiging->rekeningnummer;
							$current_period = $result->machtiging->frequentie;
							$current_price = $result->machtiging->bedrag;
						}
						update_post_meta( $referentie, 'IBAN', (string)$current_iban);
						update_post_meta( $referentie, 'BIC', (string)$current_bic);
						update_post_meta( $referentie, 'Accountholder', (string)$current_gebruikers_id);
						update_post_meta( $referentie, 'Rekening', (string)$current_rekening);
						update_post_meta( $referentie, 'PayIBAN periode', (string)$current_period);
						update_post_meta( $referentie, 'PayIBAN prijs', (string)$current_price);
						
						
						die();
						return array(
							'result' => 'success',
							'redirect' => $this->get_return_url($order)
						);
					//}
				}else{
					$referentie = $_GET['referentie'];
					if ($referentie){
						$order = new WC_Order( $referentie );
						if ($order){
							$order->update_status("failed");
						}
					}
				}
			}

			


			// get all pages HELPER FUNCTION
			function get_pages( $title = false, $indent = true ) {
				$wp_pages = get_pages( 'sort_column=menu_order' );
				$page_list = array();
				if ( $title ) $page_list[] = $title;
				foreach ( $wp_pages as $page ) {
					$prefix = '';
					// show indented child pages?
					if ( $indent ) {
						$has_parent = $page->post_parent;
						while ( $has_parent ) {
							$prefix .=  ' - ';
							$next_page = get_page( $has_parent );
							$has_parent = $next_page->post_parent;
						}
					}
					// add to page list array array
					$page_list[$page->ID] = $prefix . $page->post_title;
				}
				return $page_list;
			}

			
		}



		

		/**
		 * Add the Gateway to WooCommerce
		 * */
		function woocommerce_add_payibansepafree_gateway( $methods ) {
			$methods[] = 'WC_payIBANmachtigingfree';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_payibansepafree_gateway' );

	}
}

