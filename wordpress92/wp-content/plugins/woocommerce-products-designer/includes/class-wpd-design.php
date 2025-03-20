<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Contains all methods and hooks callbacks related to the user design
 *
 * @author HL
 */
class WPD_Design {

    function delete_saved_design_ajax() {
        $design_index = $_GET['design_index'];
        global $current_user;
        $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
        unset($user_designs[$design_index]);
        foreach ($user_designs as $index => $design) {
            foreach ($design[2] as $key => $value) {
                $user_designs[$index][2][$key]['json'] = wp_slash($value['json']);
            }
        }

        delete_user_meta($current_user->ID, 'wpc_saved_designs');

        $result = true;
        foreach ($user_designs as $index => $design) {
            $result = add_user_meta($current_user->ID, 'wpc_saved_designs', $design);

            if (!$result) {
                break;
            }
        }


        echo json_encode(
                array(
                    'success' => $result,
                    'success_message' => __('Design successfully deleted.', 'wpd'),
                    'failure_message' => __('An error occured. Please try again later', 'wpd'),
                )
        );
        die();
    }

    private function get_output_zip_folder_name($prod_id) {
        global $wpd_settings;

        $wpd_product = new WPD_Product($prod_id);
        $product_metas = $wpd_product->settings;
        $product_output_settings = get_proper_value($product_metas, 'output-settings');
        $zip_name = get_proper_value($product_output_settings, 'zip-folder-name');
        if (empty($zip_name)) {
            $zip_name = uniqid('wpc_');
        }

        return $zip_name . '.zip';
    }

    function add_custom_design_to_cart_ajax() {
        global $woocommerce;
        $message = '';
        if (wpd_woocommerce_version_check()) {
            $cart_url = wc_get_cart_url();
        } else {
            $cart_url = $woocommerce->cart->get_cart_url();
        }

        $main_variation_id = filter_input(INPUT_POST, 'variation_id');

        $cart_item_key = filter_input(INPUT_POST, 'cart_item_key');
        $wpd_product = new WPD_Product($main_variation_id);
        $wpc_metas = $wpd_product->settings;
        $data = array();
        $total_price_form = 0;
        if (class_exists('Ofb')) {
            if (isset($wpc_metas['form-builder'])) {
                if ($wpc_metas['form-builder'] != '') {
                    $form_fields = stripslashes_deep(filter_input(INPUT_POST, 'form_fields'));
                    if (isset($form_fields) && !empty($form_fields)) {
                        if (isset($form_fields)) {
                            $form_fields_decode = json_decode($form_fields);
                            foreach ($form_fields_decode as $key => $value) {
                                $data[$key] = $value;
                            }
                        }
                    }
                    $total_price_form = get_form_data($wpc_metas['form-builder'], $data);
                }
            }
        }
        $newly_added_cart_item_key = false;

        $tmp_dir = uniqid();
        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir['basedir'] . "/WPC/$tmp_dir";
        $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir";
        if (wp_mkdir_p($generation_path)) {
            $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir";
            $zip_name = $this->get_output_zip_folder_name($main_variation_id);

            $final_canvas_parts = $_POST['final_canvas_parts'];
            $result = $this->export_data_to_files($generation_path, $final_canvas_parts, $main_variation_id, $zip_name);
            if (!empty($result) && is_array($result)) {
                $final_canvas_parts['output']['files'] = $result;
                $final_canvas_parts['output']['working_dir'] = $tmp_dir;
                $final_canvas_parts['output']['zip'] = $zip_name;
                $final_canvas_parts['output']['tpl'] = filter_input(INPUT_POST, 'tpl');

                if (class_exists('Ofb')) {

                    if (isset($wpc_metas['form-builder'])) {

                        if ($wpc_metas['form-builder'] != '') {
                            if (isset($data) && !empty($data)) {
                                $final_canvas_parts['output']['form_fields'] = $data;
                            }

                            $final_canvas_parts['output']['total_price_form'] = $total_price_form;
                        }
                    }
                }
                $newly_added_cart_item_key = true;
                if ($cart_item_key) {
                    $newly_added_cart_item_key = $this->add_designs_to_cart($final_canvas_parts);
                    WC()->cart->remove_cart_item($cart_item_key );
                    // $woocommerce->cart->cart_contents[$cart_item_key]['wpc_generated_data'] = $final_canvas_parts;
                    // $woocommerce->cart->calculate_totals();
                    $message = "<div class='wpc_notification success f-right'>" . __('Item successfully updated.', 'wpd') . " <a href='$cart_url'>" . __('View Cart', 'wpd') . '</a></div>';
                } else {
                    $newly_added_cart_item_key = $this->add_designs_to_cart($final_canvas_parts);
                    if ($newly_added_cart_item_key) {
                        $message = "<div class='wpc_notification success f-right'>" . __('Product successfully added to basket.', 'wpd') . " <a href='$cart_url'>View Cart</a></div>";
                    } else {
                        $message = "<div class='wpc_notification failure f-right'>" . __('A problem occured while adding the product to the cart. Please try again.', 'wpd') . '</div>';
                    }
                }
            } else {
                $message = "<div class='wpc_notification failure f-right'>" . __('A problem occured while generating the output files... Please try again.', 'wpd') . '</div>';
            }
        } else {
            $message = "<div class='wpc_notification failure f-right'>" . __("The creation of the directory $generation_path failed. Make sure that the complete path is writeable and try again.", 'wpd') . '</div>';
        }

        echo json_encode(
                array(
                    'success' => $newly_added_cart_item_key,
                    'message' => $message,
                    'url' => $cart_url,
                    'form_fields' => $data,
                )
        );
        die();
    }

    private function add_designs_to_cart($final_canvas_parts) {
        global $woocommerce;
        $newly_added_cart_item_key = false;
        $variations_str = stripslashes_deep(filter_input(INPUT_POST, 'variations'));
        $variations = json_decode($variations_str, true);

        foreach ($variations as $variation_name => $variation_info) {

            $variation_id = $variation_info["id"];
            $quantity = $variation_info["qty"];
            if ($quantity <= 0) {
                continue;
            }

            $product = wc_get_product($variation_id);
            $variation = array();
            if ($product->get_type() == 'simple') {
                $product_id = $variation_id;
                $is_variable=false;
            } else {
                $variation = $product->get_variation_attributes();
                $product_id = $product->get_parent_id();
                $is_variable=true;
            }

            $variations = array();

            if (isset($_SESSION['wpd_key'])) {
                $variations = get_transient($_SESSION['wpd_key']);
            }

            foreach ($variation as $key => $value) {
                if (isset($variations[$key]) && '' === $value) {
                    $variation[$key] = $variations[$key];
                }
            }

            if (isset($_SESSION['combinaison'][$variation_name])) {
                $variation = $_SESSION['combinaison'][$variation_name];
            }
            
            if(!$is_variable)
            {
                $variations_id=""; 
                $variation="";
            }
            else $variations_id=$variation_id; 

            $newly_added_cart_item_key = $woocommerce->cart->add_to_cart($product_id, $quantity, $variations_id, $variation, array('wpc_generated_data' => $final_canvas_parts));
            if (method_exists($woocommerce->cart, 'maybe_set_cart_cookies')) {
                $woocommerce->cart->maybe_set_cart_cookies();
            }
            $qty_key = "qty_" . $newly_added_cart_item_key . "_" . $variation_id;
            update_option($qty_key, $quantity);
        }

        return $newly_added_cart_item_key;
    }

    private function merge_pictures($base_layer, $top_layer, $final_img, $variation_id) {
        $layer_infos = pathinfo($base_layer);

        if ($layer_infos['extension'] == 'jpg') {
            $base = imagecreatefromjpeg($base_layer);
        } else {
            $base = imagecreatefrompng($base_layer);
        }

        imagealphablending($base, true);
        list($base_w, $base_h, $type, $attr) = getimagesize($base_layer);

        $top_layer_infos = pathinfo($top_layer);
        if ($top_layer_infos['extension'] == 'jpg') {
            $top = imagecreatefromjpeg($top_layer);
        } else {
            $top = imagecreatefrompng($top_layer);
        }

        imagealphablending($top, true);
        list($top_w, $top_h, $type, $attr) = getimagesize($top_layer);

        $src_x = ( $base_w - $top_w ) / 2;
        $src_x = apply_filters('wpd_watermark_position_x', $src_x, $variation_id, $base_w, $base_h, $top_w, $top_h);
        $src_y = ( $base_h - $top_h ) / 2;
        $src_y = apply_filters('wpd_watermark_position_y', $src_y, $variation_id, $base_w, $base_h, $top_w, $top_h);

        imagecopyresampled($base, $top, $src_x, $src_y, 0, 0, $top_w, $top_h, $top_w, $top_h);
        imagedestroy($top);

        imagecolortransparent($base, imagecolorallocatealpha($base, 0, 0, 0, 127));

        imagealphablending($base, false);
        imagesavealpha($base, true);

        imagepng($base, $final_img);
    }

