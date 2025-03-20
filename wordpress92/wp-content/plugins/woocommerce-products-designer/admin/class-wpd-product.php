<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wpd-product
 *
 * @author HL
 */
class WPD_Product {

    public $variation_id;
    public $root_product_id;
    public $product;
    public $settings;
    public $variation_settings;

    public function __construct($id) {
        if ($id) {
            $this->root_product_id = $this->get_parent($id);
            // If it's a variable product
            if ($id != $this->root_product_id) {
                $this->variation_id = $id;
            }
            // Simple product and others
            else {
                $this->variation_id = $this->root_product_id;
            }

            $this->product = wc_get_product($id);
            $config = get_post_meta($this->root_product_id, 'wpd-metas', true);

            if (isset($config[$this->variation_id])) {
                $config_id = $config[$this->variation_id]['config-id'];
                if ($config_id) {
                    $this->settings = get_post_meta($config_id, 'wpd-metas', true);
                    $product_metas = get_post_meta($this->root_product_id, 'wpc-metas', true);
                    if (isset($product_metas['related-products'])) {
                        $this->settings['related-products'] = $product_metas['related-products'];
                    }
                    if (isset($product_metas['related-quantities'])) {
                        $this->settings['related-quantities'] = $product_metas['related-quantities'];
                    }
                }
            }
        }
    }

    private function get_checkbox_value($values, $search_key, $default_value) {
        if (get_proper_value($values, $search_key, $default_value) == 1) {
            $is_checked = "checked='checked'";
        } else {
            $is_checked = '';
        }
        return $is_checked;
    }

    /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label() {
        ?>

        <li class="wpc_related_products_tab show_if_variable"><a href="#wpc_related_products_tab_data"><?php _e('Related Products / Quantities', 'wpd'); ?></a></li>
        <?php
    }

    /**
     * Adds the Custom column to the default products list to help identify which ones are custom
     *
     * @param array $defaults Default columns
     * @return array
     */
    function get_product_columns($defaults) {
        $defaults['is_customizable'] = __('Custom', 'wpd');
        return $defaults;
    }

    /**
     * Sets the Custom column value on the products list to help identify which ones are custom
     *
     * @param type $column_name Column name
     * @param type $id Product ID
     */
    function get_products_columns_values($column_name, $id) {
        if ($column_name === 'is_customizable') {
            $wpc_metas = get_post_meta($id, 'wpd-metas', true);
            if (isset($wpc_metas[$id]['config-id'])) {
                if (empty($wpc_metas[$id]['config-id'])) {
                    _e('No', 'wpd');
                } else {
                    _e('Yes', 'wpd');
                }
            } else {
                _e('No', 'wpd');
            }
        }
    }

    public function is_customizable() {
        return (!empty($this->settings) );
    }

    function get_product_tab_data() {
        $product_id = get_the_ID();
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        ?>
        <div id="wpc_related_products_tab_data" class="panel woocommerce_options_panel wpc-sh-triggerable">
            <div class="related-products-container">
                <?php
                $this->get_related_products_quantities_content($product_id, $wpc_metas);
                ?>
            </div>
        </div>
        <?php
    }

    function get_related_products_content_ajx() {
        $product_id = $_POST['product_id'];
        $wpc_metas = get_post_meta($product_id, 'wpc-metas', true);
        $this->get_related_products_quantities_content($product_id, $wpc_metas);
        die();
    }

