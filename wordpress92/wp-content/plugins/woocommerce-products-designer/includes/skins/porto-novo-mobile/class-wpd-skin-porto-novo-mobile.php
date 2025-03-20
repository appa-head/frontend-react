<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-vpc-default-skin
 *
 * @author HL
 */
class WPD_Skin_Porto_Novo_Mobile {

    public $editor;
    public $wpc_metas;

    public function __construct($editor_obj, $wpc_metas) {
        if ($editor_obj) {
            $this->editor = $editor_obj;
            $this->wpc_metas = $wpc_metas;
        }
    }

    public function display() {
        global $wpd_settings;
        $wpd_settings = apply_filters('wpd_global_settings', $wpd_settings);

        ob_start();

        $this->register_styles();
        $this->register_scripts();

        $text_options = get_proper_value($wpd_settings, 'wpc-texts-options', array());
        $shapes_options = get_proper_value($wpd_settings, 'wpc-shapes-options', array());
        $cliparts_options = get_proper_value($wpd_settings, 'wpc-images-options', array());
        $uploads_options = get_proper_value($wpd_settings, 'wpc-upload-options', array());
        $designs_options = get_proper_value($wpd_settings, 'wpc-designs-options', array());
        $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
        $toolbar_options = get_proper_value($wpd_settings, 'wpc-toolbar-options', array());

        $design_tab_visible = get_proper_value($designs_options, 'visible-tab', 'yes');

        $toolbar_clear_all = get_proper_value($toolbar_options, 'clear', 'yes');
        $toolbar_delete = get_proper_value($toolbar_options, 'delete', 'yes');
        $toolbar_dulpicate = get_proper_value($toolbar_options, 'duplicate', 'yes');
        $toolbar_send_to_back = get_proper_value($toolbar_options, 'send-to-back', 'yes');
        $toolbar_bring_to_front = get_proper_value($toolbar_options, 'bring-to-front', 'yes');
        $toolbar_flip_horizontally = get_proper_value($toolbar_options, 'flipH', 'yes');
        $toolbar_flip_vertically = get_proper_value($toolbar_options, 'flipV', 'yes');
        $toolbar_center_horizontally = get_proper_value($toolbar_options, 'centerH', 'yes');
        $toolbar_center_vertically = get_proper_value($toolbar_options, 'centerV', 'yes');

        $responsive_behavior = get_proper_value($wpd_settings['wpc-general-options'], 'responsive', array());

        if ($responsive_behavior == 1) {
            $wpd_responsive_class = 'wpd-responsive-mode';
            $wpd_responsive_menu = '<div class="wpc-editor-menu-box">Menu<button class="wpc-editor-menu"><i class="fa fa-bars"></i></button></div>';
            $wpd_responsive_menu2 = '<div class="wpc-editor-menu-box">Actions<button class="wpc-editor-menu-right"><i class="fa fa-cog"></i></button></div>';
        } else {
            $wpd_responsive_class = '';
            $wpd_responsive_menu = '';
            $wpd_responsive_menu2 = '';
        }

        $ui_fields = wpd_get_ui_options_fields();

        foreach ($ui_fields as $key => $field) {
            $icon_field_name = '';

            if (isset($field['icon']) || isset($field['bg-color']) || isset($field['txt-color'])) {
                if (isset($field['icon'])) {
                    $icon_field_name = $key . '-icon';
                    wpd_generate_css_tab($ui_options, "$key", $icon_field_name, $key);
                }
                if (isset($field['bg-color']) || isset($field['text-color'])) {
                    wpd_generate_css_tab($ui_options, "$key", '', $field['fied-name']);
                }
            } else {
                wpd_generate_css_tab($ui_options, "$key", $icon_field_name, $key);
            }
        }
        apply_filters('wpd_show_design_save_name', 1);
        ?>

        <!--old editor-->
        <!--end old editor-->

        <!--New-->
        <div class="wpc-container wpc-porto-skin wpd-responsive-mode mobile">      
            <div class="wpc-editor-wrap ">
                <div class="wpc-editor-col-2">
                    <div class="container">
                        <div id="tab-customize" class="wpd-tab-content current">
                            <div id="wpc-editor-container">
                                <div class="wpd-editor-right-tools-wrap">
                                    <div class="wpc-button-bar-wrap">
                                        <div class="wpc-button-bar">
                                            <span id="undo-btn" class="icon-wpd_icons-09 disabled" data-placement="top" data-tooltip-title="<?php _e('Double click to undo', 'wpd'); ?>"></span>
                                            <span id="redo-btn" class="icon-wpd_icons-08 disabled" data-placement="top" data-tooltip-title="<?php _e('Double click to redo', 'wpd'); ?>"></span>
                                            <span id="grid-btn" class="icon-1216" data-placement="top" data-tooltip-title="<?php _e("Double clik to show grid","wpd")?>"></span> 
                                        </div>
                                                  
                                        <div class="wpc-button-bar">
                                         <?php $this->get_design_actions_box(); ?>
                                         </div>
                                           
                                    </div>
                                </div>
                                <div>
                                    <canvas id="wpc-editor"></canvas>
                                </div>
                                <div class="wpd-editor-bottom-tools-wrap wpd_btn_hidden">
                                    <div class="wpc-button-bar-wrap">
                                        <div class="wpc-button-bar"> 
                                            <span id="flip_h_btn" class="icon-687 wpd_btn_hidden" data-placement="top" data-tooltip-title="<?php _e("Double click to flip horizontally","wpd")?>"></span>
                                            <span id="flip_v_btn" class="icon-691 wpd_btn_hidden" data-placement="top" data-tooltip-title="<?php _e("Double click to flip vertically","wpd")?>"></span> 
                                            <span id="align_btn" class="icon-683 wpd_btn_hidden"></span>
                                            <div class="align_btn_wrap">
                                                <div> 
                                                    <span id="align_h_btn" class="icon-680" data-placement="top" data-tooltip-title="<?php _e("Double click to center horizontally","wpd")?>"></span>
                                                    <span id="align_v_btn" class="icon-682" data-placement="top" data-tooltip-title="<?php _e("Double click to center vertically","wpd")?>"></span>
                                                    <span id="send_to_back_btn" class="icon-677" data-placement="top" data-tooltip-title="<?php _e("Double click to send to back","wpd")?>"></span>
                                                    <span id="bring_to_front_btn" class="icon-676" data-placement="top" data-tooltip-title="<?php _e("Double click to bring to front","wpd")?>"></span>
                                                </div>
                                            </div> 
                                        </div>
                                        <div class="wpc-button-bar">
                                           <span id="wpd-edit-object" class="icon-516 wpd_btn_hidden" data-placement="top" data-tooltip-title="<?php _e("Double click to Edit", "wpd") ?>"></span>
                                        </div>
                                        <div class="wpc-button-bar">
                                            <span id="copy_paste_btn" class="icon-443 wpd_btn_hidden" data-placement="top" data-tooltip-title="<?php _e("Double click to duplicate","wpd")?>"></span>
                                            <span id="delete_btn" class="icon-014 wpd_btn_hidden" ></span>
                                            <span id="clear_all_btn" class="icon-1407 wpd_btn_hidden" ></span>
                                        </div>
                                       

                                    </div>
                                </div>
                                <?php $this->get_parts(); ?>

                            </div>
                        </div>                 
                        <div id="debug-wrap">
                            <div id="debug"></div>
                            <div class="debug-icon  ti-close"></div> 
                        </div>       
                    </div>
                    <div class="wpc-editor-col wpc-editor-col-tools ">
                        <div class="wpc-tools-container ">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <div class="wpc-tools-head" id="text-panel">
                                            <div id="text" class="icon-261" data-placement="top" data-tooltip-title="<?php _e('Double click to open text tools', 'wpd'); ?>"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="swiper-slide" >
                                        <div class="wpc-tools-head" id="cliparts-panel">
                                            <div id="cliparts" class="icon-1143" data-placement="top" data-tooltip-title="<?php _e('Double click to open clipart tools', 'wpd'); ?>"></div>
                                        </div>
                                    </div>
                                    
                            
                                    <div class="swiper-slide">
                                        <div class="wpc-tools-head" id="uploads-panel">
                                            <div id="uploads" class="icon-1197" data-placement="top" data-tooltip-title="<?php _e('Double click to open upload tools', 'wpd'); ?>"></div>
                                        </div>
                                    </div>
                                    
                                   
                                    <div class="swiper-slide">
                                        <div class="wpc-tools-head" id="shapes-panel">
                                            <div id="shapes" class="icon-951" data-placement="top" data-tooltip-title="<?php _e('Double click to open shape tools', 'wpd'); ?>"></div>
                                        </div>
                                    </div>
                                   
                                    <div class="swiper-slide">
                                        <div class="wpc-tools-head" id="add-cart-panel">
                                            <div id="add-cart-content" class="icon-1325" data-placement="top" data-tooltip-title="<?php _e('Double click to open cart panel', 'wpd'); ?>"></div>
                                        </div>
                                    </div>
                            
                                </div>
                                <!-- If we need navigation buttons -->
                                <div class="swiper-button-prev swiper-button-black"></div>
                                <div class="swiper-button-next swiper-button-black"></div>
                            </div>
                            <?php
                            $product = wc_get_product($this->editor->item_id);
                            if (isset($this->wpc_metas['related-products']) && !empty($this->wpc_metas['related-products']) && ( $product->get_type() == 'variation' )) {
                                ?>
                                <div class="wpc-tools-wrap" data-tools-head="related-products-panel">

                                    <div id="related-products-panel-content" class="wpc-tools-content">
                                        <div  class="wpc-panel-head">
                                            <div class="wpc-panel-title"><?php _e('Pick a product', 'wpd'); ?> </div>
                                            <span id="related-products-panel-close" class="wpc-tools-close ti-close"></span>
                                        </div>
                                        <div class="wpd-tools-content-scroll-wrap">
                                            <?php
                                            $related_attributes = $this->wpc_metas['related-products'];
                                            $variation = wc_get_product($this->editor->item_id);
                                            $edit_mode_indic = '';
                                            $variation_to_load_tab = wpd_get_variation_from_attributes(wc_get_product($this->editor->item_id)->get_parent_id());
                                            foreach ($variation_to_load_tab as $variation_to_load) {
                                                $variation = wc_get_product($variation_to_load);

                                                $img_id = $variation->get_image_id();
                                                if ($img_id) {
                                                    $glimpse = "<div class='wpd-product-img' style='background-image:url(" . wp_get_attachment_url($img_id) . ")'></div>";
                                                } else {
                                                    $glimpse = $label;
                                                }

                                                $design_index = false;
                                                if (isset($wp_query->query_vars['design_index'])) {
                                                    $design_index = $wp_query->query_vars['design_index'];
                                                }

                                                $cart_item_key = false;
                                                if (isset($wp_query->query_vars['edit'])) {
                                                    $cart_item_key = $wp_query->query_vars['edit'];
                                                    $edit_mode_indic = 'cart-item-edit';
                                                }

                                                $order_item_id = false;
                                                if (isset($wp_query->query_vars['oid'])) {
                                                    $order_item_id = $wp_query->query_vars['oid'];
                                                }

                                                $tpl_id = false;
                                                if (isset($wp_query->query_vars['tpl'])) {
                                                    $tpl_id = $wp_query->query_vars['tpl'];
                                                }

                                                $wpd_product = new WPD_Product($variation_to_load);
                                                $design_url = $wpd_product->get_design_url($design_index, $cart_item_key, $order_item_id, $tpl_id);
                                                $selected_class = ( $variation_to_load == $this->editor->item_id ) ? 'selected' : '';

                                                $wpd_variation_to_load = new WPD_Product($variation_to_load);

                                                $variation_to_load_attributes = $variation->get_variation_attributes();
                                                $attribute_str = '';

                                                foreach ($variation_to_load_attributes as $variation_to_load_attribute_key => $variation_to_load_attribute) {
                                                    if (in_array($variation_to_load_attribute_key, $related_attributes)) {
                                                        if (!empty($attribute_str) && '' !== $variation_to_load_attribute) {
                                                            $attribute_str .= '+';
                                                        }
                                                        $attribute_str .= $variation_to_load_attribute;
                                                    }
                                                }
                                                echo '<div class="wpd-product-box">';
                                                echo '<div class="wpd-product-label">' . $attribute_str . ' </div>';
                                                ?>
                                                <a class="wpd-rp-attribute <?php echo $selected_class . ' ' . $edit_mode_indic; ?>" href="<?php echo $design_url; ?>"  data-desc="<?php echo $wpd_variation_to_load->get_related_product_desc(); ?>"><?php echo $glimpse; ?></a>
                                                <?php
                                                echo '</div>';
                                            }
                                            ?>
                                            <div id="wpd-rp-desc">
                                                <?php echo $this->editor->wpd_product->get_related_product_desc(); ?>
                                            </div>
                                        </div>
                                    </div>       
                                </div>
                                <?php
                            }
                            ?>
                            <?php
                            $colors_options = $wpd_settings['wpc-colors-options'];
                        ?>
                            <div class="wpc-tools-wrap" data-tools-head="text-panel">
                                <div id="text-panel-content" class="wpc-tools-content ">
                                    <?php $this->get_text_tools($text_options, $colors_options); ?>

                                </div>
                            </div>

                        
                        
                            <div class="wpc-tools-wrap" data-tools-head="cliparts-panel">

                                <div id="cliparts-panel-content" class="wpc-tools-content">
                                    <div id="wpd-clipart-group-container" class="wpd-group-parent-child-wrap">
                                        <div  class="wpc-panel-head">
                                            <div class="wpc-panel-title"><?php _e('Cliparts', 'wpd'); ?></div>
                                            <span id="cliparts-panel-close" class="wpc-tools-close ti-close"></span>
                                            <!--                                        <div class="wpd-parent-wrap-close icon-557"></div>-->
                                            <div class="wpd-upload-search-wrap">
                                                <span class="icon-464"></span>
                                                <input type="text" id="wpd-cliparts-group-search" placeholder="Search">
                                            </div>
                                        </div>

                                        <div id="wpd-cliparts-groups-wrapper" class="wpd-tools-content-scroll-wrap ">
                                            <ul class="" id="wpd-search-cliparts-group-results"></ul>

                                            <?php
                                            echo $this->get_images_tools($cliparts_options);
                                            ?>


                                        </div>


                                    </div>
                        
                                    <div class="wpc-tools-wrap" data-tools-head="uploads-panel">
                                        <div id="uploads-panel-content" class="wpc-tools-content">
                                            <?php echo $this->get_uploads_tools($uploads_options); ?>
                                        </div>
                                    </div>
                         
                    
                                    <div class="wpc-tools-wrap" data-tools-head="shapes-panel">
                                        <div id="shapes-panel-content" class="wpc-tools-content">
                                            <div>
                                                <div  class="wpc-panel-head">
                                                    <div class="wpc-panel-title"><?php _e('Shapes', 'wpd'); ?></div>
                                                    <span id="" class="wpc-tools-close ti-close icon-883"></span>

                                                </div>
                                                <div>
                                                    <?php $this->get_shapes_tools($shapes_options, $colors_options); ?>
                                                </div>
                                            </div>
                                            <div class="wpd-shapes-editing-wrap">
                                                <?php $this->get_shapes_tools_edit($shapes_options, $colors_options); ?>
                                            </div>

                                        </div>
                                    </div>
                                   
                                    <div class="wpc-tools-wrap" data-tools-head="add-cart-panel">
                                        <div id="add-cart-panel-content" class="wpc-tools-content">
                                            <div  class="wpc-panel-head">
                                                <div class="wpc-panel-title"><?php _e('Cart', 'wpd'); ?></div>                                               
                                                <span id="add-cart-panel-close" class="wpc-tools-close ti-close"></span>
                                            </div>
                                            <div id="" class="">   
                                                <?php
                                                if (!is_admin()) {
                                                    $this->get_cart_actions_box();
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wpc-tools-wrap">

                                        <div id="wpd-filters-tools-wrap" class="wpc-tools-content">
                                            <?php echo $this->get_filters_tools($uploads_options, $cliparts_options); ?>
                                        </div>
                                    </div>
                                
                            
                                
                    
                                                                           
                                </div>
                            </div>
            
            
                        </div>
                    </div>
            
        
        
        
                    <!--New End-->

        
                    <?php
                    $output = ob_get_clean();
                    return $output;
                }

                private function get_toolbar() {
                    global $wpd_settings;
                    $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
                    $toolbar_options = get_proper_value($wpd_settings, 'wpc-toolbar-options', array());

                    $options_array = array(
                        'grid-btn' => 'grid',
                        'clear_all_btn' => 'clear',
                        'delete_btn' => 'delete',
                        'copy_paste_btn' => 'duplicate',
                        'send_to_back_btn' => 'send-to-back',
                        'bring_to_front_btn' => 'bring-to-front',
                        'flip_v_btn' => 'flipV',
                        'flip_h_btn' => 'flipH',
                        'align_h_btn' => 'centerH',
                        'align_v_btn' => 'centerV',
                        'undo-btn' => 'undo',
                        'redo-btn' => 'redo',
                        'wpc-buttons-bar > span' => 'toolbar',
                    );
                    $toolbar_icons['toolbar'] = array(
                        'grid-btn',
                        'clear_all_btn',
                        'delete_btn',
                        'copy_paste_btn',
                        'send_to_back_btn',
                        'bring_to_front_btn',
                        'flip_v_btn',
                        'flip_h_btn',
                        'align_h_btn',
                        'undo-btn',
                        'redo-btn',
                    );

                    $attribut_value_array['background-size'] = '30px';
                    $attribut_value_array['background-position'] = 'center';

                    foreach ($options_array as $id => $field_name) {
                        wpd_generate_css_tab($ui_options, $id, '', "$field_name", '', '', $toolbar_icons);
                        wpd_generate_css_tab($ui_options, $id, $field_name, '', $attribut_value_array);
                        
                    }
                    ?>
                    <div id="wpc-buttons-bar">
            
                        <span id="grid-btn"></span>
            
                        <span id="clear_all_btn" ></span>
            
                        <span id="delete_btn" ></span>
            
                        <span id="copy_paste_btn" ></span>
            
                        <span id="send_to_back_btn" ></span>
            
                        <span id="bring_to_front_btn" ></span>
            
                        <span id="flip_h_btn" ></span>
            
                        <span id="flip_v_btn"></span>
            
                        <span id="align_h_btn"></span>
            
                        <span id="align_v_btn" ></span>
           
                        <span id="undo-btn" ></span>
                        <span id="redo-btn" ></span>
           
                    </div>
                    <?php
                }

                private function get_text_tools($text_options) {
                    global $wpd_settings;
                    $setting_text = get_proper_value($wpd_settings, 'wpc-texts-options', array());
                    $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
                    $default_text_color = get_proper_value($ui_options, 'default-text-color');
                    $default_text_bg_color = get_proper_value($ui_options, 'default-background-color');
                    $default_outline_bg_color = get_proper_value($ui_options, 'default-outline-background-color');

                    wpd_generate_css_tab($ui_options, 'wpc-add-text', '', 'buttons');

                    $options_array = array(
                        'font-family',
                        'font-size',
                        'bold',
                        'italic',
                        'text-color',
                        'background-color',
                        'outline-width',
                        'outline',
                        'curved',
                        'text-radius',
                        'text-spacing',
                        'opacity',
                        'text-alignment',
                        'underline',
                        'text-strikethrough',
                        'text-overline',
                    );

                    foreach ($options_array as $option) {
                        $text_components[$option] = get_proper_value($text_options, $option, 'yes');
                    }

                    $fonts = get_option('wpc-fonts');
                    if (empty($fonts)) {
                        $fonts = wpd_get_default_fonts();
                    }
                    ?>
                    <div class="wpc-panel-head">
                        <div class="wpc-panel-title"><?php _e('Text', 'wpd'); ?></div>
                        <span id="text-panel-close" class="wpc-tools-close ti-close"></span>
                        <div class="wpc-text-area-wrap">
                            <textarea id="new-text" placeholder="Type something..." class="text-element-border text-container "></textarea>
                            <button id="wpc-add-text" class="wpc-btn-effect"><?php _e('Add', 'wpd'); ?></button>
                            <button id="wpc-edit-text" class="wpc-btn-effect wpd-hidden"><?php _e('Edit', 'wpd'); ?></button>
                        </div>
                        <ul id="wpc-text-tabs" class="wpc-tabs wpd-hidden">
                            <li class="wpc-tab-link current" data-tab="tab-formatting"><?php _e('Formatting', 'wpd'); ?></li>
               
                            <li class="wpc-tab-link" data-tab="tab-font"><?php _e('Font Family', 'wpd'); ?></li>
                
                        </ul>   
                    </div>
                    <div id="text-tool-container" class="text-tool-container dspl-table wpd-hidden wpd-tools-content-scroll-wrap">
                        <div id="tab-formatting" class="wpc-tab-content current">
                            <div class="wpc-tab-content-wrapper">
                                <div class="">
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Size', 'wpd'); ?></span>
                                        <?php
                                        $options = array();
                                        $max_filtered_size = apply_filters('wpd-max-font-size', 30);
                                        $min_filtered_size = apply_filters('wpd-min-font-size', 8);
                                        $selected_filtered_size = apply_filters('wpd-default-font-size', 30);

                                        $default_size = intval(get_proper_value($setting_text, 'default-font-size', $selected_filtered_size));
                                        $min_size = intval(get_proper_value($setting_text, 'min-font-size', $min_filtered_size));
                                        $max_size = intval(get_proper_value($setting_text, 'max-font-size', $max_filtered_size));

                                        for ($i = $min_size; $i <= $max_size; $i++) {
                                            $options[$i] = $i;
                                        }
                                        ?> 
                                        <div>
                                            <div class="wpd-number-input">
                                                <button  class="minus" ></button>
                                                <input class="quantity" min="<?php echo $min_size; ?>" step="1" name="font-size-selector" id="font-size-selector" value="<?php echo $default_size; ?>" max="<?php echo $max_size; ?>" type="number">
                                                <button  class="plus"></button>
                                            </div>
                                        </div>
                                    </div>
                      
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Color', 'wpd'); ?></span>
                                        <span id="txt-color-selector" class="icon-1392 wpd-color-icon" ><span class="wpd-color-view" style="background-color: <?php echo $default_text_color; ?>;" ></span></span>
                                    </div>
                                        <span style="display: none;" id="txt-color-selector" class="icon-1392 wpd-color-icon" ><span class="wpd-color-view" style="background-color: <?php echo $default_text_color; ?>;" ></span></span>
                                    <div id="wpd-text-align-row" class="wpd-tool-row wpd-hide-row">
                                        <span><?php _e('Alignment', 'wpd'); ?></span>
                                        <div class="wpc-text-alignment">
                                            <input type="radio" id="txt-align-left" name="radio" class="txt-align" value="left">
                                            <label for="txt-align-left"><span class="icon-1342"></span></label>
                                            <input type="radio" id="txt-align-center" name="radio" class="txt-align" value="center">
                                            <label for="txt-align-center"><span class="icon-1343"></span></label>
                                            <input type="radio" id="txt-align-right" name="radio" class="txt-align" value="right">
                                            <label for="txt-align-right"><span class="icon-1270"></span></label>
                                            <input type="radio" id="txt-align-justify" name="radio" class="txt-align" value="justify">
                                            <label for="txt-align-justify"><span class="icon-1269"></span></label>
                                        </div>
                                    </div>
                            
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Style', 'wpd'); ?></span>
                                        <div class="wpc-text-style">
                                    
                                            <input type="checkbox" id="bold-cb" class="custom-cb">
                                            <label  for="bold-cb" ><span class="icon-wpd_icons-03"></span></label>
                                    
                                            <input type="checkbox" id="italic-cb" class="custom-cb">
                                            <label  for="italic-cb"><span class="icon-wpd_icons-04"></span></label>
                                    
                                        </div>
                                    </div>
                        
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Decoration', 'wpd'); ?></span>
                                        <div class="wpc-text-decoration">
                               
                                            <input type="radio" id="underline-cb" name="txt-decoration" class="txt-decoration" value="underline">
                                            <label for="underline-cb"><span class="icon-wpd_icons-01"></span></label>
                                
                                            <input type="radio" id="strikethrough-cb" name="txt-decoration" class="txt-decoration" value="line-through">
                                            <label for="strikethrough-cb" ><span class="icon-wpd_icons-06"></span></label>
                                
                                            <input type="radio" id="overline-cb" name="txt-decoration" class="txt-decoration" value="overline">
                                            <label for="overline-cb"><span class="icon-wpd_icons-05"></span></label>
                                
                                            <input type="radio" id="txt-none-cb" name="txt-decoration" class="txt-decoration" value="none">
                                            <label for="txt-none-cb"><span class="icon-wpd_icons-07"></span></label>
                                        </div>
                                    </div>
                           
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Background color', 'wpd'); ?></span>                            
                                        <span id="txt-bg-color-selector" class="bg-color-selector icon-1392 wpd-color-icon" ><span style="background-color: <?php echo $default_text_bg_color; ?>;" class="wpd-color-view"></span></span>
                                    </div>
                            
                                    <div class="wpd-tool-row">
                                        <span><?php _e('Opacity', 'wpd'); ?></span>
                                        <div>
                                            <div class="wpd-number-input">
                                                <button class="minus" ></button>
                                                <input class="quantity" name="opacity"  id="opacity-slider" data-opacity="true" step="0.1" min="0" max="1" value="1" type="number">
                                                <button  class="plus"></button>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <div class="wpc-text-curved-wrap">
                                        <span class="wpc-text-curved-label">
                                            <span><?php _e('Curved', 'wpd'); ?></span>
                                            <label class="wpc-switch" for="cb-curved" id="cb-curved-label">
                                                <input type="checkbox" id="cb-curved" class="custom-cb checkmark"> 
                                                <span class="wpc-slider round"></span>
                                            </label>
                                        </span> 
                                        <div class="wpc-text-curved-content wpd-tool-row">
                                            <label for="radius" class="radius-label "><?php _e('Radius', 'wpd'); ?></label>
                                            <div>
                                                <div class="wpd-number-input">
                                                    <button class="minus" ></button>
                                                    <input name="radius" id="curved-txt-radius-slider" step="10" min="0" max="300" value="150" type="number">
                                                    <button class="plus"></button>
                                                </div>
                                            </div>                  

                                        </div>
                                        <div class="wpc-text-curved-content wpd-tool-row">
                                            <label for="spacing" class="spacing-label "><?php _e('Spacing', 'wpd'); ?></label>
                                            <div>
                                                <div class="wpd-number-input">
                                                    <button class="minus" ></button>
                                                    <input step="1" name="spacing" id="curved-txt-spacing-slider" min="0" max="20" value="9" type="number">
                                                    <button  class="plus"></button>
                                                </div>
                                            </div>      

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
             
                
                        <div id="tab-font" class="wpc-tab-content">
                            <div class="wpc-tab-content-wrapper">
                                <div  class="wpd-font-group-wrap">
                                    <div class="font-selector-container ">
                                        <div id="font-family-selector" class="text-element-border">
                                            <?php
                                            $preload_div = '';
                                            if (isset($this->wpc_metas['use-global-fonts']) && 'no' === $this->wpc_metas['use-global-fonts'] && isset($this->wpc_metas['select-fonts']) && !empty($this->wpc_metas['select-fonts'])) {
                                                $fonts = $this->wpc_metas['select-fonts'];

                                                foreach ($fonts as $font) {
                                                    $font_label = json_decode($font)[0];
                                                    ?>
                                                    <div style="font-family: <?php echo $font_label; ?>">
                                                        <div class="wpd-font-view">Hello</div>
                                                        <div class="wpd-font-name"><?php echo $font_label; ?></div>
                                                    </div>
                                                    <?php
                                                    $preload_div .= "<span style='font-family: $font_label;'>.</span>";
                                                }
                                        
                                            } else {
                                                foreach ($fonts as $font) {
                                                    $font_label = $font[0];
                                                    ?>
                                                    <div style="font-family: <?php echo $font_label; ?>">
                                                        <div class="wpd-font-view">Hello</div>
                                                        <div class="wpd-font-name"><?php echo $font_label; ?></div>
                                                    </div>
                                                    <?php
                                                    $preload_div .= "<span style='font-family: $font_label;'>.</span>";
                                                }
                                            }
                                            ?>

                                        </div>
                                    </div>
                                </div>
                                <?php echo "<div id='wpd-fonts-preloader'>".$preload_div."</div>";?>
                            </div>
                        </div>
            
                    </div> 
        
        
        

                    <?php
                }

                private function get_shapes_tools_edit($shapes_options, $colors_options) {
                    global $wpd_settings;

                    $options_array = array('background-color', 'outline-width', 'outline', 'opacity', 'square', 'r-square', 'circle', 'triangle', 'heart', 'polygon', 'star');
                    $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
                    $default_shape_color = get_proper_value($ui_options, 'default-shape-background-color');
                    $default_shape_outline_bg_color = get_proper_value($ui_options, 'default-shape-outline-background-color');
                    foreach ($options_array as $option) {
                        $shapes_components[$option] = get_proper_value($shapes_options, $option, 'yes');
                    }
                    ?>
                    <div id="wpd-shapes-filters-wrap" class="">
                        <div  class="wpc-panel-head">
                            <div class="wpc-panel-title"><?php _e('Edit shape', 'wpd'); ?></div>
                            <div class="wpd-shape-edit-close icon-557"></div>
                            <span id="" class="wpc-tools-close ti-close icon-883"></span>
                        </div>
                        <div>
               
                    
                            <div class="wpd-tool-row">
                                <span class="text-label"><?php _e('Background', 'wpd'); ?></span>
                                <span id="shape-color-selector" class="bg-color-selector icon-1392 wpd-color-icon" ><span style="background-color: <?php echo $default_shape_color; ?>;" class="wpd-color-view"></span></span>
                            </div>
                       
                            <div class="wpd-tool-row">
                                <span class="text-label"><?php _e('Outline width', 'wpd'); ?></span>
                                <div>  
                                    <div class="wpd-number-input">
                                        <button class="minus" ></button>
                                        <input class="quantity" name="shape-thickness-slider"  id="shape-thickness-slider" data-opacity="false" step="1" min="0" max="10" value="0" type="number">
                                        <button  class="plus"></button>
                                    </div> 
                                </div>
                            </div>
                    
                            <div class="wpd-tool-row">
                                <label class=" color-label"><?php _e('Outline Color', 'wpd'); ?></label> 
                                <span id="shape-outline-color-selector" class="bg-color-selector icon-1392 wpd-color-icon" ><span style="background-color: <?php echo $default_shape_outline_bg_color; ?>;" class="wpd-color-view"></span></span>
                            </div>
                    
                            <div class="wpd-tool-row">
                                <span class="text-label"><?php _e('Opacity', 'wpd'); ?></span>
                                <div>  
                                    <div class="wpd-number-input">
                                        <button class="minus" ></button>
                                        <input class="quantity" name="shape-opacity-slider"  id="shape-opacity-slider" data-opacity="true" step="0.1" min="0" max="1" value="1" type="number">
                                        <button  class="plus"></button>
                                    </div> 
                                </div>
                            </div>
                       
                        </div>
                    </div>
                    <?php
                }

                private function get_shapes_tools($shapes_options, $colors_options) {
                    global $wpd_settings;

                    $options_array = array('background-color', 'outline-width', 'outline', 'opacity', 'square', 'r-square', 'circle', 'triangle', 'heart', 'polygon', 'star');
                    foreach ($options_array as $option) {
                        $shapes_components[$option] = get_proper_value($shapes_options, $option, 'yes');
                    }
                    ?>
                    <div class="dspl-table">
                        <div>
                            <div class="img-container shapes">
                                <span id="square-btn"></span>
                                <span id="r-square-btn"></span>
                                <span id="circle-btn"></span>
                                <span id="triangle-btn"></span>
                                <span id="heart-btn"></span>
                                <span id="polygon5" class="polygon-btn" data-num="5"></span>
                                <span id="polygon6" class="polygon-btn" data-num="6"></span>
                                <span id="polygon7" class="polygon-btn" data-num="7"></span>
                                <span id="polygon8" class="polygon-btn" data-num="8"></span>
                                <span id="polygon9" class="polygon-btn" data-num="9"></span>
                                <span id="polygon10" class="polygon-btn" data-num="10"></span>
                                <span id="star5" class="star-btn" data-num="5"></span>
                                <span id="star6" class="star-btn" data-num="6"></span>
                                <span id="star7" class="star-btn" data-num="7"></span>
                                <span id="star8" class="star-btn" data-num="8"></span>
                                <span id="star9" class="star-btn" data-num="9"></span>
                                <span id="star10" class="star-btn" data-num="10"></span>
                            </div>
                        </div>
                    </div>
                    <?php
                }

                private function get_filters_tools($options1, $options2) {
                    $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
                    foreach ($options_array as $option) {
                        $filters_settings_1[$option] = get_proper_value($options1, $option, 'yes');
                    }
                    foreach ($options_array as $option) {
                        $filters_settings_2[$option] = get_proper_value($options2, $option, 'yes');
                    }
                    ?>
                    <div class="wpd-uploads-editing-wrap">
                        <div id="wpd-uploads-filters-wrap">
                            <div  class="wpc-panel-head">
                                <div class="wpc-panel-title"><?php _e('Edit Image', 'wpd'); ?></div>
                                <span id="" class="wpc-tools-close ti-close"></span>
                            </div>
                            <div class="wpd-tools-content-scroll-wrap">
                                <div class="filter-set-container">
                                    <span class="filter-set-label"><?php _e('Filters', 'wpd'); ?></span>
                                    <div class="txt-center ">
                                        <div class="filter-set-content"><?php $this->get_image_filters(2, $options1, $options2); ?></div>
                                        <div class="clipart-bg-color-container"></div>
                                    </div>
                                </div>
                                <div class="wpd-tool-row pad-0-10">
                                    <span ><?php _e('Opacity', 'wpd'); ?></span>
                                    <div>
                                        <div class="wpd-number-input">
                                            <button class="minus" ></button>
                                            <input class="quantity" name="img-opacity-slider"  id="img-opacity-slider" data-opacity="true" step="0.1" min="0" max="1" value="1" type="number">
                                            <button  class="plus"></button>
                                        </div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }

                private function get_uploads_tools($options) {
                    global $wpd_settings;
                    $wpd_settings = apply_filters('wpd_global_settings', $wpd_settings);
                    if (isset($options['wpc-uploader'])) {
                        $uploader = $options['wpc-uploader'];
                    }

                    $form_class = 'custom-uploader';
                    if ($uploader == 'native') {
                        $form_class = 'native-uploader';
                    }
                    if (!is_admin()) {
                        ?>
                        <div  class="wpc-panel-head">
                            <div class="wpc-panel-title"> <?php _e('Uploads', 'wpd'); ?></div>                                             
                            <span id="uploads-panel-close" class="wpc-tools-close ti-close"></span>
                            <ul id="wpc-uploads-tabs" class="wpc-tabs">
                                <li class="wpc-tab-link current" data-tab="tab-upload">Computer</li>
                            </ul>
                        </div>
                        <div id="tab-upload" class="wpc-tab-content current">
                            <div class="wpc-tab-content-wrapper">
                                <div>
                                    <form id="userfile_upload_form" class="<?php echo $form_class; ?>" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpc-picture-upload-nonce'); ?>">
                                        <input type="hidden" name="action" value="handle_picture_upload">
                                        <?php
                                        if ($uploader == 'native') {
                                            ?>
                                            <input type="file" name="userfile" id="userfile">
                                            <?php
                                        } else {
                                            ?>
                                            <div id="drop" class="drop">
                                                <div class="wpc-uploader">
                                                    <?php _e('Add new file:', 'wpd'); ?>
                                                    <a>
                                                        <?php _e('Upload a new image', 'wpd'); ?>
                                                        <label for="userfile"></label>
                                                        <input type="file" name="userfile" id="userfile">
                                                    </a>
                                                </div>
                                                <div class="acd-upload-info"></div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </form>
                                    <div id="acd-uploaded-img" class="img-container wpd-tools-content-scroll-wrap"></div>
                                </div>
                            </div>
                        </div> 
                        <?php
                    } else {
                        echo "<span class='filter-set-label' style='display: inline-block;'></span><a id='wpc-add-img' class='button' style='margin-bottom: 10px;'>" . __('Add image', 'wpd') . '</a>';
                    }
                    $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
                    foreach ($options_array as $option) {
                        $filters_settings[$option] = get_proper_value($options, $option, 'yes');
                    }
                }

                private function get_image_filters($index, $options1, $options2) {
                    $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
                    foreach ($options_array as $option) {
                        $filters_settings_1[$option] = get_proper_value($options1, $option, 'yes');
                    }

                    foreach ($options_array as $option) {
                        $filters_settings_2[$option] = get_proper_value($options2, $option, 'yes');
                    }
                    ?>

                    <input type="checkbox" id="grayscale-<?php echo $index; ?>"  class="custom-cb filter-cb acd-grayscale">
                    <label for="grayscale-<?php echo $index; ?>"><?php _e('Grayscale', 'wpd'); ?></label>
                    <input type="checkbox" id="invert-<?php echo $index; ?>" class="custom-cb filter-cb acd-invert">
                    <label for="invert-<?php echo $index; ?>"><?php _e('Invert', 'wpd'); ?></label>
                    <input type="checkbox" id="sepia-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia">
                    <label for="sepia-<?php echo $index; ?>"><?php _e('Sepia 1', 'wpd'); ?></label>
                    <input type="checkbox" id="sepia2-<?php echo $index; ?>" class="custom-cb filter-cb acd-sepia2">
                    <label for="sepia2-<?php echo $index; ?>"><?php _e('Sepia 2', 'wpd'); ?></label>
                    <input type="checkbox" id="blur-<?php echo $index; ?>" class="custom-cb filter-cb acd-blur">
                    <label for="blur-<?php echo $index; ?>"><?php _e('Blur', 'wpd'); ?></label>
                    <input type="checkbox" id="sharpen-<?php echo $index; ?>" class="custom-cb filter-cb acd-sharpen">
                    <label for="sharpen-<?php echo $index; ?>"><?php _e('Sharpen', 'wpd'); ?></label>
                    <input type="checkbox" id="emboss-<?php echo $index; ?>" class="custom-cb filter-cb acd-emboss">
                    <label for="emboss-<?php echo $index; ?>"><?php _e('Emboss', 'wpd'); ?></label>
                    <?php
                }
                
                
           
                function get_images_tools($options) {
                   
                    
                    $use_lazy_load = get_proper_value($options, 'lazy', 'yes');
                    if ($use_lazy_load == 'yes') {
                        $clipart_class = 'o-lazy';
                        $src_attr = 'data-original';
                    } else {
                        $clipart_class = '';
                        $src_attr = 'src';
                    }
                    $groups = '<ul class="wpd-cliparts-groups">';
                    $cliparts_output = '';
                    $args = array(
                        'numberposts' => -1,
                        'post_type' => 'wpc-cliparts',
                    );
                    $first_display = "style='display: block'";
                    $cliparts_groups = get_posts($args);

                    if (isset($this->wpd_metas['global-cliparts']) && 'no' === $this->wpd_metas['global-cliparts']['use-global-cliparts'] && isset($this->wpd_metas['global-cliparts']['selected-cliparts']) && !empty($this->wpd_metas['global-cliparts']['selected-cliparts'])) {
                        $cliparts_groups_id = $this->wpd_metas['global-cliparts']['selected-cliparts'];
                        $cliparts_groups = array();
                        foreach ($cliparts_groups_id as $cliparts_group_id) {
                            array_push($cliparts_groups, get_post($cliparts_group_id));
                        }
                    }

                    foreach ($cliparts_groups as $cliparts_group) {
                        $cliparts = get_post_meta($cliparts_group->ID, 'wpd-cliparts-data', true);
                        if (!empty($cliparts)) {
                            $groups .= '<li data-group-name="' . $cliparts_group->post_title . '" data-groupid="' . $cliparts_group->ID . '">' . $cliparts_group->post_title . '</li>';
                            $first_display = '';
                        }
                    }
                    $groups .= '</ul>';
                    $output = $groups;
                    $output .= '</div></div>';
                    $output .= '<div id="cliparts-panel-content-child" class="wpc-tools-content-child wpd-group-child-wrap">';
                    $output .= '<div  class="wpc-panel-head">';
                    $output .= '<span class="wpc-panel-title" id="wpd-clipart-group-selected"></span><span id="" class="wpc-tools-close ti-close"></span>';
                    $output .= '<div class="wpd-child-wrap-close icon-557"></div>';
                    $output .= '<div class="wpd-upload-search-wrap"><span class="icon-464"></span><input type="text" id="wpd-cliparts-search" placeholder="Search"></div>';
                    $output .= '</div>';
                    $output .= '<div id="tab-clipart-upload-child" class="wpd-tools-content-scroll-wrap"><div class="" id="wpd-search-cliparts-results"></div>';
                    $output .= '<div id="wpd-cliparts-wrapper" class=""><div class="" id="wpd-search-cliparts-results"></div>';
                    $output .= '<div class="" id="wpd-all-cliparts">';

                    foreach ($cliparts_groups as $cliparts_group) {
                        $cliparts = get_post_meta($cliparts_group->ID, 'wpd-cliparts-data', true);
                        if (!empty($cliparts)) {
                            foreach ($cliparts as $clipart) {
                                $attachment_url = o_get_proper_image_url($clipart['id']);
                                $name = get_proper_value($clipart, 'name', 0);
                                $price = get_proper_value($clipart, 'price', 0);
                                $custom_attributes = apply_filters('wpd_cliparts_attributes', array(), $clipart['id'], $cliparts_group);
                                $custom_attributes = wpd_build_attributes_from_array($custom_attributes);
                                if (empty($price)) {
                                    $price = 0;
                                }

                                $cliparts_output .= '<div class="wpd-cliparts-container" data-groupid="' . $cliparts_group->ID . '">';
                                $cliparts_output .= ' <span class="clipart-img" style="background-image:URL(' . $attachment_url . ')" data-img-name="' . $name . '" data-price="' . $price . '" data-original="' . $attachment_url . '" data-url="' . $attachment_url . '" data-src="' . $attachment_url . '">';
                                $cliparts_output .= '</span></div>';
                            
                                
                                }
                        }
                    }
                    $output .= $cliparts_output;
                    $output .= '</div></div></div></div>';
                    return apply_filters('wpd_cliparts_output', $output, $groups, $cliparts_groups, $options);
                }

                private function get_parts() {
                    $parts = $this->wpc_metas['parts'];

                    $is_first = true;
                    ?>
                    <div id="product-part-container">
                        <ul id="wpc-parts-bar">
                            <?php
                            $pricing_rules = array();
                            if (isset($this->wpc_metas['pricing-rules'])) {
                                $pricing_rules = $this->wpc_metas['pricing-rules'];
                            }

                            foreach ($parts as $part_data) {
                                $part_key = sanitize_title($part_data['name']);

                                $icon = get_proper_value($part_data, 'icon');
                                $bg_included_id = get_proper_value($part_data, 'bg-inc');
                                $bg_not_included_id = get_proper_value($part_data, 'bg-not-inc');
                                $part_ov_img = get_proper_value($part_data, 'ov-img');
                                $overlay_included = get_proper_value($part_data, 'ov-inc', '-1');
                                $class = '';
                                if ($is_first) {
                                    $class = "class='active'";
                                }
                                $is_first = false;
                                $img_ov_src = '';

                                if (isset($part_ov_img)) {
                                    $img_ov_src = o_get_proper_image_url($part_ov_img);
                                }

                                $bg_not_included_src = '';
                                if (!empty($bg_not_included_id)) {
                                    $bg_not_included_src = o_get_proper_image_url($bg_not_included_id);
                                }

                                $bg_included_src = '';
                                if (!empty($bg_included_id)) {
                                    $bg_included_src = o_get_proper_image_url($bg_included_id);
                                }

                                $part_img = $part_data['name'];
                                if (!$icon) {
                                    $part_img = $part_data['name'];
                                } else {
                                    $icon_src = o_get_proper_image_url($icon);
                                    if ($icon_src) {
                                        $part_img = '<img src="' . $icon_src . '">';
                                    }
                                }
                                $part_price = 0;
                                foreach ($pricing_rules as $key => $pricing_rule) {
                                    foreach ($pricing_rule['rules'] as $key1 => $rules) {
                                        if ($part_key === sanitize_title($rules['value'])) {
                                            $part_price = $pricing_rule['a_price'];
                                        }
                                    }
                                }
                                ?>
                                <li data-id="<?php echo $part_key; ?>" data-url="<?php echo $bg_not_included_src; ?>" data-bg="<?php echo $bg_included_src; ?>" <?php echo $class; ?> data-placement="top" data-tooltip-title="<?php echo $part_data['name']; ?> <?php echo ( $part_price > 0 ) ? '(' . get_woocommerce_currency_symbol() . '' . $part_price . ')' : ''; ?>" data-ov="<?php echo $img_ov_src; ?>" data-ovni="<?php echo $overlay_included; ?>">
                                    <?php echo $part_img; ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }

                private function get_design_actions_box() {
                    global $wpd_settings;
                    $general_options = $wpd_settings['wpc-general-options'];
                    $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
                    wpd_generate_css_tab($ui_options, 'wpc-action-title', '', 'separators');
                    wpd_generate_css_tab($ui_options, 'preview-btn', 'icon', 'preview-btn');
                    wpd_generate_css_tab($ui_options, 'download-btn', 'icon', 'download-btn');
                    wpd_generate_css_tab($ui_options, 'save-btn', 'icon', 'save-btn');

                    if (isset($general_options['wpc-download-btn'])) {
                        $download_btn = $general_options['wpc-download-btn'];
                    }
                    if (isset($general_options['wpc-preview-btn'])) {
                        $preview_btn = $general_options['wpc-preview-btn'];
                    }
                    if (isset($general_options['wpc-save-btn'])) {
                        $save_btn = $general_options['wpc-save-btn'];
                    }

                    $design_index = -1;
                    if (isset($_GET['design_index'])) {
                        $design_index = $_GET['design_index'];
                    }
                    // We don't show the box at all if there is nothing to show inside
                    if (isset($preview_btn) && $preview_btn === '0' && isset($download_btn) && $download_btn === '0' && isset($save_btn) && $save_btn === '0') {
                        return;
                    }

                    if (isset($preview_btn) && $preview_btn !== '0') {
                        ?>
                        <span id="preview-btn" class="icon-491" data-placement="top" data-tooltip-title="<?php _e('Double click to preview design', 'wpd'); ?>"></span>                         
                        <?php
                    }
                    if (!is_admin()) {
                        if (isset($download_btn) && $download_btn !== '0') {
                            ?>
                            <span id="download-btn" class="icon-1166" data-placement="top" data-tooltip-title="<?php _e('Double click to download design', 'wpd'); ?>"></span>
                            <?php
                        }
                        if (isset($save_btn) && $save_btn !== '0') {
                            $btn_save = "<span id='save-btn' class='icon-612' data-placement='top' data-tooltip-title='Double click to save design'  data-index='" . $design_index . "'></span>";
                            echo apply_filters('wpd_get_design_custom_name', $btn_save, $design_index);
                        }
                    }

                    $modal = '<div class="omodal fade o-modal wpd-modal" id="wpd-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="omodal-dialog">
              <div class="omodal-content">
                <div class="omodal-header">
                  <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                  <h4 class="omodal-title" id="myModalLabel">' . __('PREVIEW', 'wpd') . '</h4>
                </div>
                <div class="omodal-body txt-center">
                </div>
              </div>
            </div>
        </div>';
                    if (!is_admin()) {
                        array_push(wpd_retarded_actions::$code, $modal);
                        add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                    } else {
                        echo $modal;
                    }
                }

                private function get_cart_actions_box() {
                    global $wpd_settings, $wp_query;
                    $general_options = $wpd_settings['wpc-general-options'];
                    $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
                    wpd_generate_css_tab($ui_options, 'wpd-cart-title', false, 'separators');
                    wpd_generate_css_tab($ui_options, 'minus', '', 'minus-btn');
                    wpd_generate_css_tab($ui_options, 'plus', '', 'plus-btn');
                    wpd_generate_css_tab($ui_options, 'add-to-cart-btn', 'icon', 'add-to-cart-btn');
                    wpd_generate_css_tab($ui_options, 'add-to-cart-btn', false, 'ac-buttons', false, false);
                    wpd_generate_css_tab($ui_options, 'total-price', '', 'texts', '', true);
                    wpd_generate_css_tab($ui_options, 'wpd-qty.wpc-custom-right-quantity-input', '', 'texts', '', true);
                    wpd_generate_css_tab($ui_options, 'wpc-custom-right-quantity-input-set', false, 'qty-buttons', false, true);
                    if (isset($general_options['wpc-cart-btn'])) {
                        $cart_btn = $general_options['wpc-cart-btn'];
                    }

                    $product = wc_get_product($this->editor->item_id);
                    $tpl_price = 0;
                    if (isset($wp_query->query_vars['tpl'])) {
                        $tpl_id = $wp_query->query_vars['tpl'];
                        $tpl_price = wpd_get_template_price($tpl_id);
                    }

                    global $wp_query;
                    $add_to_cart_label = __('ADD TO CART', 'wpd');
                    if (isset($wp_query->query_vars['edit'])) {
                        $add_to_cart_label = __('UPDATE CART ITEM', 'wpd');
                    }
                    ?>
                    <div id="wpc-cart-box" class="">
                        <div class="wpd-tools-content-scroll-wrap">

                            <?php
                            if (isset($this->wpc_metas['related-quantities']) && !empty($this->wpc_metas['related-quantities']) && $product->get_type() == 'variation') {
                                $related_attributes = $this->wpc_metas['related-quantities'];

                                $wpd_root_product = new WPD_Product($this->editor->root_item_id);

                                $usable_attributes = $wpd_root_product->extract_usable_attributes();

                                $variation = wc_get_product($this->editor->item_id);

                                $variation_to_load_tab = wpd_get_variation_from_attributes($variation->get_parent_id());

                                $_SESSION['combinaison'] = [];
                                if (!empty($variation_to_load_tab)) {
                                    foreach ($variation_to_load_tab as $variation_to_load) {
                                        $variation_to_load_ob = wc_get_product($variation_to_load);
                                        $quantity_display = '';
                                        if ($variation_to_load_ob->is_sold_individually()) {
                                            $quantity_display = "style='display: none;'";
                                        }

                                        $wpd_variation = new WPD_Product($variation_to_load);
                                        $purchase_properties = $wpd_variation->get_purchase_properties();
                                        $selected_attributes = wc_get_product($variation_to_load)->get_variation_attributes();

                                        $price = $variation_to_load_ob->get_price() + $tpl_price;

                                        $price_html = ' <span class="total_order">' . wc_price($price * $purchase_properties['min_to_purchase']) . '</span>';
                                        $to_search = $selected_attributes;
                                        foreach ($usable_attributes as $attribute_name => $attribute_data) {
                                            $attribute_key = $attribute_data['key'];

                                            if (in_array($attribute_key, $related_attributes)) {
                                                $to_search[$attribute_key] = array();
                                                foreach ($attribute_data['values'] as $attribute_value) {



                                                    if (is_object($attribute_value)) {
                                                        $sanitized_value = $attribute_value->slug;
                                                        $label = $attribute_value->name;
                                                    } else {
                                                        $sanitized_value = sanitize_title($attribute_value);
                                                        $label = $attribute_value;
                                                    }

                                                    array_push($to_search[$attribute_key], $label);
                                                }
                                            }
                                        }
                                        $combinaisons = [];
                                        $combinaisons[] = $selected_attributes;
                                        $combine = false;
                                        foreach ($selected_attributes as $key => $value) {
                                            if ('' === $value && !empty($to_search[$key])) {
                                                $combine = true;
                                                $combinaisons = wpd_make_variation_combine($key, $to_search[$key], $combinaisons);
                                            }
                                        }

                                        // Variation properties
                                        if ($combine) {

                                            foreach ($combinaisons as $combinaison) {
                                                $array_key = array_keys($combinaison);
                                                $end = end($array_key);
                                                $key = '';
                                                foreach ($combinaison as $combinaison_key => $value) {
                                                    if ($end !== $combinaison_key) {
                                                        $key .= $value . '+';
                                                    } else {
                                                        $key .= $value;
                                                    }
                                                }
                                                $_SESSION['combinaison'][$key] = $combinaison;
                                                ?>
                                                <div class="wpc-qty-container" data-id="<?php echo $variation_to_load; ?>" <?php echo $quantity_display; ?>>
                                                    <label><?php echo $key; ?></label>
                                                    <input type="button" id="minus" value="-" class="minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                                                    <input type="number" variation_name="<?= $key ?>" step="<?php echo $purchase_properties['step']; ?>" value="<?php echo $purchase_properties['min_to_purchase']; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties['min']; ?>" max="<?php echo $purchase_properties['max']; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>"  >
                                                    <input type="button" id="plus" value="+" class="plus wpc-custom-right-quantity-input-set wpc-btn-effect">

                                                    <div class="total-price">
                                                        <?php echo $price_html; ?>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            $variation_to_load_attributes = $variation_to_load_ob->get_variation_attributes();
                                            $attribute_str = '';

                                            foreach ($variation_to_load_attributes as $variation_to_load_attribute_key => $variation_to_load_attribute) {
                                                if (in_array($variation_to_load_attribute_key, $related_attributes)) {
                                                    if (!empty($attribute_str) && '' !== $variation_to_load_attribute) {
                                                        $attribute_str .= '+';
                                                    }
                                                    $attribute_str .= $variation_to_load_attribute;
                                                }
                                            }
                                            ?>
                                            <div class="wpc-qty-container" data-id="<?php echo $variation_to_load; ?>" <?php echo $quantity_display; ?>>
                                                <label><?php echo $attribute_str; ?></label>
                                                <input type="button" id="minus" value="-" class="minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                                                <input type="number" variation_name="<?= $attribute_str ?>" step="<?php echo $purchase_properties['step']; ?>" value="<?php echo $purchase_properties['min_to_purchase']; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties['min']; ?>" max="<?php echo $purchase_properties['max']; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>"  >
                                                <input type="button" id="plus" value="+" class="plus wpc-custom-right-quantity-input-set wpc-btn-effect">

                                                <div class="total-price">
                                                    <?php echo $price_html; ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                }
                                ?>

                                <?php
                            } else {
                                $purchase_properties = $this->editor->wpd_product->get_purchase_properties();
                                $price = $product->get_price() + $tpl_price;

                                $price_html = ' <span class="total_order">' . wc_price($price * $purchase_properties['min_to_purchase']) . '</span>';
                                $custom_quantity = isset($_GET['custom_qty']) ? $_GET['custom_qty'] : $purchase_properties['min_to_purchase'];
                                $quantity_display = '';
                                if ($product->is_sold_individually()) {
                                    $quantity_display = "style='display: none;'";
                                }
                                ?>

                                <div class="wpc-qty-container" data-id="<?php echo $this->editor->item_id; ?>" <?php echo $quantity_display; ?>>
                                    <input type="button" id="minus" value="-" class="minus wpc-custom-right-quantity-input-set wpc-btn-effect">
                                    <input type="number" step="<?php echo $purchase_properties['step']; ?>" value="<?php echo $custom_quantity ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties['min']; ?>" max="<?php echo $purchase_properties['max']; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>">
                                    <input type="button" id="plus" value="+" class="plus wpc-custom-right-quantity-input-set wpc-btn-effect">

                                    <div class="total-price">
                                        <?php echo $price_html; ?>
                                    </div>
                                </div>

                                <?php
                            }
                            do_action('wpd_cart_box', $this->editor->wpd_product);
                            ?>
                        </div>
                        <button id="add-to-cart-btn" class="wpc-btn-effect" data-id="<?php echo $this->editor->item_id; ?>"><?php echo $add_to_cart_label; ?></button>
                    </div>
                    <?php
                }

                private function register_scripts() {
                    wp_enqueue_script('wpd-scrollevent', WPD_URL . 'public/js/scrollevent.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-spectrum-js', WPD_URL . 'public/js/spectrum.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-number-js', WPD_URL . 'public/js/jquery.number.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-fabric-js', WPD_URL . 'public/js/fabric.all.min_1.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-accounting-js', WPD_URL . 'public/js/accounting.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-editor-js', WPD_URL . 'public/js/editor.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-editor-text-controls', WPD_URL . 'public/js/editor.text.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-editor-toolbar-js', WPD_URL . 'public/js/editor.toolbar.js', array('jquery', 'wpd-editor-js'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-editor-shapes-js', WPD_URL . 'public/js/editor.shapes.js', array('jquery'), WPD_VERSION, false);

                    wp_enqueue_script('wpd-swiper-js', WPD_URL . 'includes/skins/porto-novo-mobile/assets/js/swiper.min.js', array('jquery'), WPD_VERSION, false);

                    wp_enqueue_script('wpd-porto-skin-js', WPD_URL . 'includes/skins/porto-novo-mobile/assets/js/porto.js', array('jquery'), WPD_VERSION, false);

                    wp_enqueue_script('wpd-mCustomScrollbar-js', WPD_URL . 'includes/skins/porto-novo-mobile/assets/js/jquery.mCustomScrollbar.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-doubleTap-js', WPD_URL . 'includes/skins/porto-novo-mobile/assets/js/jquery-doubletap.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-accordion-js', WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-block-UI-js', WPD_URL . 'public/js/blockUI/jquery.blockUI.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-lazyload-js', WPD_URL . 'public/js/jquery.lazyload.min.js', array('jquery'), WPD_VERSION, false);
                    wp_enqueue_script('wpd-editor-img-js', WPD_URL . 'public/js/editor.img.js', array('jquery', 'wpd-lazyload-js'), WPD_VERSION, false);
                    wp_enqueue_script('wp-js-hooks', WPD_URL . 'public/js/wp-js-hooks.min.js', array('jquery'), WPD_VERSION, false);

                    wpd_register_upload_scripts();
                }

                private function register_styles() {
                    wp_enqueue_style('wpd-spectrum-css', WPD_URL . 'public/css/spectrum.min.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-swiper-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/swiper.min.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-porto-skin-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/porto.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-line-icons-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/line-icons.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-icons-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/wpd_icons.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-font-awesome-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/font-awesome.min.css', array(), WPD_VERSION, 'all');
                    wp_enqueue_style('wpd-mCustomScrollbar-css', WPD_URL . 'includes/skins/porto-novo-mobile/assets/css/jquery.mCustomScrollbar.min.css', array(), WPD_VERSION, 'all');

                    wpd_register_fonts();
                }

            }
            