    function get_watermarked_preview() {
        $watermark = o_get_proper_image_url($_POST['watermark']);
        $wpc_img_format = $_POST['format'];
        $allowed_extensions = array('png', 'jpg');
        if (!in_array($wpc_img_format, $allowed_extensions)) {
            return false;
        }
        $upload_dir = wp_upload_dir();
        $preview_filename = uniqid('preview_') . ".$wpc_img_format";
        $output_file_path = $upload_dir['basedir'] . '/WPC/' . $preview_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $output_file_path)) {
            $url = $upload_dir['baseurl'] . '/WPC/' . $preview_filename;
            $this->merge_pictures($output_file_path, $watermark, $output_file_path, $_POST['product-id']);
            echo json_encode(array('url' => $url));
        } else {
            echo "<div class='wpc_notification failure'>" . __('An error has occured. Please try again later or contact the administrator.', 'wpd');
        }
        die();
    }

    function save_custom_design_for_later_ajax() {
        $final_canvas_parts = $_POST['final_canvas_parts'];
        $variation_id = $_POST['variation_id'];
        $design_index = $_POST['design_index'];
        $design_custom_name = $_POST['design_custom_name'];
        $cart_item_key = '';
        if (isset($_POST['cart_item_key'])) {
            $cart_item_key = $_POST['cart_item_key'];
        }
        $is_logged = 0;
        $result = 0;
        $message = '';
        $wpd_product = new WPD_Product($variation_id);
        $customization_url = $wpd_product->get_design_url();
        $url = wp_login_url($customization_url);
        $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
        if ($myaccount_page_id) {
            $url = get_permalink($myaccount_page_id);
        }
        if (is_user_logged_in()) {
            global $current_user;
            $message = $current_user->ID;
            $is_logged = 1;
            if (isset($design_custom_name) && !empty($design_custom_name)) {
                $today = $design_custom_name;
            } else {
                $today = date('Y-m-d H:i:s');
            }
            $tmp_dir = uniqid();
            $upload_dir = wp_upload_dir();
            $generation_path = $upload_dir['basedir'] . "/WPC/$tmp_dir";
            $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir";
            if (wp_mkdir_p($generation_path)) {
                $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir";
                $zip_name = $this->get_output_zip_folder_name($variation_id);
                $export_result = $this->export_data_to_files($generation_path, $final_canvas_parts, $variation_id, $zip_name);
                if (!empty($export_result) && is_array($export_result)) {
                    $final_canvas_parts['output']['files'] = $export_result;
                    $final_canvas_parts['output']['working_dir'] = $tmp_dir;
                    $final_canvas_parts['output']['zip'] = $zip_name;
                    $to_save = array($variation_id, $today, $final_canvas_parts);
                    $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');

                    foreach ($user_designs as $index => $design) {
                        foreach ($design[2] as $key => $value) {
                            if (isset($value['json'])) {
                                $user_designs[$index][2][$key]['json'] = wp_slash($value['json']);
                            }
                        }
                    }

                    if ($design_index !== -1) {
                        $user_designs[$design_index] = $to_save;
                    } else {
                        array_push($user_designs, $to_save);
                    }

                    delete_user_meta($current_user->ID, 'wpc_saved_designs');
                    foreach ($user_designs as $index => $design) {
                        $result = add_user_meta($current_user->ID, 'wpc_saved_designs', $design);
                        if (!$result) {
                            break;
                        }
                    }
                    if ($result) {
                        $result = 1;
                        $message = "<div class='wpc_notification success'>" . __('The design has successfully been saved to your account.', 'wpd') . '</div>';
                        if ($design_index == -1) {
                            $design_index = count($user_designs) - 1;
                        }
                        $wpd_product = new WPD_Product($variation_id);
                        $url = $wpd_product->get_design_url($design_index);
                    } else {
                        $result = 0;
                        $message = "<div class='wpc_notification failure'>" . __('An error has occured. Please try again later or contact the administrator.', 'wpd') . '</div>';
                    }
                }
            }
        } else {
            if (!isset($_SESSION['wpc_designs_to_save'])) {
                $_SESSION['wpc_designs_to_save'] = array();
            }
            if (!isset($_SESSION['wpc_designs_to_save'][$variation_id])) {
                $_SESSION['wpc_designs_to_save'][$variation_id] = array();
            }

            array_push($_SESSION['wpc_designs_to_save'][$variation_id], $final_canvas_parts);
            $to_save = array();
            foreach ($final_canvas_parts as $part_key => $part_data) {
                if (!isset($part_data['json'])) {
                    continue;
                }
                $to_save[$part_key] = $part_data['json'];
            }
            $_SESSION['wpd-data-to-load'] = json_encode($to_save);
        }
        echo json_encode(
                array(
                    'is_logged' => $is_logged,
                    'success' => $result,
                    'message' => $message,
                    'url' => $url,
                )
        );
        die();
    }

    function save_canvas_to_session_ajax() {
        $final_canvas_parts = $_POST['final_canvas_parts'];
        $template_object = get_post_type_object('wpc-template');
        $can_manage_templates = current_user_can($template_object->cap->edit_posts);
        if ($can_manage_templates) {
            $_SESSION['to_save'] = $final_canvas_parts;
        }
        die();
    }

    function add_order_design_to_mail($attachments, $status, $order) {

        global $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        $download_btn = $options['wpc-send-design-mail'];
        if ($download_btn !== '0') {
            $allowed_statuses = array('new_order', 'customer_invoice', 'customer_processing_order', 'customer_completed_order');
            if (isset($status) && in_array($status, $allowed_statuses)) {
                $items = $order->get_items();
                foreach ($items as $order_item_id => $item) {
                    $upload_dir = wp_upload_dir();
                    if (isset($item['wpc_data'])) {
                        $unserialized_data = $item['wpc_data'];
                        $tmp_dir = $unserialized_data['output']['working_dir'];
                        array_push($attachments, $upload_dir['basedir'] . "/WPC/$tmp_dir/" . $unserialized_data['output']['zip']);
                    } elseif (isset($item['wpc_data_upl'])) {
                        // Looks like the structure changed for latest versions of WC (tested on 2.3.7)
                        $design_url = $item['item_meta']['wpc_data_upl'][0];
                        if (is_serialized($design_url)) {
                            $unserialized_urls = $design_url;
                            foreach ($unserialized_urls as $design_url) {
                                $design_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $design_url);
                                array_push($attachments, $design_path);
                            }
                        } else {
                            $design_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $design_url);
                            array_push($attachments, $design_path);
                        }
                    }
                }
            }
            $attachments = str_replace('"', '', $attachments);
        }
        return $attachments;
    }

    function generate_downloadable_file() {
        $final_canvas_parts = $_POST['final_canvas_parts'];
        $tmp_dir = uniqid();
        $upload_dir = wp_upload_dir();
        $generation_path = $upload_dir['basedir'] . "/WPC/$tmp_dir";
        $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir";
        $variation_id = $_POST['variation_id'];
        $wpd_product = new WPD_Product($variation_id);
        $output_settings = $wpd_product->settings['output-settings'];

        if (wp_mkdir_p($generation_path)) {

            $zip_name = $this->get_output_zip_folder_name($variation_id);
            $result = $this->export_data_to_files($generation_path, $final_canvas_parts, $variation_id, $zip_name, true);
            if (!empty($result) && is_array($result)) {
                $output_msg = '';
                if ($output_settings['zip-output'] == 'yes') {
                    $output_msg = '<div>' . __('The generation has been successfully completed. Please click ', 'wpd') . "<a href='$generation_url/" . $zip_name . "' download='" . $zip_name . "'>" . __('here', 'wpd') . '</a> ' . __('to download your design', 'wpd') . '.</div>';
                } else {
                    foreach ($result as $part_key => $part_file_arr) {
                        $part_file = apply_filters('wpd_downloadable_file_url', $part_file_arr['file'], $part_file_arr);
                        $output_msg .= '<div>' . ucfirst($part_key) . __(': please click ', 'wpd') . "<a href='$generation_url/$part_key/$part_file' download='$part_file'>" . __('here', 'wpd') . '</a> ' . __('to download', 'wpd') . '.</div>';
                    }
                }
                echo json_encode(
                        array(
                            'success' => 1,
                            'message' => "<div class='wpc-success'>" . $output_msg . '</div>',
                        )
                );
            } else {
                echo json_encode(
                        array(
                            'success' => 0,
                            'message' => "<div class='wpc-failure'>" . __('An error occured in the generation process. Please try again later.', 'wpd') . '</div>',
                        )
                );
            }
        } else {
            echo json_encode(
                    array(
                        'success' => 0,
                        'message' => "<div class='wpc-failure'>" . __("Can't create a generation directory...", 'wpd') . '</div>',
                    )
            );
        }
        do_action('after_generated_file');
        die();
    }

    function get_user_account_products_meta($item_id, $item, $order) {
        if (!is_account_page()) {
            return;
        }
        global $wpd_settings;
        $options = $wpd_settings['wpc-general-options'];
        $output = '';
        $download_btn = get_proper_value($options, 'wpc-user-account-download-btn', '');
        $invalid_statuses = array('wc-cancelled', 'wc-refunded', 'wc-failed');
        if (!in_array($order->get_status(), $invalid_statuses) && $download_btn !== '0' && isset($item['variation_id']) && (!empty($item['variation_id']) || $item['variation_id'] == '0' )) {
            $product = wc_get_product($item['variation_id']);
            $item_id = uniqid();
            ob_start();
            $this->get_order_custom_admin_data($item_id, $item, $product);
            $admin_data = ob_get_contents();
            ob_end_clean();
            $output .= $admin_data;
        }
        echo $output;
    }

    function save_customized_item_meta($item_id, $order_item, $order_id) {
        global $wpd_settings;
        $output_options = get_proper_value($wpd_settings, 'wpc-output-options', array());
        $use_order_id = get_proper_value($output_options, 'use-order-id-as-zip-name', 'no');
        if ($use_order_id == 'yes') {
            $upload_dir = wp_upload_dir();
            $old_tmp_dir = $order_item->legacy_values['wpc_generated_data']['output']['working_dir'];
            $old_generation_path = $upload_dir['basedir'] . "/WPC/$old_tmp_dir";
            $new_generation_path = $upload_dir['basedir'] . "/WPC/$order_id-$item_id";
            // Suppression ancien zip
            unlink($old_generation_path . '/' . $order_item->legacy_values['wpc_generated_data']['output']['zip']);
            // Renommage du répertoire
            rename($old_generation_path, $new_generation_path);
            // Regénération du zip
            $new_zip_name = "$order_id-$item_id.zip";
            $this->generate_design_archive($new_generation_path, "$new_generation_path/$new_zip_name");
            // MAJ des metas
            $order_item->legacy_values['wpc_generated_data']['output']['working_dir'] = "$order_id-$item_id";
            $order_item->legacy_values['wpc_generated_data']['output']['zip'] = $new_zip_name;
        }
        if (isset($order_item->legacy_values['wpc_generated_data'])) {
            wc_add_order_item_meta($item_id, 'wpc_data', $order_item->legacy_values['wpc_generated_data']);
        }

        if (isset($order_item->legacy_values['wpc-uploaded-designs'])) {
            wc_add_order_item_meta($item_id, 'wpc_data_upl', $order_item->legacy_values['wpc-uploaded-designs']);
        }
        if (isset($order_item->legacy_values['wpc_design_pricing_options']) && !empty($order_item->legacy_values['wpc_design_pricing_options'])) {
            $wpc_design_pricing_options_data = $this->get_design_pricing_options_data($order_item->legacy_values['wpc_design_pricing_options']);
            wc_add_order_item_meta($item_id, '_wpc_design_pricing_options', $wpc_design_pricing_options_data);
        }
    }

    function get_order_custom_admin_data($item_id, $item, $_product) {
        global $wpd_settings;
        $output_options = get_proper_value($wpd_settings, 'wpc-output-options', array());
        //$design_composition_visible = get_proper_value($output_options, 'design-composition', 'no');
        $output = '';
        if (!is_object($item)) {
            return;
        }
        $uploaded_data = wc_get_order_item_meta($item_id, 'wpc_data_upl');
        if ($uploaded_data) {
            $output .= "<div class='wpc_order_item' data-item='$item_id'>";
            foreach ($uploaded_data as $design_url) {
                $output .= "<a class='button' href='" . $design_url . "' download='" . basename($design_url) . "'>" . __('Download custom design', 'wpd') . '</a> ';
            }
            $output .= '</div>';
        } elseif (isset($item['wpc_data'])) {
            $upload_dir = wp_upload_dir();
            $output .= "<div class='wpc_order_item' data-item='$item_id'>";
            $unserialized_data = $item['wpc_data'];
            $design_data = $item['wpc_data']['output']['files'];

            $customization_list = $item['wpc_data'];
            if (class_exists('Ofb')) {
                if (isset($customization_list['output']['form_fields'])) {
                    $form_fields = $customization_list['output']['form_fields'];
                    foreach ($form_fields as $key => $value) {
                        if (!is_array($value)) {
                            $output .= '<p>' . $key . ' : ' . $value . '</p>';
                        } else {
                            $output .= '<p>' . $key . ' : ';
                            foreach ($value as $item => $data) {
                                $output .= $data . ' ';
                            }
                            $output .= ' </p>';
                        }
                    }
                }
            }

            $design_details = '';
            if (isset($unserialized_data['output']['tpl']) && !empty($unserialized_data['output']['tpl'])) {
                $tpl_name = get_the_title($unserialized_data['output']['tpl']);
                $design_details = "<br><b>Used Template</b>: $tpl_name<br>";
            }
            //if ($design_composition_visible == 'yes') {
            //    $design_details .= $this->get_desing_details_from_json($unserialized_data);
            //}
            if (count($item['item_meta']['wpc_data']) > 1) {
                foreach ($design_data as $data_key => $data) {
                    $tmp_dir = $unserialized_data['output']['working_dir'];
                    $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir/$data_key/";
                    if (is_admin()) {
                        $img_src = $generation_url . $data['image'];
                    } else {
                        if (isset($data['preview'])) {
                            $img_src = $generation_url . $data['preview'];
                        } else {
                            $img_src = $generation_url . $data['image'];
                        }
                    }
                    $original_part_img_url = $unserialized_data[$data_key]['original_part_img'];
                    $modal_id = uniqid() . "_$item_id" . "_$data_key";
                    $output .= '<span><a class="o-modal-trigger button" data-toggle="o-modal" data-target="#' . $modal_id . '">' . ucfirst($data_key) . '</a></span>';
                    $output .= '<div class="omodal fade o-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="omodal-dialog">
                                  <div class="omodal-content">
                                    <div class="omodal-header">
                                      <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                      <h4 class="omodal-title" id="myModalLabel' . $modal_id . '">Preview</h4>
                                    </div>
                                    <div class="omodal-body">
                                        <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $img_src . '"></div>
                                    </div>
                                  </div>
                                </div>
                              </div>';
                }
            }
            $zip_file = $unserialized_data['output']['zip'];
            if (!empty($zip_file)) {
                $output .= "<a class='button' href='" . $upload_dir['baseurl'] . "/WPC/$tmp_dir/$zip_file' download='" . basename($zip_file) . "'>" . __('Download design', 'wpd') . '</a> ';
            }
            if (isset($item['wpc_design_pricing_options'])) {
                $output .= $item['wpc_design_pricing_options'];
            }

            $output .= $design_details;
            $output .= '</div>';
        }

        echo $output;
    }

    public static function get_custom_design_upload_form($product_id) {
        global $wpd_settings;
        wpd_register_upload_scripts();
        $options = $wpd_settings['wpc-upload-options'];
        $post_id = get_the_ID();
        $wpd_product = new WPD_Product($product_id);
        $wpc_metas = $wpd_product->settings;
        if (!isset($wpc_metas['can-upload-custom-design'])) {
            return;
        }
        $uploader = $options['wpc-uploader'];
        $form_class = 'custom-uploader';
        if ($uploader == 'native') {
            $form_class = 'native-uploader';
        }
        ob_start();
        $form_id = uniqid();
        ?>
        <div class="wpc-uploaded-design-container">
            <form id="custom-upload-form-<?php echo $form_id; ?>" class="<?php echo $form_class; ?> custom-upload-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpc-custom-upload-nonce'); ?>">
                <input type="hidden" name="wpc-product-id-upl" value="<?php echo $product_id; ?>">
                <input type="hidden" name="action" value="handle-custom-design-upload">
                <?php
                if ($uploader == 'native') {
                    ?>
                    <input type="file" name="user-custom-design" class="user-custom-design"/>
                    <?php
                } else {
                    ?>

                    <div class="drop">
                        <a><?php _e('Pick a file', 'wpd'); ?></a>
                        <label for="user-custom-design-<?php echo $form_id; ?>"></label>
                        <input type="file" name="user-custom-design" class="user-custom-design" id="user-custom-design-<?php echo $form_id; ?>"/>
                        <div class="acd-upload-info"></div>
                    </div>
                    <?php
                }
                ?>
            </form>
            <div class="wpc-uploaded-file">
            </div>
            <?php self::get_option_form($post_id, $wpc_metas); ?>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    function handle_custom_design_upload() {
        if (
                !check_ajax_referer('wpc-custom-upload-nonce', 'nonce', false) && !check_ajax_referer('wpc-picture-upload-nonce', 'nonce', false)) {
            $busted = __('Cheating huh?', 'wpd');
            die($busted);
        }

        $upload_dir = wp_upload_dir();
        $product_id = $_POST['wpc-product-id-upl'];
        $generation_path = $upload_dir['basedir'] . '/WPC';
        if (!is_dir($generation_path)) {
            wp_mkdir_p($generation_path);
        }
        $generation_url = $upload_dir['baseurl'] . '/WPC';
        $file_name = uniqid();
        $valid_formats = array();
        $options = get_option('wpc-upload-options');
        $valid_formats_raw = $options['wpc-custom-designs-extensions'];
        if (!empty($valid_formats_raw)) {
            $valid_formats = array_map('trim', explode(',', $valid_formats_raw));
        }
        $name = $_FILES['user-custom-design']['name'];
        $size = $_FILES['user-custom-design']['size'];

        if (isset($_POST) and $_SERVER['REQUEST_METHOD'] == 'POST') {

            if (strlen($name)) {
                if (!isset($_SESSION['wpc-user-uploaded-designs'])) {
                    $_SESSION['wpc-user-uploaded-designs'] = array();
                }

                if (isset($_SESSION['wpc-user-uploaded-designs'][$product_id])) {
                    unset($_SESSION['wpc-user-uploaded-designs'][$product_id]);
                }

                $path_parts = pathinfo($name);
                $ext = $path_parts['extension'];
                $ext = strtolower($ext);
                if (in_array($ext, $valid_formats) || empty($valid_formats)) {
                    $tmp = $_FILES['user-custom-design']['tmp_name'];
                    $success = 0;
                    $message = '';
                    if (move_uploaded_file($tmp, $generation_path . '/' . $file_name . ".$ext")) {
                        $success = 1;
                        $_SESSION['wpc-user-uploaded-designs'][$product_id] = "$generation_url/$file_name.$ext";

                        $message = $_FILES['user-custom-design']['name'] . __(' successfully uploaded. Click on', 'wpd') . "<a class='button wpd-add-to-cart-after-upload'>" . __('Add to cart', 'wpd') . '</a>' . __('to add this product and your design to the cart.', 'wpd');
                        $message = apply_filters('wpd_successfully_uploaded', $message);
                        $valid_formats_for_thumb = array('psd', 'eps');
                        if (in_array($ext, $valid_formats_for_thumb)) {
                            $output_thumb = uniqid() . '.png';
                            $thumb_generation_success = generate_adobe_thumb($generation_path, $file_name . ".$ext", $output_thumb);
                            if ($thumb_generation_success) {
                                $message .= "<div class='wpc-file-preview'><b>Preview</b><br><img src='$generation_url/$output_thumb'></div>";
                            }
                        }
                    } else {
                        $success = 0;
                        $message = __('An error occured during the upload. Please try again later', 'wpd');
                    }
                } elseif (!in_array($ext, $valid_formats)) {
                    $success = 0;
                    $message = __('Incorrect file extension. Allowed extensions: ', 'wpd') . implode(', ', $valid_formats);
                }
                echo json_encode(
                        array(
                            'success' => $success,
                            'message' => $message,
                        )
                );
            }
        }
        die();
    }

    function get_cart_item_price($cart) {
        if ($_SESSION['wpd_calculated_totals'] == true) {
            return;
        }
        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {

            if ($cart_item['variation_id']) {
                $variation_id = $cart_item['variation_id'];
            } else {
                $variation_id = $cart_item['product_id'];
            }

            if (function_exists('icl_object_id')) {
                // WPML runs the hook twice which doubles the price in cart.
                // We just need to make sure the plugin uses the original price so it won't matter
                $variation = wc_get_product($variation_id);
                $item_price = $variation->get_price();
            } else {
                $item_price = $cart_item['data']->get_price();
            }
            if (isset($cart_item['wpc_generated_data'])) {
                $data = $cart_item['wpc_generated_data'];
                $wpd_product = new WPD_Product($variation_id);
                $wpc_metas = $wpd_product->settings;
                $form_fields_data = array();
                $total_price_form = 0;
                if (class_exists('Ofb')) {
                    if (isset($wpc_metas['form-builder'])) {
                        if ($wpc_metas['form-builder'] != '') {
                            if (isset($data['output']['total_price_form'])) {
                                $total_price_form = $data['output']['total_price_form'];
                            }
                        }
                    }
                }

                $a_price = $this->get_additional_price($variation_id, $data);
                $item_price += $a_price + $total_price_form;
            }
            if (isset($cart_item['wpc_design_pricing_options']) && !empty($cart_item['wpc_design_pricing_options'])) {
                $a_price = $this->get_design_options_prices($cart_item['wpc_design_pricing_options']);
                $item_price = apply_filters('wpc_cart_item_price_with_options', ( $item_price + $a_price), $variation_id, $item_price, $a_price);
            }

            // Ajout d'un filtre pour mettre à jour le prix total de l'element dans le panier.
            $item_price = apply_filters('wpc_cart_item_price', $item_price);

            $cart_item['data']->set_price($item_price);
        }
        $_SESSION['wpd_calculated_totals'] = true;
    }

    function get_design_price_ajax() {
        if (isset($_POST['variations'])) {
            $variations = $_POST['variations'];
        } else {
            $variations = array();
        }

        if (isset($_POST['dimensions'])) {
            $dimensions = $_POST['dimensions'];
        } else {
            $dimensions = array();
        }

        $serialized_parts = (array) json_decode(stripslashes_deep($_POST['serialized_parts']));
        $results = array();
        if (isset($serialized_parts['variation_id'])) {
            $variation_id = $serialized_parts['variation_id'];
            $wpd_product = new WPD_Product($variation_id);
            $wpc_metas = $wpd_product->settings;
            $get_form_data = 0;
            if (class_exists('Ofb')) {
                if (isset($wpc_metas['form-builder'])) {
                    if ($wpc_metas['form-builder'] != '') {
                        $form_fields = $serialized_parts['form_fields'];

                        $data = array();
                        if (isset($form_fields)) {
                            foreach ($form_fields as $key => $value) {
                                $data[$key] = $value;
                            }
                        }

                        $get_form_data = get_form_data($wpc_metas['form-builder'], $data);
                    }
                }
            }
            $tpl_base_price = wpd_get_template_price($_POST['tpl']);

            foreach ($variations as $variation_id => $quantity) {

                $product = wc_get_product($variation_id);
                $product_price = $this->apply_quantity_based_discount_if_needed($product, $product->get_price(), $variations);
                $a_price = $this->get_additional_price($variation_id, $serialized_parts, $dimensions);
                $results[$variation_id] = $a_price + $product_price + $tpl_base_price + $get_form_data;
            }
        }

        echo json_encode(
                array(
                    'prices' => $results,
                )
        );
        die();
    }

    // WAD special integration
    private function apply_quantity_based_discount_if_needed($product, $normal_price, $products_qties) {
        // We check if there is a quantity based discount for this product
        $quantity_pricing = get_post_meta($product->get_id(), 'o-discount', true);
        // $products_qties = $this->get_cart_item_quantities();
        $rules_type = get_proper_value($quantity_pricing, 'rules-type', 'intervals');

        $id_to_check = $product->get_id();
        if (!isset($products_qties["$id_to_check"]) || empty($quantity_pricing) || !isset($quantity_pricing['enable'])) {
            return $normal_price;
        }

        if (isset($quantity_pricing['rules']) && $rules_type == 'intervals') {
            foreach ($quantity_pricing['rules'] as $rule) {
                if ($rule['min'] <= $products_qties[$id_to_check] && $products_qties[$id_to_check] <= $rule['max']) {
                    if ($quantity_pricing['type'] == 'fixed') {
                        $normal_price -= $rule['discount'];
                    } elseif ($quantity_pricing['type'] == 'percentage') {
                        $normal_price -= ( $normal_price * $rule['discount'] ) / 100;
                    }
                    break;
                }
            }
        } elseif (isset($quantity_pricing['rules-by-step']) && $rules_type == 'steps') {

            foreach ($quantity_pricing['rules-by-step'] as $rule) {
                if ($products_qties[$id_to_check] % $rule['every'] == 0) {
                    if ($quantity_pricing['type'] == 'fixed') {
                        $normal_price -= $rule['discount'];
                    } elseif ($quantity_pricing['type'] == 'percentage') {
                        $normal_price -= ( $normal_price * $rule['discount'] ) / 100;
                    }
                    break;
                }
            }
        }
        return $normal_price;
    }

    public function get_additional_price($product_id, $data, $dimensions = array()) {
        $wpd_product = new WPD_Product($product_id);
        $elements_analysis = $this->extract_priceable_elements($data, $dimensions);
        $priceable_elements = $elements_analysis[0];
        // Sum of prices per item (cliparts for example)
        $total_items_price = $elements_analysis[1];
        $wpc_metas = $wpd_product->settings;
        $pricing_rules = array();
        if (isset($wpc_metas['pricing-rules'])) {
            $pricing_rules = $wpc_metas['pricing-rules'];
        }

        $tpl_price = 0;
        if (isset($data['output']['tpl']) && !empty($data['output']['tpl'])) {
            $tpl_price = wpd_get_template_price($data['output']['tpl']);
        }

        $total_additionnal_price = 0;
        if (is_array($pricing_rules) && !empty($pricing_rules) && is_array($priceable_elements) && !empty($priceable_elements)) {
            $rule_group = 0;
            // For each rule group
            foreach ($pricing_rules as $rules_group) {
                $rule_index = 0;
                $rules = $rules_group['rules'];
                $additionnal_price = $rules_group['a_price'];
                $scope = $rules_group['scope'];

                $group_results = $this->get_group_results($priceable_elements, $rules);
                $group_count = $this->get_group_valid_items_count($group_results);
                // If the rules are not valid for this group, we skip the count

                if (!$group_count) {
                    continue;
                }
                if ($scope === 'item') {
                    foreach ($group_results as $key => $value) {
                        if ('i-text' === $key && isset($elements_analysis[0]['i-text'])) {

                            foreach ($elements_analysis[0]['i-text'] as $index => $val) {

                                foreach ($value as $group_result) {

                                    $total_additionnal_price += $additionnal_price * $group_result[$index]['nb_attr'];
                                }
                            }
                        } elseif ('text' === $key && isset($elements_analysis[0]['text'])) {
                            foreach ($elements_analysis[0]['text'] as $index => $val) {

                                foreach ($value as $group_result) {

                                    $total_additionnal_price += $additionnal_price * $group_result[$index]['nb_attr'];
                                }
                            }
                        } elseif ('image' === $key) {
                            $total_additionnal_price += $additionnal_price * $elements_analysis[0]['image'][0]['img_nb'];
                        } else {
                            $total_additionnal_price += $additionnal_price;
                        }
                    }
                } elseif ($scope === 'additional-items') {
                    $another_image = false;
                    foreach ($group_results as $value) {
                        foreach ($value[0] as $val) {
                            if (isset($val["type"]) && ("text" === $val["type"] || "i-text" === $val["type"])) {
                                $total_additionnal_price += $additionnal_price * $val['nb_additional_item'];
                            } elseif (isset($val["type"]) && "image" === $val["type"] && !$another_image) {
                                $total_additionnal_price += $additionnal_price * $val['nb_additional_item'];
                                $another_image = true;
                            }
                        }
                    }
                } else {
                    $total_additionnal_price += $additionnal_price;
                }
            }
        }
        return $total_additionnal_price + $total_items_price + $tpl_price;
    }

 
    private function extract_priceable_elements($data, $dimensions = array()) {

        $elements = array();
        $total_items_price = 0;

        if (!empty($dimensions)) {
            $square = $dimensions[0] * $dimensions[1];
            $elements['canvas-square'] = array();
            array_push(
                    $elements['canvas-square'], array(
                'canvas_square_unit' => $square,
                'price' => 0,
                    )
            );
        }
        $elements['canvas-part-used'] = array();

        if (is_array($data)) {
            foreach ($data as $part => $part_data) {

                if (is_object($part_data)) {
                    $part_data = (array) $part_data;
                }

                if (!isset($part_data['json'])) {

                    continue;
                }
                $raw_json = $part_data["json"];
                $json = str_replace("\n", "|n", $raw_json);
                $unslashed_json = stripslashes_deep($json);
                $decoded_json = json_decode($unslashed_json);
                if (!is_object($decoded_json)) {
                    continue;
                }
                $anonymous_function = function ( $o ) {
                    return $o->type;
                };
                $map = array_map($anonymous_function, $decoded_json->objects);
                $totals_by_type = array_count_values($map);

                $part_used = null;

                if (!empty($decoded_json->objects)) {
                    $part_used = $part;
                    array_push(
                            $elements['canvas-part-used'], array(
                        "$part_used" => $part_used,
                        'price' => 0,
                            )
                    );
                }

                foreach ($decoded_json->objects as $key => $object) {
                    $object_type = $object->type;
                    // We merge paths and paths group to a single type
                    if ($object->type == 'path' || $object->type == 'path-group' || $object->type == 'group') {
                        $object_type = 'path';
                    }
                    if (!isset($elements[$object_type]) || !is_array($elements[$object_type])) {
                        $elements[$object_type] = array();
                    }

                    // Object price defined by the user
                    $price = 0;
                    if (isset($object->price) && $object->price) {
                        $price = $object->price;
                    }
                    $total_items_price += $price;

                    if ($object_type == 'text' || $object_type == 'i-text') {
                        $nb_chars = strlen($decoded_json->objects[$key]->text);
                        $nb_lines = substr_count($decoded_json->objects[$key]->text, "\n") + substr_count($decoded_json->objects[$key]->text, "|n");
                        $is_just_one_ine = false;
                        if (0 === $nb_lines && 0 !== $nb_chars) {
                            $nb_lines = 1;
                            $is_just_one_ine = true;
                        }

                        if (!$is_just_one_ine) {
                            array_push($elements[$object_type], array("txt_nb_chars" => ($nb_chars - $nb_lines), "txt_nb_lines" => ($nb_lines + 1), "txt_nb" => $totals_by_type[$object_type]));
                        } else {
                            array_push($elements[$object_type], array("txt_nb_chars" => $nb_chars, "txt_nb_lines" => $nb_lines, "txt_nb" => $totals_by_type[$object_type]));
                        }
                    } elseif ($object_type == 'image') {
                        array_push(
                                $elements[$object_type], array(
                            'src' => $object->src,
                            'img_nb' => $totals_by_type[$object_type],
                            'price' => $price,
                                )
                        );
                    } elseif ($object_type == 'path' || $object_type == 'path-group' || $object_type == 'group') {
                        // We merge paths and paths group to a single type
                        $paths_total = 0;
                        if (isset($totals_by_type['path'])) {
                            $paths_total += $totals_by_type['path'];
                        }
                        if (isset($totals_by_type['path-group'])) {
                            $paths_total += $totals_by_type['path-group'];
                        }
                        if (isset($totals_by_type['group'])) {
                            $paths_total += $totals_by_type['group'];
                        }

                        array_push(
                                $elements[$object_type], array(
                            'path_nb' => $paths_total,
                            'price' => $price,
                                )
                        );
                    }
                }
            }
        }


        $elements = $this->fix_total_by_types($elements);
        return array($elements, $total_items_price);
    }

    private function fix_total_by_types($elements_by_types) {
        foreach ($elements_by_types as $type => $elements) {
            foreach ($elements as $i => $element) {
                if (isset($elements_by_types[$type][$i]['img_nb'])) {
                    $elements_by_types[$type][$i]['img_nb'] = count($elements);
                } elseif (isset($elements_by_types[$type][$i]['path_nb'])) {
                    $elements_by_types[$type][$i]['path_nb'] = count($elements);
                }
            }
        }
        return $elements_by_types;
    }

    private function wpc_starts_with($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }

    private function wpc_check_rule($objects, $rule, $type) {
        $param = $rule['param'];
        $value = $rule['value'];
        $operator = $rule['operator'];
        $results = array();
        foreach ($objects as $object) {

            if (isset($object[$param])) {
                $to_eval = "if($object[$param] $operator $value) return true; else return false;";
                $evaluation = eval($to_eval);
            } else {
                if (isset($object[sanitize_title($value)])) {

                    $evaluation = true;
                } else {

                    $evaluation = false;
                }
            }
            if ($evaluation) {
                if ($object[$param] > $value) {
                    array_push($results, array('evaluation' => $evaluation, 'nb_attr' => $object[$param], 'nb_additional_item' => $object[$param] - $value, 'type' => $type));
                } else {
                    array_push($results, array('evaluation' => $evaluation, 'nb_attr' => $object[$param], 'type' => $type));
                }
            } else {

                array_push($results, array('evaluation' => $evaluation));
            }
        }
        return $results;
    }

    private function get_group_valid_items_count($group_results) {
        $group_count = false;
        foreach ($group_results as $key => &$value) {
            foreach ($value as &$value1) {
                if (isset($value1[0]) && isset($value1[0]['evaluation'])) {
                    $value1[0] = $value1[0]['evaluation'];
                }
            }
        }
        foreach ($group_results as $group_type => $type_results) {
            if (count($type_results) === 1) {
                $intersection = current($type_results);
            } else {
                $intersection = call_user_func_array('array_intersect', $type_results);
            }
            $group_type_count = count(array_filter($intersection));

            // If at least one rule is not valid for any item, the group is not valid
            if (!$group_type_count) {
                return 0;
            } elseif ($group_count) {
                $group_count = min(array($group_count, $group_type_count));
            } else {
                $group_count = $group_type_count;
            }
        }

        return $group_count;
    }

    private function get_group_results($priceable_elements, $rules) {
        $group_results = array();
        //For each rule in the group
        foreach ($rules as $rule_arr) {
            //We skip invalid rules
            if (!$rule_arr["param"] || !$rule_arr["operator"] || !$rule_arr["value"])
                continue;
            //If it's a i-text rule
            if ($this->wpc_starts_with($rule_arr["param"], "txt")) {

                if (isset($priceable_elements["i-text"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["i-text"], $rule_arr, "i-text");
                else
                    $results_arr = array(array('evaluation' => false));

                if (!isset($group_results["i-text"]))
                    $group_results["i-text"] = array();
                array_push($group_results["i-text"], $results_arr);
            }
            //else if it's an image rule
            else if ($this->wpc_starts_with($rule_arr["param"], "img")) {
                if (isset($priceable_elements["image"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["image"], $rule_arr, "image");
                else
                    $results_arr = array(array('evaluation' => false));
                if (!isset($group_results["image"]))
                    $group_results["image"] = array();
                array_push($group_results["image"], $results_arr);
            }
            //else if it's a vector rule
            else if ($this->wpc_starts_with($rule_arr["param"], "path")) {
                if (isset($priceable_elements["path"]))
                    $results_arr = $this->wpc_check_rule($priceable_elements["path"], $rule_arr, "path");
                else
                    $results_arr = array(array('evaluation' => false));
                if (!isset($group_results["path"]))
                    $group_results["path"] = array();

                array_push($group_results["path"], $results_arr);
            }

            //else if it's a canvas dimensions rule
            else if ($this->wpc_starts_with($rule_arr["param"], "canvas")) {
                if (isset($priceable_elements["canvas-square"])) {
                    $results_arr = $this->wpc_check_rule($priceable_elements["canvas-square"], $rule_arr, "canvas-square");
                } else {
                    $results_arr = array(array('evaluation' => false));
                }

                if (!isset($group_results["canvas_square_unit"]))
                    $group_results["canvas_square_unit"] = array();

                array_push($group_results["canvas_square_unit"], $results_arr);
            }

            //else if it's a part used
            else if ($this->wpc_starts_with($rule_arr["param"], "part")) {
                if (isset($priceable_elements["canvas-part-used"])) {
                    $results_arr = $this->wpc_check_rule($priceable_elements["canvas-part-used"], $rule_arr, "canvas-part-used");
                } else {
                    $results_arr = array(array('evaluation' => false));
                }

                if (!isset($group_results["canvas_part_used"]))
                    $group_results["canvas_part_used"] = array();

                array_push($group_results["canvas_part_used"], $results_arr);
            }
        }

        return $group_results;
    }

    private function wpd_exec($cmd) {
        $output = array();
        exec("$cmd 2>&1", $output);
        return $output;
    }

    private function get_design_options_prices($json_wpc_design_options) {
        $wpc_design_options_prices = 0;
        if (!empty($json_wpc_design_options)) {
            $json = $json_wpc_design_options;
            $json = str_replace("\n", '|n', $json);
            $unslashed_json = stripslashes_deep($json);
            $decoded_json = json_decode($unslashed_json);
            if (is_object($decoded_json) && property_exists($decoded_json, 'opt_price')) {
                $wpc_design_options_prices = $decoded_json->opt_price;
            }
        }
        return $wpc_design_options_prices;
    }

    public static function get_design_pricing_options_data($wpc_design_pricing_options) {
        $wpc_design_pricing_options_data = '';
        if (!empty($wpc_design_pricing_options) && function_exists('ninja_forms_get_field_by_id')) {
            $decoded_json = self::wpc_json_decode($wpc_design_pricing_options);
            if (is_object($decoded_json)) {
                $wpc_ninja_form_fields_to_hide_name = array('_wpnonce', '_ninja_forms_display_submit', '_form_id', '_wp_http_referer');
                $wpc_ninja_form_fields_type_to_hide = array('_calc', '_honeypot');
                $wpc_ninja_form_id = '';
                if (isset($decoded_json->wpc_design_opt_list->_form_id)) {
                    $wpc_ninja_form_id = $decoded_json->wpc_design_opt_list->_form_id;
                }
                $wpc_design_pricing_options_data .= '<div class = "wpc_cart_item_form_data_wrap mg-bot-10">';
                foreach ($decoded_json->wpc_design_opt_list as $ninja_forms_field_id => $ninja_forms_field_value) {
                    if (!in_array($ninja_forms_field_id, $wpc_ninja_form_fields_to_hide_name)) {
                        $wpc_get_ninjaform_field_arg = array(
                            'id' => str_replace('ninja_forms_field_', '', $ninja_forms_field_id),
                            'form_id' => $wpc_ninja_form_id,
                        );
                        $wpc_ninjaform_field = ninja_forms_get_field_by_id($wpc_get_ninjaform_field_arg);
                        if (!in_array($wpc_ninjaform_field['type'], $wpc_ninja_form_fields_type_to_hide) && !( empty($wpc_ninjaform_field['data']['label']) && empty($ninja_forms_field_value) )) {
                            $wpc_ninja_form_field_value = $ninja_forms_field_value;
                            $wpc_design_pricing_options_data .= '<b>' . $wpc_ninjaform_field['data']['label'] . '</b>: ' . $wpc_ninja_form_field_value . '<br />';
                        }
                    }
                }
                $wpc_design_pricing_options_data .= '<div class = "wpc_cart_item_form_data_wrap">';
            }
        }
        return $wpc_design_pricing_options_data;
    }

    public static function get_option_form($product_id, $wpc_metas) {
        if (function_exists('ninja_forms_display_form')) {
            global $woocommerce;
            $product = wc_get_product($product_id);
            if ($product->get_type() == 'variation') {
                $normal_product_id = $product->get_parent_id();
            } else {
                $normal_product_id = $product_id;
            }

            if (isset($wpc_metas['ninja-form-options']) && !empty($wpc_metas['ninja-form-options'])) {
                $form_id = $wpc_metas['ninja-form-options'];
            }
            if (!empty($form_id)) {
                global $woocommerce;
                $currency_symbol = get_woocommerce_currency_symbol();
                $product_regular_price = get_post_meta(get_the_ID(), '_regular_price', true);

                // Fill the form in cart item edition case
                add_filter('ninja_forms_field', 'WPD_Design::wpc_fill_option_form', 10, 2);
                echo '<div class = "wpd-design-opt" data-currency_symbol = "' . $currency_symbol . '" data-regular_price = "' . $product_regular_price . '" >';
                ninja_forms_display_form($form_id);
                echo '</div>';
            }
        }
    }

    public static function wpc_json_decode($json) {
        $decoded_json = '';
        if (!empty($json)) {
            $json = str_replace("\n", '|n', $json);
            $unslashed_json = stripslashes_deep($json);
            $decoded_json = json_decode($unslashed_json);
        }
        return $decoded_json;
    }

    public static function wpc_fill_option_form($data, $field_id) {
        // $data will contain all of the field settings that have been saved for this field.
        // Let's change the default value of the field if in cart item edition case
        global $wp_query;
        if (isset($wp_query->query_vars['edit']) && isset($_SESSION['wpc_design_pricing_options'])) {
            $cart_item_key = $wp_query->query_vars['edit'];
            if (isset($_SESSION['wpc_design_pricing_options'][$cart_item_key])) {
                $wpc_json_ninja_form_fields = $_SESSION['wpc_design_pricing_options'][$cart_item_key];
                $wpc_ninja_form_fields = self::wpc_json_decode($wpc_json_ninja_form_fields);
                if (is_object($wpc_ninja_form_fields)) {
                    $wpc_design_opt_list = $wpc_ninja_form_fields->wpc_design_opt_list;
                    $wpc_ninja_form_id = $wpc_design_opt_list->_form_id;
                    $wpc_get_ninjaform_field_arg = array(
                        'id' => $field_id,
                        'form_id' => $wpc_ninja_form_id,
                    );
                    $wpc_ninjaform_field = ninja_forms_get_field_by_id($wpc_get_ninjaform_field_arg);

                    if (property_exists($wpc_design_opt_list, 'ninja_forms_field_' . $field_id)) {
                        $ninja_forms_field_id = 'ninja_forms_field_' . $field_id;
                        if ($wpc_ninjaform_field['type'] == '_checkbox') {
                            $default_value = '';
                            $checkbox = trim($wpc_design_opt_list->$ninja_forms_field_id);
                            if ($checkbox == 'checked') {
                                $default_value = $checkbox;
                            }
                            $data['default_value'] = $default_value;
                        } else {
                            $data['default_value'] = $wpc_design_opt_list->$ninja_forms_field_id;
                        }
                    } elseif (property_exists($wpc_design_opt_list, 'ninja_forms_field_' . $field_id . '[]')) {
                        $ninja_forms_field_id = 'ninja_forms_field_' . $field_id . '[]';
                        if ($wpc_ninjaform_field['data']['list_type'] == 'checkbox') {
                            $checkbox_list = explode(';', $wpc_design_opt_list->$ninja_forms_field_id);
                            $default_value = array();
                            foreach ($checkbox_list as $checkbox) {
                                $checkbox = explode(':', $checkbox);
                                if (isset($checkbox[1]) && trim($checkbox[1]) == 'checked') {
                                    $default_value[] = trim($checkbox[0]);
                                }
                            }
                            $data['default_value'] = $default_value;
                        } elseif ($wpc_ninjaform_field['data']['list_type'] == 'multi') {
                            $multi_list = explode('|', $wpc_design_opt_list->$ninja_forms_field_id);
                            $default_value = array();
                            foreach ($multi_list as $value) {
                                $default_value[] = trim($value);
                            }
                            $data['default_value'] = $default_value;
                        }
                    }
                }
            }
        }
        return $data;
    }

    private function save_pdf_output($variation_id, $input_file, $output_file, $pdf_fonts = array()) {
        $wpd_product = new WPD_Product($variation_id);
        $product_metas = $wpd_product->settings;
        $variation_output_settings = get_proper_value($product_metas, 'output-settings', array());
//        $pdf_format = get_proper_value($variation_output_settings, 'pdf-format', 'A0');
	
        // Some formats are grouped together and separated by ,
//        if (strpos($pdf_format, ',') !== false) {
//            $formats = explode(',', $pdf_format);
//            $pdf_format = $formats[0];
//        }

        $pdf_orientation = get_proper_value($variation_output_settings, 'pdf-orientation', 'P');
        $pdf_margin_lr = get_proper_value($variation_output_settings, 'pdf-margin-lr', 0);
        $pdf_margin_tb = get_proper_value($variation_output_settings, 'pdf-margin-tb', 0);

        $pdf_unit = PDF_UNIT;
//        if ($pdf_format == 'custom') {
            $pdf_width=get_proper_value($variation_output_settings, 'output-w', 0);
	$pdf_height=get_proper_value($variation_output_settings, 'output-h', 0);
            $pdf_unit = get_proper_value($variation_output_settings, 'output-unit', 'in');
            if ($pdf_unit == 'px') {
                $pdf_unit = 'mm';
                $pdf_width = $pdf_width * 0.264583333;
                $pdf_height = $pdf_height * 0.264583333;
            }
            $pdf_format = array($pdf_width, $pdf_height);
//        } else {
//            $dimensions = TCPDF_STATIC::getPageSizeFromFormat($variation_output_settings['pdf-format']);
//            $pdf_width = $dimensions[0];
//            $pdf_height = $dimensions[1];
//        }

        $pdf = new TCPDF($pdf_orientation, $pdf_unit, $pdf_format, true, 'UTF-8', false);

        $pdf->SetCreator('Woocommerce Products Designer by ORION');
        $pdf->SetAuthor('Woocommerce Products Designer by ORION');
        $pdf->SetTitle('Output');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins($pdf_margin_lr, $pdf_margin_tb, -1, true);
        $pdf->SetHeaderMargin($pdf_margin_tb);
        $pdf->SetFooterMargin($pdf_margin_tb);

        // set auto page breaks
        $pdf->SetAutoPageBreak(true, $pdf_margin_tb);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf->setLanguageArray($l);
        }

        $pdf->AddPage();

        $path_parts = pathinfo($input_file);
        $ext = $path_parts['extension'];
        if ($ext == 'svg') {
            // We write the fonts
            $wpd_fonts = get_option('wpc-fonts');
            if (empty($wpd_fonts)) {
                $wpd_fonts = wpd_get_default_fonts();
            }
            $real_fonts_names = array();
            foreach ($wpd_fonts as $font) {
                $font_label = $font[0];
                if (in_array($font_label, $pdf_fonts)) {
                    // If the font file has been defined
                    if (isset($font[2]) && !empty($font[2])) {
                        $font_files = $font[2];
                        foreach ($font_files as $font_file_row) {
                            $styles = implode('', $font_file_row['styles']);

                            $font_file_path = get_attached_file($font_file_row['file_id']);
                            if (!file_exists($font_file_path)) {
                                continue;
                            }
                            // convert TTF font to TCPDF format and store it on the fonts folder
                            $fontname = TCPDF_FONTS::addTTFfont($font_file_path, 'TrueTypeUnicode', $styles, 96);
                            $real_fonts_names[$font_label] = $fontname;
                            // use the font
                            $pdf->SetFont($fontname, '', 14, '', false);
                        }
                    }
                }
            }
            $this->replace_fonts_names_in_svg($input_file, $real_fonts_names);
            $pf = TCPDF_STATIC::getPageSizeFromFormat(get_proper_value($variation_output_settings, 'pdf-format', 'A0'));
            $pdf->ImageSVG($file = $input_file, $x = '', $y = '', $w = $pf[0], $h = 0, $link = '', $align = '', $palign = 'C', $border = 0, $fitonpage = true);
        } else {
            $pdf->Image($file = $input_file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = 'C', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
        }

        // Crop marks
        $crop_marks = get_proper_value($variation_output_settings, 'crop-marks', array());
        if (!empty($crop_marks) && is_array($crop_marks)) {
            foreach ($crop_marks as $crop_mark) {
                $pdf->cropMark($crop_mark['x'], $crop_mark['y'], $crop_mark['w'], $crop_mark['h'], $crop_mark['type']);
            }
        }

        // Zones
        $zones = get_proper_value($variation_output_settings, 'zones', array());
        if (!empty($zones) && is_array($zones)) {
            foreach ($zones as $zone) {
                $zone_w = $pdf_width - $zone['h'] * 2;
                $zone_h = $pdf_height - $zone['v'] * 2;
                $dash = get_proper_value($zone, 'dash-spacing', 0);
                $hex_color = get_proper_value($zone, 'color', '#FF0000');
                $color = wpd_hex_to_rgb($hex_color);
                $border_width = get_proper_value($zone, 'border-width', 0);
                $style3 = array(
                    'width' => $border_width,
                    'cap' => 'butt',
                    'join' => 'miter',
                    'dash' => $dash,
                    'color' => $color,
                );
                $pdf->Rect($zone['h'], $zone['v'], $zone_w, $zone_h, 'D', array('all' => $style3));
            }
        }

        $pdf->Output($output_file, 'F');
    }

    private function replace_fonts_names_in_svg($input, $fonts) {
        $xdoc = new DomDocument();
        $xdoc->Load($input);
        $text_elements = $xdoc->getElementsByTagName('text');
        for ($i = 0; $i < $text_elements->length; $i++) {
            $tagName = $xdoc->getElementsByTagName('text')->item($i);
            $attribNode = $tagName->getAttributeNode('font-family');
            $font_family = $attribNode->value;

            if (!isset($fonts[$font_family])) {
                continue;
            }
            $tagName->setAttribute('font-family', $fonts[$font_family]);

            $new_svg = $xdoc->saveXML();
            file_put_contents($input, $new_svg);
        }
    }

    /**
     * Export data to archive
     *
     * @param string $generation_dir Working directory path
     * @param array  $data Data to export
     * @param int    $variation_id Product/Variation ID
     * @return boolean|string
     */
    private function export_data_to_files($generation_dir, $data, $variation_id, $zip_name, $pdf_watermark = false) {
        global $wpd_settings;
        $global_output_settings = $wpd_settings['wpc-output-options'];
        $generate_pdf = false;
        $generate_zip = false;

        $wpd_product = new WPD_Product($variation_id);
        $product_metas = $wpd_product->settings;
        $product_output_settings = get_proper_value($product_metas, 'output-settings', array());
        $watermark_id = get_proper_value($product_metas, 'watermark', false);
        $watermark = false;
        if ($watermark_id) {
            $watermark = o_get_proper_image_url($watermark_id);
        }

        if ($product_output_settings['output-format'] == 'pdf+png' || $product_output_settings['output-format'] == 'pdf+jpg' || $product_output_settings['output-format'] == 'pdf+svg') {
            $generate_pdf = true;
        }
        if ($product_output_settings['zip-output'] == 'yes') {
            $generate_zip = true;
        }

        $wpc_cmyk_conversion = $wpd_product->get_option($product_output_settings, $global_output_settings, 'wpc-cmyk-conversion', 'no');
        if (!class_exists('Imagick')) {
            $wpc_cmyk_conversion = 'no';
        }

        $wpc_img_format = $_POST['format'];
        $allowed_extensions = array('png', 'jpg');
        if (!in_array($wpc_img_format, $allowed_extensions)) {
            return false;
        }

        $output_arr = array();
        foreach ($data as $part_key => $part_data) {
            $part_dir = "$generation_dir/$part_key";
            if (!wp_mkdir_p($part_dir)) {
                echo "Can't create part directory...";
                continue;
            }

            // Part image
            $output_file_path = $part_dir . "/$part_key.$wpc_img_format";

            $moved = move_uploaded_file($_FILES[$part_key]['tmp_name']['image'], $output_file_path);

            if ($wpc_cmyk_conversion == 'yes') {
                $wpc_cmyk_profil = get_proper_value($global_output_settings, 'wpc-cmyk-profil', false);

                if ($wpc_cmyk_profil) {
                    $wpc_cmyk_profil = get_home_path() . $wpc_cmyk_profil;
                    $cmd = "convert $output_file_path -colorspace cmyk -profile " . $wpc_cmyk_profil . " $output_file_path";
                } else {
                    $cmd = "convert $output_file_path -colorspace cmyk $output_file_path";
                }

                $exec_result = $this->wpd_exec($cmd);
                if (!empty($exec_result)) {
                    echo $exec_result[0] . '<br>';
                }
            }

            $output_arr[$part_key]['image'] = "$part_key.$wpc_img_format";

            // Preview
            if ($watermark_id) {
                $preview_filename = uniqid('preview_') . ".$wpc_img_format";
                $preview_file_path = "$part_dir/" . $preview_filename;
                $this->merge_pictures($output_file_path, $watermark, $preview_file_path, $variation_id);
                $output_arr[$part_key]['preview'] = $preview_filename;
            } else {
                $output_arr[$part_key]['preview'] = $output_arr[$part_key]['image'];
            }

            if (!$generate_pdf && !$generate_zip) {
                $output_arr[$part_key]['file'] = "$part_key.$wpc_img_format";
            }

            $fonts = array();
            // SVG
            if ($product_output_settings['output-format'] == 'svg' || $product_output_settings['output-format'] == 'pdf+svg') {
                $svg_path = $part_dir . "/$part_key.svg";

                file_put_contents($svg_path, stripcslashes($part_data['svg']), FILE_APPEND | LOCK_EX);
                $this->embbed_images_in_svg($svg_path, $svg_path);
                $output_file_path = $svg_path;

                // Fonts extraction
                $raw_json = $part_data['json'];
                $json = str_replace("\n", '|n', $raw_json);
                $unslashed_json = stripslashes_deep($json);
                $decoded_json = json_decode($unslashed_json);
                if (!is_object($decoded_json)) {
                    continue;
                }
                $map = array_map(create_function('$o', 'return $o->type;'), $decoded_json->objects);
                foreach ($decoded_json->objects as $object) {
                    $object_type = $object->type;
                    if ($object_type == 'text' || $object_type == 'i-text') {
                        if (!in_array($object->fontFamily, $fonts)) {
                            array_push($fonts, $object->fontFamily);
                        }
                    }
                }

                $output_arr[$part_key]['file'] = "$part_key.svg";
            }

            // Part pdf
            if ($generate_pdf) {

                $output_pdf_file_path = $part_dir . "/$part_key.pdf";

                $pdf_generation_method = apply_filters('wpd_pdf_generation_method', array($this, 'save_pdf_output'));

                if ($pdf_watermark && $watermark_id) {
                    call_user_func($pdf_generation_method, $variation_id, $preview_file_path, $output_pdf_file_path, $fonts);
                } else {
                    $preview_file_path = $output_file_path;
                    call_user_func($pdf_generation_method, $variation_id, $output_file_path, $output_pdf_file_path, $fonts);
                }

                do_action('wpd_after_pdf_generation', $pdf_generation_method, $variation_id, $output_file_path, $preview_file_path, $output_pdf_file_path, $fonts, $pdf_watermark, $watermark_id);

                if (!$generate_zip) {
                    $output_arr[$part_key]['file'] = "$part_key.pdf";
                }
            }
        }

        $result = $this->generate_design_archive($generation_dir, "$generation_dir/$zip_name");
        return $output_arr;
    }

    private function embbed_images_in_svg($input, $output) {
        $xdoc = new DomDocument();
        $xdoc->Load($input);
        $images = $xdoc->getElementsByTagName('image');
        for ($i = 0; $i < $images->length; $i++) {
            $tagName = $xdoc->getElementsByTagName('image')->item($i);
            $attribNode = $tagName->getAttributeNode('xlink:href');
            $img_src = $attribNode->value;
            if (strpos($img_src, 'data:image') !== false) {
                continue;
            }

            $type = pathinfo($img_src, PATHINFO_EXTENSION);
            $data = $this->url_get_contents($img_src);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $tagName->setAttribute('xlink:href', $base64);

            $new_svg = $xdoc->saveXML();
            file_put_contents($output, $new_svg);
        }
    }

    /**
     * Creates a compressed zip file
     *
     * @param type $source Input directory path to zip
     * @param type $destination Output file path
     * @return boolean
     */
    private function generate_design_archive($source, $destination) {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
                } elseif (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file), $this->url_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), $this->url_get_contents($source));
        }

        return $zip->close();
    }

    function unset_wpc_data_upl_meta($hidden_meta) {
        array_push($hidden_meta, 'wpc_data_upl');
        array_push($hidden_meta, '_wpc_design_pricing_options');
        return $hidden_meta;
    }

    function force_individual_cart_items($cart_item_data, $product_id) {
        if (isset($_SESSION['wpc-user-uploaded-designs'][$product_id])) {
            $unique_cart_item_key = md5(microtime() . rand());
            $cart_item_data['unique_key'] = $unique_cart_item_key;
        }

        return $cart_item_data;
    }

    function save_data_to_reload() {
        $_SESSION['wpd-data-to-load'] = $_POST['serialized_parts'];
        echo json_encode(
                array(
                    'success' => true,
                )
        );
    }

    function save_user_temporary_designs($user_login, $user) {

        if (isset($_SESSION['wpc_designs_to_save'])) {
            foreach ($_SESSION['wpc_designs_to_save'] as $variation_id => $design_array) {
                foreach ($design_array as $key => $design) {
                    $today = date('Y-m-d H:i:s');
                    add_user_meta($user->ID, 'wpc_saved_designs', array($variation_id, $today, $design));
                }
                unset($_SESSION['wpc_designs_to_save'][$variation_id]);
            }
            unset($_SESSION['wpc_designs_to_save']);
        }
    }

    /**
     * Replacement just in case file_get_contents fails
     *
     * @param type $Url
     * @return type
     */
    function url_get_contents($Url) {
        // If it's a path, we prefer use the file_get_contents
        if (function_exists('curl_init') && !file_exists($Url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $Url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
        } else {
            $output = file_get_contents($Url);
        }

        return $output;
    }

    public function get_desing_details_from_json($unserialized_design_data) {
        ob_start();
        ?>
        <table class="wpc-grid wpd-design-composition">
            <div style="margin: 10px 0;"><?php _e('Design composition: ', 'wpd'); ?></div>
            <?php
            foreach ($unserialized_design_data as $part_name => $part_data) {
                if (isset($part_data['json']) && !empty($part_data['json'])) {
                    $part_details = json_decode($part_data['json']);
                    ?>
                    <tr>
                        <td class="wpc-col-1-3 wpc-part-name">
                            <?php echo $part_name; ?>
                        </td>
                        <td class="wpc-col-2-3 wpc-part-details">
                            <?php
                            $this->get_design_part_details($part_details);
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <?php
        $output = ob_get_clean();
        return $output;
    }

    public function get_design_part_details($part_details) {
        $to_exclude = apply_filters(
                'wpd_part_details_to_exclude', array(
            'originX',
            'originY',
            'strokeLineCap',
            'strokeLineJoin',
            'fillRule',
            'globalCompositeOperation',
            'crossOrigin',
            'meetOrSlice',
            'strokeMiterLimit',
            'paths',
                )
        );
        ?>
        <table>
            <?php
            foreach ($part_details->objects as $i => $details) {
                if (!empty($details)) {
                    ?>
                    <tr>
                        <?php
                        $item = get_object_vars($details);
                        if ($details->type == 'group') {
                            // Curved text
                            if (property_exists($details, 'originalText')) {
                                $item['type'] = 'Curved Text';
                                $item['FontSize'] = $item['objects'][0]->fontSize;
                                $item['FontWeight'] = $item['objects'][0]->fontWeight;
                                $item['FontFamily'] = $item['objects'][0]->fontFamily;
                                unset($item['originalText']);
                                unset($item['objects']);
                            }
                        }
                        $this->get_part_item_details($item, $to_exclude);
                        ?>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <?php
    }

    public function get_part_item_details($item, $to_exclude) {
        ?>
        <td class="wpc-col-1-3">
            <?php echo ucfirst($item['type']); ?>
        </td>
        <td class="wpc-col-2-3">
            <?php
            //var_dump($item);
            foreach ($item as $key => $value) {
                if ($key == 'type') {
                    continue;
                }
               
                if ($key == 'src' || $key=='svgSrc') {
                    
                    $key = 'Image';
                    $png_file_url = $value;
                    $value = apply_filters('wpd_get_original_upload_file_name', $value);
                    $value = "<img src='$png_file_url'><a class='button' href='$value' download='" . basename($value) . "'>Download</a>";
                }
                if (in_array($key, $to_exclude)) {
                    continue;
                }
                if (is_object($value)) {
                    continue;
                }
                if (!empty($value)) {
                    ?>
                    <span><strong><?php echo ucfirst($key); ?></strong>: <?php echo $value; ?></span><br />
                    <?php
                }
            }
            ?>
        </td>
        <?php
    }

    public function get_desing_details_from_json_old($unserialized_design_data) {
        $to_exclude = array(
            'originX',
            'originY',
            'strokeLineCap',
            'strokeLineJoin',
            'fillRule',
            'globalCompositeOperation',
            'crossOrigin',
            'meetOrSlice',
            'strokeMiterLimit',
        );
        $output = '<div class="wpc-col-1-1 wpd-design-composition">' . __('Design composition: ', 'wpd') . '<br />';
        foreach ($unserialized_design_data as $part_name => $part_data) {
            if (isset($part_data['json']) && !empty($part_data['json'])) {

                $output .= '<div class="wpc-col-1-1"><div class="wpd-design-part"><b>' . $part_name . '</b></div></div> ';
                $part_details = json_decode($part_data['json']);
                $output .= '<div class="wpc-col-1-1">';
                if (property_exists($part_details, 'background') && !empty($part_details->background)) {
                    $output .= '<div class="wpc-col-4-12">' . __('Background', 'wpd') . ': </div>';
                    $output .= '<div class="wpc-col-8-12"><a href="' . $part_details->background->src . '"><img src="' . $part_details->background->src . '" alt="" style="max-width: 90px;  max-height: 90px;"/></a></div>';
                }

                if (property_exists($part_details, 'backgroundImage') && !empty($part_details->backgroundImage)) {
                    $output .= '<div class="wpc-col-4-12">' . __('Background (inc)', 'wpd') . ': </div>';
                    $output .= '<div class="wpc-col-8-12"><a href="' . $part_details->backgroundImage->src . '"><img src="' . $part_details->backgroundImage->src . '" alt="" style="max-width: 90px;  max-height: 90px;"/></a></div>';
                }
                if (property_exists($part_details, 'overlayImage') && !empty($part_details->overlayImage)) {
                    $output .= '<div class="wpc-col-4-12">' . __('Overlay', 'wpd') . ': </div>';
                    $output .= '<div class="wpc-col-8-12"><a href="' . $part_details->overlayImage->src . '"><img src="' . $part_details->overlayImage->src . '" alt="" style="max-width: 90px;  max-height: 90px;"/></a></div>';
                }
                foreach ($part_details->objects as $i => $details) {
                    if (!empty($details)) {

                        $output .= '<div class="wpc-col-1-12">' . ( $i + 1 ) . '-</div>';
                        $output .= '<div  class="wpc-col-10-12">';
                        foreach ($details as $detail_key => $detail_value) {
                            if ($detail_key == 'src') {
                                $detail_key = 'Image';
                                $detail_value = "<img src='$detail_value'><a class='button' href='$detail_value' download='" . basename($detail_value) . "'>Download</a>";
                            }
                            if (in_array($detail_key, $to_exclude)) {
                                continue;
                            }
                            if (is_object($detail_value)) {
                                continue;
                            }
                            if (!empty($detail_value)) {
                                $output .= '<span><strong>' . ucfirst($detail_key) . ': </strong>' . $detail_value . '</span><br />';
                            }
                        }
                        $output .= '</div>';
                    }
                }
                $output .= '</div></div> ';
            }
        }
        $output .= '</div>';
        return $output;
    }

    function get_user_account_load_order_button($actions, $order) {
        $items = $order->get_items();
        foreach ($items as $order_item_id => $item) {
            if (isset($item['wpc_data'])) {
                if (isset($item['variation_id']) && !empty($item['variation_id'])) {
                    $product_id = $item['variation_id'];
                } else {
                    $product_id = $item['product_id'];
                }
                $wpd_product = new WPD_Product($product_id);

                $actions['wpd-reload'] = array(
                    'url' => $wpd_product->get_design_url(false, false, $order_item_id),
                    'name' => __('Reload Design: ', 'wpd') . $item['name'],
                );
            }
        }
        return $actions;
    }

    function get_user_saved_designs() {
        global $current_user;
        $user_designs = get_user_meta($current_user->ID, 'wpc_saved_designs');
        if (empty($user_designs)) {
            _e('No saved design.', 'wpd');
            return;
        }
        ?>
        <h2><?php _e('Saved Designs', 'wpd'); ?></h2>
        <table class="shop_table shop_table_responsive my_account_orders">

            <thead>
                <tr>
                    <th class="order-date"><span class="nobr"><?php _e('Date', 'wpd'); ?></span></th>
                    <th class="order-status"><span class="nobr"><?php _e('Preview', 'wpd'); ?></span></th>
                    <th class="order-actions"><span class="nobr">&nbsp;</span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($user_designs as $s_index => $user_design) {
                    if (!empty($user_design)) {
                        $variation_id = $user_design[0];
                        $save_time = $user_design[1];
                        $design_data = $user_design[2];
                        $order_item_id = '';
                        // Comes from an order
                        if (count($user_design) >= 4) {
                            $order_item_id = $user_design[3];
                        }
                        echo "<tr class='wpc_order_item' data-item='$variation_id'>";
                        if (count($user_design) > 1) {
                            echo "<td >$save_time</td>";
                        }
                        if (is_array($design_data)) {
                            $new_version = false;
                            $upload_dir = wp_upload_dir();
                            if (isset($design_data['output']['files'])) {
                                $tmp_dir = $design_data['output']['working_dir'];
                                $design_data = $design_data['output']['files'];
                                $new_version = true;
                            }
                            echo '<td>';
                            foreach ($design_data as $data_key => $data) {
                                if (!empty($data)) {
                                    if ($new_version) {
                                        $generation_url = $upload_dir['baseurl'] . "/WPC/$tmp_dir/$data_key/";
                                        $img_src = $generation_url . $data['image'];
                                        $original_part_img_url = '';
                                    } else {
                                        if (!isset($data['image'])) {
                                            continue;
                                        }
                                        $img_src = $data['image'];
                                        $original_part_img_url = $data['original_part_img'];
                                    }

                                    if ($order_item_id) {
                                        $modal_id = $order_item_id . "_$variation_id" . "_$data_key";
                                    } else {
                                        $modal_id = $s_index . "_$variation_id" . "_$data_key";
                                    }

                                    echo '<span><a class="wpd-button button" data-toggle="o-modal" data-target="#' . $modal_id . '">' . ucfirst($data_key) . '</a></span>';
                                    $modal = '<div class="omodal fade o-modal wpc-modal wpc_part" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="omodal-dialog">
                                          <div class="omodal-content">
                                            <div class="omodal-header">
                                              <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                              <h4 class="omodal-title" id="myModalLabel' . $modal_id . '">' . __('Preview', 'wpd') . '</h4>
                                            </div>
                                            <div class="omodal-body">
                                                <div style="background-image:url(' . $original_part_img_url . ')"><img src="' . $img_src . '"></div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>';
                                    array_push(wpd_retarded_actions::$code, $modal);
                                    add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                                }
                            }
                            echo '</td>';
                            echo '<td>';
                            $wpd_product = new WPD_Product($variation_id);
                            if ($order_item_id) {
                                echo '<a class="wpd-button button" href="' . $wpd_product->get_design_url(false, false, $order_item_id) . '">' . __('Load', 'wpc') . '</a>';
                            } else {
                                echo '<a class="wpd-button button" href="' . $wpd_product->get_design_url($s_index) . '">' . __('Load', 'wpc') . '</a>';
                                echo '<a class="wpd-button wpd-delete-design button" data-index="' . $s_index . '">' . __('Delete', 'wpc') . '</a>';
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                }
                ?>
                <tr class="order">
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

}