    private function get_related_products_quantities_content($product_id, $wpc_metas) {
        $product = wc_get_product($product_id);
        $wpd_product = new WPD_Product($product_id);
        if ($product->get_type() == 'variable') {
            $usable_attributes = $wpd_product->extract_usable_attributes();

            $keys = array(
                'related-products' => __('Which attributes should be used for the related products box?', 'wpd'),
                'related-quantities' => __('Which attributes should be used for the related quantities box?', 'wpd'),
            );

            foreach ($keys as $key => $desc) {
                echo $desc . '<br>';

                $related_products = array();
                if (isset($wpc_metas[$key])) {
                    $related_products = $wpc_metas[$key];
                }

                foreach ($usable_attributes as $attribute_name => $attribute_data) {
                    $variation_label = $attribute_data['label'];
                    $is_checked = ( in_array($attribute_data['key'], $related_products) ) ? "checked='checked'" : '';
                    ?>
                    <input type="checkbox" name="wpc-metas[<?php echo $key; ?>][]" value="<?php echo $attribute_data['key']; ?>" <?php echo $is_checked; ?>>
                    <?php echo $variation_label; ?>
                    <br>
                    <?php
                }

                echo '<br>';
            }
        } else {
            echo 'This feature is only available for variable products.';
        }
    }

    public function extract_usable_attributes() {
        $product = $this->product;
        $attributes = $product->get_attributes();
        $usable_attributes = array();
        foreach ($attributes as $attribute) {
            $sanitized_name = sanitize_title($attribute['name']);
            if ($attribute['is_visible'] && $attribute['is_variation']) {
                if ($attribute['is_taxonomy']) {
                    $values = wc_get_product_terms($product->get_id(), $attribute['name'], array('fields' => 'all'));
                    $taxonomy = get_taxonomy($attribute['name']);
                    $key = 'attribute_' . $sanitized_name;
                    $usable_attributes[$attribute['name']] = array(
                        'key' => $key,
                        'label' => $taxonomy->labels->singular_name,
                        'values' => $values,
                    );
                } else {
                    $key = 'attribute_' . $sanitized_name;
                    // Convert pipes to commas and display values
                    $values = array_map('trim', explode(WC_DELIMITER, $attribute['value']));
                    $usable_attributes[$attribute['name']] = array(
                        'key' => $key,
                        'label' => $attribute['name'],
                        'values' => $values,
                    );
                }
            }
        }

        return $usable_attributes;
    }

    /**
     * Checks the product contains at least one active part
     *
     * @return boolean
     */
    public function has_part() {
        $parts = get_proper_value($this->settings, 'parts');
        return !empty($parts);
    }

    /**
     * Returns the customization page URL
     *
     * @global Array $wpd_settings
     * @param int   $design_index Saved design index to load
     * @param mixed $cart_item_key Cart item key to edit
     * @param int   $order_item_id Order item ID to load
     * @param int   $tpl_id ID of the template to load
     * @return String
     */
    public function get_design_url($design_index = false, $cart_item_key = false, $order_item_id = false, $tpl_id = false) {
        global $wpd_settings;

        if ($this->variation_id) {
            $item_id = $this->variation_id;
        } else {
            $item_id = $this->root_product_id;
        }

        $options = $wpd_settings['wpc-general-options'];
        $wpc_page_id = $options['wpc_page_id'];
        if (function_exists('icl_object_id')) {
            $wpc_page_id = icl_object_id($wpc_page_id, 'page', false, ICL_LANGUAGE_CODE);
        }
        $wpc_page_url = '';
        if ($wpc_page_id) {
            $wpc_page_url = get_permalink($wpc_page_id);
            if ($item_id) {
                $query = parse_url($wpc_page_url, PHP_URL_QUERY);
                // Returns a string if the URL has parameters or NULL if not
                if (get_option('permalink_structure')) {
                    if (substr($wpc_page_url, -1) != '/') {
                        $wpc_page_url .= '/';
                    }
                    if ($design_index || $design_index === 0) {
                        $wpc_page_url .= "saved-design/$item_id/$design_index/";
                    } elseif ($cart_item_key) {
                        $qty_key = "qty_" . $cart_item_key . "_" . $item_id;
                        $wpc_page_url .= "edit/$item_id/$cart_item_key/" . '?custom_qty=' . get_option($qty_key, $this->get_purchase_properties()['min_to_purchase']);
                    } elseif ($order_item_id) {
                        $wpc_page_url .= "ordered-design/$item_id/$order_item_id/";
                        $wpc_page_url = apply_filters('wpd_customized_order_page_url', $wpc_page_url);
                    } else {
                        $wpc_page_url .= 'design/' . $item_id . '/';
                        if ($tpl_id) {
                            $wpc_page_url .= "$tpl_id/";
                        }
                    }
                } else {
                    if ($design_index !== false) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&design_index=' . $design_index;
                    } elseif ($cart_item_key) {
                        $qty_key = "qty_" . $cart_item_key . "_" . $item_id;
                        $wpc_page_url .= '&product_id=' . $item_id . '&edit=' . $cart_item_key . '&custom_qty=' . get_option($qty_key, $this->get_purchase_properties()['min_to_purchase']);
                    } elseif ($order_item_id) {
                        $wpc_page_url .= '&product_id=' . $item_id . '&oid=' . $order_item_id;
                    } else {
                        $wpc_page_url .= '&product_id=' . $item_id;
                        if ($tpl_id) {
                            $wpc_page_url .= "&tpl=$tpl_id";
                        }
                    }
                }
            }
        }

        return $wpc_page_url;
    }

    /**
     * Returns a variation root product ID
     *
     * @param type $variation_id Variation ID
     * @return int
     */
    public function get_parent($variation_id) {
        $variable_product = wc_get_product($variation_id);
        if (!$variable_product) {
            return false;
        }
        if ($variable_product->get_type() != 'variation') {
            $product_id = $variation_id;
        } else {
            $product_id = $variable_product->get_parent_id();
        }

        return $product_id;
    }

    /**
     * Returns the defined value for a product setting which can be local(product metas) or global (options)
     *
     * @param array  $product_settings Product options
     * @param array  $global_settings Global options
     * @param string $option_name Option name / Meta key
     * @param int    $field_value Default value to return if empty
     * @return string
     */
    public function get_option($product_settings, $global_settings, $option_name, $field_value = '') {
        if (isset($product_settings[$option_name]) && ( (!empty($product_settings[$option_name]) ) || $product_settings[$option_name] === '0' )) {
            $field_value = $product_settings[$option_name];
        } elseif (isset($global_settings[$option_name]) && !empty($global_settings[$option_name])) {
            $field_value = $global_settings[$option_name];
        }

        return $field_value;
    }

    function set_custom_upl_cart_item_data($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        global $woocommerce;
        if ($variation_id) {
            $element_id = $variation_id;
        } else {
            $element_id = $product_id;
        }
        if (isset($_SESSION['wpc-user-uploaded-designs'][$element_id])) {
            if (!isset($woocommerce->cart->cart_contents[$cart_item_key]['wpc-uploaded-designs'])) {
                $woocommerce->cart->cart_contents[$cart_item_key]['wpc-uploaded-designs'] = array();
            }

            array_push($woocommerce->cart->cart_contents[$cart_item_key]['wpc-uploaded-designs'], $_SESSION['wpc-user-uploaded-designs'][$element_id]);
            $woocommerce->cart->calculate_totals();
            unset($_SESSION['wpc-user-uploaded-designs'][$element_id]);
        }
        if (!isset($woocommerce->cart->cart_contents[$cart_item_key]['wpc_design_pricing_options'])) {
            $woocommerce->cart->cart_contents[$cart_item_key]['wpc_design_pricing_options'] = array();
        }

        if (isset($_POST['wpd-design-opt'])) {
            $woocommerce->cart->cart_contents[$cart_item_key]['wpc_design_pricing_options'] = $_POST['wpd-design-opt'];
            $woocommerce->cart->calculate_totals();
        }
    }

    /**
     * Returns the minimum and maximum order quantities
     *
     * @return type
     */
    function get_purchase_properties() {
        if ($this->variation_id) {
            $defined_min_qty = get_post_meta($this->variation_id, 'variation_minimum_allowed_quantity', true);
            // We consider the values defined for the all of them
            if (!$defined_min_qty) {
                $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);
            }
            $product_metas = get_post_meta($this->root_product_id, 'wpc-metas', true);

            if (!$defined_min_qty && isset($product_metas['related-products']) && !empty($product_metas['related-products'])) {
                $defined_min_qty = 0;
            } elseif (!isset($product_metas['related-products']) || empty($product_metas['related-products'])) {
                $defined_min_qty = 1;
            }

            $defined_max_qty = get_post_meta($this->variation_id, 'variation_maximum_allowed_quantity', true);
            // We consider the values defined for the all of them
            if (!$defined_max_qty) {
                $defined_max_qty = get_post_meta($this->root_product_id, 'maximum_allowed_quantity', true);
            }
        } else {
            $defined_min_qty = get_post_meta($this->root_product_id, 'minimum_allowed_quantity', true);
            if (!$defined_min_qty) {
                $defined_min_qty = 1;
            }

            $defined_max_qty = get_post_meta($this->root_product_id, 'variation_maximum_allowed_quantity', true);
        }

        $step = apply_filters('woocommerce_quantity_input_step', '1', $this->product);
        $min_qty = apply_filters('woocommerce_quantity_input_min', $defined_min_qty, $this->product);

        if (!$defined_max_qty) {
            $defined_max_qty = apply_filters('woocommerce_quantity_input_max', $this->product->backorders_allowed() ? '' : $this->product->get_stock_quantity(), $this->product);
        }

        $min_to_purchase = $min_qty;
        if (!$min_qty) {
            $min_to_purchase = 1;
        }

        $defaults = array(
            'max_value' => $defined_max_qty,
            'min_value' => $min_qty,
            'step' => $step,
        );
        $args = apply_filters('woocommerce_quantity_input_args', wp_parse_args(array(), $defaults), $this->product);

        return array(
            'min' => $args['min_value'],
            'min_to_purchase' => $args['min_value'],
            'max' => $args['max_value'],
            'step' => $args['step'],
        );
    }

    function get_related_product_desc() {
        $purchase_properties = $this->get_purchase_properties();
        if ($purchase_properties['min'] > 1) {
            return __('Requires a minimum purchase of ', 'wpd') . $purchase_properties['min'] . __(' item(s).', 'wpd');
        } else {
            return '';
        }
    }

    public function save_product_settings_fields($item_id) {
        $meta_key = 'wpc-metas';
        $variation = wc_get_product($item_id);
        // If we're dealing with a variation, Product ID is the root ID of the product
        if (get_class($variation) == 'WC_Product_Variation') {
            $product_id = $variation->get_parent_id();
        } else {
            $product_id = $item_id;
        }
        if (isset($_POST[$meta_key])) {
            // Careful this hooks only send the updated data, not the complete form
            $old_metas = get_post_meta($product_id, $meta_key, true);
            if (empty($old_metas)) {
                $old_metas = array();
            }
            $new_metas = array_replace($old_metas, $_POST[$meta_key]);

            // If the related products and quantities are not in the post variable, that means the user is disabling them
            if (!isset($_POST[$meta_key]['related-products'])) {
                $new_metas['related-products'] = array();
            }

            if (!isset($_POST[$meta_key]['related-quantities'])) {
                $new_metas['related-quantities'] = array();
            }
        } else {
            $new_metas = array();
            $new_metas['related-products'] = array();
            $new_metas['related-quantities'] = array();
        }
        update_post_meta($product_id, $meta_key, $new_metas);
    }

    public function hide_cart_button() {
        global $product;
        global $wpd_settings;
        $general_options = $wpd_settings['wpc-general-options'];
        $hide_cart_button = get_proper_value($general_options, 'wpd-hide-cart-button', true);
        $custom_products = wpd_get_custom_products();
        $anonymous_function = function ( $o ) {
            return $o->id;
        };
        $custom_products_ids = array_map($anonymous_function, $custom_products);
        $pid = $product->get_id();
        if (in_array($pid, $custom_products_ids) && $hide_cart_button) {
            
            if($hide_cart_button){
                remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
            }
            ?>
            <script type="text/javascript">
                var hide_cart_button = <?php echo $hide_cart_button; ?>;
                jQuery('[value="<?php echo $pid; ?>"]').parent().find('.add_to_cart_button').hide();
                jQuery('[value="<?php echo $pid; ?>"]').parent().find('.single_add_to_cart_button').hide();
            </script>
            <?php
        }
    }

    public function get_custom_products_body_class($classes, $class) {
        if (is_singular(array('product'))) {
            global $wpd_settings;
            $general_options = $wpd_settings['wpc-general-options'];
            $hide_cart_button = get_proper_value($general_options, 'wpd-hide-cart-button', true);

            $custom_products = wpd_get_custom_products();
            $anonymous_function = function ( $o ) {
                return $o->id;
            };
            $custom_products_ids = array_map($anonymous_function, $custom_products);
            $pid = get_the_ID();
            $product = new WPD_Product($pid);
            if (in_array($pid, $custom_products_ids)) {
                array_push($classes, 'wpd-is-customizable');
                if ($hide_cart_button) {
                    array_push($classes, 'wpd-hide-cart-button');
                }
            }
        }
        return $classes;
    }

    /**
     * Returns the templates page URL
     *
     * @return string
     */
    public function get_templates_page_url() {

        if ($this->variation_id) {
            $item_id = $this->variation_id;
        } else {
            $item_id = $this->root_product_id;
        }

        $wpd_templates_id = $this->get_templates_page_id();
        if (empty($wpd_templates_id)) {
            return false;
        }
        $wpd_templates_url = add_query_arg('for-product', $this->variation_id, get_permalink($wpd_templates_id));
        return $wpd_templates_url;
    }

    /**
     * Returns the templates page ID
     *
     * @global Array $wpd_settings
     * @return int
     */
    function get_templates_page_id() {
        global $wpd_settings;
        $general_options = $wpd_settings['wpc-general-options'];
        $global_templates_page_id = get_proper_value($general_options, 'wpd-templates-page');
        $product_templates_page_id = get_proper_value($this->settings, 'templates-page', $global_templates_page_id);
        if (empty($product_templates_page_id)) {
            $product_templates_page_id = $global_templates_page_id;
        }
        if (!empty($product_templates_page_id) && function_exists('icl_object_id')) {
            $product_templates_page_id = icl_object_id($product_templates_page_id, 'page', false, ICL_LANGUAGE_CODE);
        }

        return $product_templates_page_id;
    }

    public function get_templates() {
        global $wpdb;
        if ($this->variation_id) {
            $item_id = $this->variation_id;
        } else {
            $item_id = $this->root_product_id;
        }
        $search = '"' . $item_id . '"';
        $templates = $wpdb->get_results(
                "
                           SELECT p.id
                           FROM $wpdb->posts p
                           JOIN $wpdb->postmeta pm on pm.post_id = p.id 
                           WHERE p.post_type = 'wpc-template'
                            AND    p.post_status='publish'
                           AND (
                                    (pm.meta_key = 'base-product') AND (pm.meta_value ='$item_id')
                                    OR
                                    (pm.meta_key = 'base-product-alt') AND (pm.meta_value like '%$search%')
                                )
                           "
        );
        return $templates;
    }

    function get_buttons($with_upload = false) {
        ob_start();
        $content = '';
        $product = $this->product;
        $wpc_metas = $this->settings;
        $product_page = get_permalink($product->get_id());

        if ($this->variation_id) {
            $item_id = $this->variation_id;
        } else {
            $item_id = $this->root_product_id;
        }

        if ($product->get_type() != 'variation') {
            wpd_generate_design_buttons_css();
        }

        if ($product->get_type() == 'variable') {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                if (!$variation['is_purchasable'] || !$variation['is_in_stock']) {
                    continue;
                }
                $wpd_product = new WPD_Product($variation['variation_id']);
                echo $wpd_product->get_buttons($with_upload);
            }
        } else {
            $has_parts = $this->has_part();
            if (!$has_parts) {
                $output = ob_get_clean();
                return $output;
            }
            ?>
            <div class="wpd-buttons-wrap-<?php echo $product->get_type(); ?>" data-id="<?php echo $this->variation_id; ?>">
                <?php
                // Design from blank
                // if (isset($wpc_metas['can-design-from-blank']) && !empty($wpc_metas['can-design-from-blank'])) {
                    $design_from_blank_url = $this->get_design_url();
                    $content .= '<a  href="' . $design_from_blank_url . '" class="mg-top-10 wpc-customize-product">' . apply_filters("wpd_design_from_blank_btn_filter", __('Design from blank', 'wpd')) . '</a>';
                // }

                // Templates
                $templates = $this->get_templates();
                $templates_page = $this->get_templates_page_url();
                if (count($templates) && $templates_page) {
                    if (count($templates) == 1) {
                        $wpd_product = new WPD_Product($item_id);
                        $template_id = $templates[0]->id;
                        $customize_url = $wpd_product->get_design_url(false, false, false, $template_id);
                        $templates_btn = '<a href="' . $customize_url . '" data-id="' . $product->get_id() . '" data-type="' . $product->get_type() . '" class="btn-choose tpl">' . apply_filters("wpd_customize_btn_filter", __('Customize', 'wpd')) . '</a>';
                    } else {
                        $templates_btn = '<a href="' . $templates_page . '" data-id="' . $product->get_id() . '" data-type="' . $product->get_type() . '" class="btn-choose tpl">' . apply_filters("wpd_browse_our_template_btn_filter", __('Browse our templates', 'wpd')) . '</a>';
                    }
                    $content .= apply_filters('wpd_browse_our_template_button', $templates_btn, $this);
                }

                // Upload my own design
                if (isset($wpc_metas['can-upload-custom-design']) && !empty($wpc_metas['can-upload-custom-design'])) {
                    // To avoid having an upload form on the shop/categories pages
                    if ($with_upload) {
                        $modal_id = uniqid('wpc-modal');
                        $content .= '<a data-id="' . $item_id . '" data-type="' . $product->get_type() . '" data-toggle="o-modal" data-target="#' . $modal_id . '" class="mg-top-10 wpc-upload-product-design">' . apply_filters("wpd_upload_my_own_design_btn_filter", __('Upload my own design', 'wpd')) . '</a>';
                        $modals = '<div class="omodal fade wpc-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="omodal-dialog">
                                          <div class="omodal-content">
                                            <div class="omodal-header">
                                              <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                              <h4 class="omodal-title">' . __('Pick a file', 'wpd') . '</h4>
                                            </div>
                                            <div class="omodal-body txt-center">
                                                ' . WPD_Design::get_custom_design_upload_form($product->get_id()) . '
                                            </div>
                                          </div>
                                        </div>
                                      </div>';
                        array_push(wpd_retarded_actions::$code, $modals);
                        add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                    } else {
                        $content .= '<a href="' . $product_page . '" class="btn-choose custom mg-top-10 wpc-upload-product-design"> ' . apply_filters("wpd_upload_my_own_design_btn_filter", __('Upload my own design', 'wpd')) . '</a>';
                    }
                }
                if (!isset($item_id)) {
                    $item_id = '';
                }
                if (!isset($design_from_blank_url)) {
                    $design_from_blank_url = '';
                }
                if (!isset($customize_url)) {
                    $customize_url = '';
                }
                if (!isset($modal_id)) {
                    $modal_id = '';
                }
                echo apply_filters('wpd_show_customization_buttons_in_modal', $content, $item_id, $design_from_blank_url, $customize_url, $product->get_type(), $modal_id);
                ?>
            </div>
            <?php
        }
        $output = ob_get_clean();
        return $output;
    }

    function get_variable_product_details_location_notice() {
        ?>
        <div class="options_group show_if_simple show_if_variable">
            <p class="form-field _sold_individually_field show_if_simple show_if_variable" style="background: #00a0d2;color: white;display: block;padding:  0 !important;padding-left:  10px !important;">
                <?php _e('In order to assign a configuration to a variation, you will need to go into the variation properties (same area you define a variation price).', 'wpd'); ?>
            </p>
        </div>
        <?php
    }

    function duplicate_product_metas($new_product, $old_product) {
        $meta_key = 'wpc-metas';
        $old_metas = get_post_meta($old_product->get_id(), $meta_key, true);

        $new_metas = wpd_replace_key_in_array($old_metas, $old_product->get_id(), $new_product->get_id());
        update_post_meta($new_product->get_id(), $meta_key, $new_metas);
    }

    public function save_config($post_id) {
        $meta_key = 'wpd-metas';
        if (isset($_POST[$meta_key])) {
            update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
        }
    }

   public function get_output_image_width() {
	$output_settings = get_proper_value( $this->settings, 'output-settings', array() );
	$output_w	 = get_proper_value( $output_settings, 'output-w', 800 );
	if ( ! isset( $output_settings[ 'output-unit' ] ) ) {
	    $output_settings[ 'output-unit' ] = 'px';
	}
	if ( $output_settings[ 'output-unit' ] != 'px' ) {
	    $resolution	 = get_proper_value( $output_settings, 'resolution', 300 );
//            if ($output_settings['pdf-format'] != 'custom') {
//                // we retrieve the dimensions in points
//                $dimensions = TCPDF_STATIC::getPageSizeFromFormat($output_settings['pdf-format']);
//                $output_w = 0.352778 * $dimensions[0] * $resolution / 25.4;
//            } else {
	    $dimensions	 = array( $output_settings[ 'output-w' ], $output_settings[ 'output-h' ] );

	    if ( $output_settings[ 'output-unit' ] == 'pt' ) {
		$output_w = 0.352778 * floatval( $dimensions[ 0 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'mm' ) {
		$output_w = floatval( $dimensions[ 0 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'cm' ) {
		$output_w = 10 * floatval( $dimensions[ 0 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'in' ) {
		$output_w = 25.4 * floatval( $dimensions[ 0 ] ) * floatval( $resolution ) / 25.4;
	    }
//            }
	}
	return $output_w;
    }
    public function get_output_image_height() {
	$output_settings = get_proper_value( $this->settings, 'output-settings', array() );
	$output_h	 = get_proper_value( $output_settings, 'output-h', 500 );
	if ( ! isset( $output_settings[ 'output-unit' ] ) ) {
	    $output_settings[ 'output-unit' ] = 'px';
	}
	if ( $output_settings[ 'output-unit' ] != 'px' ) {
	    $resolution	 = get_proper_value( $output_settings, 'resolution', 300 );
//            if ($output_settings['pdf-format'] != 'custom') {
//                // we retrieve the dimensions in points
//                $dimensions = TCPDF_STATIC::getPageSizeFromFormat($output_settings['pdf-format']);
//                $output_w = 0.352778 * $dimensions[0] * $resolution / 25.4;
//            } else {
	    $dimensions	 = array( $output_settings[ 'output-w' ], $output_settings[ 'output-h' ] );

	    if ( $output_settings[ 'output-unit' ] == 'pt' ) {
		$output_h = 0.352778 * floatval( $dimensions[ 1 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'mm' ) {
		$output_h = floatval( $dimensions[ 1 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'cm' ) {
		$output_h = 10 * floatval( $dimensions[ 1 ] ) * floatval( $resolution ) / 25.4;
	    } elseif ( $output_settings[ 'output-unit' ] == 'in' ) {
		$output_h = 25.4 * floatval( $dimensions[ 1 ] ) * floatval( $resolution ) / 25.4;
	    }
//            }
	}
	return $output_h;
    }


}
