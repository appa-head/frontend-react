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
class WPD_Skin_Default {

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
        $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());

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

        <div class='wpc-container <?php echo $wpd_responsive_class; ?>'>
            <div class="wpd-responsive-toolbar-box">
                <?php echo $wpd_responsive_menu; ?>
                <?php $this->get_toolbar(); ?>
                <?php echo $wpd_responsive_menu2; ?>
            </div>
            <div class="wpc-editor-wrap">
                <div class="wpc-editor-col">
                    <div id="wpc-tools-box-container" class="Accordion" tabindex="0">
                        <?php
                        $colors_options = $wpd_settings['wpc-colors-options'];
                        ?>
                        <div class="AccordionPanel" id="text-panel">
                            <div id="text" class="AccordionPanelTab"><?php _e('TEXT', 'wpd'); ?></div>
                            <div class="AccordionPanelContent">
                                <?php $this->get_text_tools($text_options, $colors_options); ?>
                            </div>
                        </div>
                        <div class="AccordionPanel" id="shapes-panel">
                            <div id="shapes" class="AccordionPanelTab"><?php _e('SHAPES', 'wpd'); ?></div>
                            <div class="AccordionPanelContent">
                                <?php $this->get_shapes_tools($shapes_options, $colors_options); ?>
                            </div>
                        </div>
                        <div class="AccordionPanel" id="uploads-panel">
                            <div id="uploads" class="AccordionPanelTab"><?php _e('UPLOADS', 'wpd'); ?></div>
                            <div class="AccordionPanelContent">
                                <?php $this->get_uploads_tools($uploads_options); ?>                                 
                            </div>
                        </div>
                        <div class="AccordionPanel" id="cliparts-panel">
                            <div id="cliparts" class="AccordionPanelTab"><?php _e('CLIPARTS', 'wpd'); ?></div>
                            <div class="AccordionPanelContent" id="wpd-cliparts-tools-fake-container">
                                <?php
                                wpd_generate_css_tab($ui_options, 'wpd-cliparts-opener', '', 'buttons');
                                $modal_id = 'wpd-cliparts-modal';
                                echo '<a id="wpd-cliparts-opener" class="wpd-button" data-toggle="o-modal" data-target="#' . $modal_id . '">' . __('Browse', 'wpd') . '</a>';
                                $modal = '<div class="omodal fade o-modal wpc-modal ff-josefin" id="' . $modal_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="omodal-dialog">
                                                  <div class="omodal-content">
                                                    <div class="omodal-header">
                                                      <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                                      <h4 class="omodal-title" id="myModalLabel' . $modal_id . '">' . __('Browse cliparts', 'wpd') . '</h4>
                                                    </div>
                                                    <div class="omodal-body">
                                                        ' . $this->get_images_tools($cliparts_options) . '
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>';
                                array_push(wpd_retarded_actions::$code, $modal);
                                if (is_admin()) {
                                    add_action('admin_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                                } else {
                                    add_action('wp_footer', array('wpd_retarded_actions', 'display_code'), 10, 1);
                                }

                                $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
                                foreach ($options_array as $option) {
                                    $filters_settings[$option] = get_proper_value($cliparts_options, $option, 'yes');
                                }
                                ?>

                                <div class="filter-set-container">
                                    <span class="filter-set-label"><?php _e('Filters', 'wpd'); ?></span>
                                    <span>
                                        <div class="mg-r-element ">
                                            <?php $this->get_image_filters(1, $cliparts_options); ?>
                                            <div class="clipart-bg-color-container"></div>
                                        </div>
                                    </span>
                                </div>
                                <div>
                                    <span ><?php _e('Opacity', 'wpd'); ?></span>
                                    <span >   
                                        <?php wpd_get_opacity_dropdown('opacity', 'txt-opacity-slider', 'text-element-border text-tools-select'); ?>
                                    </span>
                                </div>
                                <?php
                                do_action('wpd_cliparts_section_end', $this->editor->wpd_product);
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="wpc-editor-col-2">
                    <div id="wpc-editor-container">
                        <canvas id="wpc-editor" ></canvas>
                        <?php echo wpd_built_with(); ?>
                    </div>

                    <div id="product-part-container" class="">
                        <?php $this->get_parts(); ?>
                    </div>
                    <?php
                    if (!is_admin()) {
                        WPD_Design::get_option_form($this->editor->root_item_id, $this->wpc_metas);
                    }
                    ?>
                    <div id="debug"></div>
                </div>
                <?php
                // We don't show the column at all if there is nothing to show inside
                $general_options = $wpd_settings['wpc-general-options'];
                if (isset($general_options['wpc-download-btn'])) {
                    $download_btn = $general_options['wpc-download-btn'];
                }

                if (isset($general_options['wpc-preview-btn'])) {
                    $preview_btn = $general_options['wpc-preview-btn'];
                }

                if (isset($general_options['wpc-cart-btn'])) {
                    $cart_btn = $general_options['wpc-cart-btn'];
                }

                if (
                        ( isset($preview_btn) && $preview_btn !== '0' ) ||
                        ( isset($download_btn) && $download_btn !== '0' ) ||
                        ( isset($cart_btn) && $cart_btn !== '0' )
                ) {
                    ?>
                    <div class=" wpc-editor-col right">
                        <?php
                        $this->get_design_actions_box();

                        if (!is_admin()) {
                            $this->get_cart_actions_box();
                        }
                        
                        ?>
                        
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
        $output = ob_get_clean();
        return $output;
    }

    private function get_toolbar() {
        global $wpd_settings;
        $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());

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
            <span id="grid-btn" data-placement="top" data-tooltip-title="<?php _e('grid', 'wpd'); ?>"></span>
            <span id="clear_all_btn" data-placement="top" data-tooltip-title="<?php _e('Clear all', 'wpd'); ?>"></span>
            <span id="delete_btn" data-placement="top" data-tooltip-title="<?php _e('Delete', 'wpd'); ?>"></span>
            <span id="copy_paste_btn" data-placement="top" data-tooltip-title="<?php _e('Duplicate', 'wpd'); ?>"></span>
            <span id="send_to_back_btn" data-placement="top" data-tooltip-title="<?php _e('Send to back', 'wpd'); ?>"></span>
            <span id="bring_to_front_btn" data-placement="top" data-tooltip-title="<?php _e('Bring to front', 'wpd'); ?>"></span>
            <span id="flip_h_btn" data-placement="top" data-tooltip-title="<?php _e('Flip horizontally', 'wpd'); ?>"></span>
            <span id="flip_v_btn" data-placement="top" data-tooltip-title="<?php _e('Flip vertically', 'wpd'); ?>"></span>
            <span id="align_h_btn" data-placement="top" data-tooltip-title="<?php _e('Center horizontally', 'wpd'); ?>"></span>
            <span id="align_v_btn" data-placement="top" data-tooltip-title="<?php _e('Center vertically', 'wpd'); ?>"></span>
            <span id="undo-btn" data-placement="top" data-tooltip-title="<?php _e('Undo', 'wpd'); ?>"></span>
            <span id="redo-btn" data-placement="top" data-tooltip-title="<?php _e('Redo', 'wpd'); ?>"></span>
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
        <div class="text-tool-container dspl-table">
            <div >
                <span class="text-label"><?php _e('Text', 'wpd'); ?></span>
                <span class="">
                    <textarea id = "new-text" class="text-element-border text-container "></textarea>
                    <button id="wpc-add-text" class="wpc-btn-effect"><?php _e('ADD', 'wpd'); ?></button>
                </span>
            </div>
            <div >
                <span ><?php _e('Font', 'wpd'); ?></span>
                <span class="font-selector-container ">
                    <select id="font-family-selector" class="text-element-border">
                        <?php
                        $preload_div = '';
                        if (isset($this->wpc_metas['use-global-fonts']) && 'no' === $this->wpc_metas['use-global-fonts'] && isset($this->wpc_metas['select-fonts']) && !empty($this->wpc_metas['select-fonts'])) {
                            $fonts = $this->wpc_metas['select-fonts'];

                            foreach ($fonts as $font) {
                                $font_label = json_decode($font)[0];
                                echo "<optgroup style='font-family:$font_label'><option>$font_label</option></optgroup>";
                                $preload_div .= "<span style='font-family: $font_label;'>.</span>";
                            }
                        } else {
                            foreach ($fonts as $font) {
                                $font_label = $font[0];
                                echo "<optgroup style='font-family:$font_label'><option>$font_label</option></optgroup>";
                                $preload_div .= "<span style='font-family: $font_label;'>.</span>";
                            }
                        }
                        ?>

                    </select>
                </span>
            </div>
            <?php
            echo "<div id='wpd-fonts-preloader'>$preload_div</div>";
            ?>
            <div >
                <span><?php _e('Size', 'wpd'); ?></span>
                <span >
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
                    echo wpd_get_html_select('font-size-selector', 'font-size-selector', 'text-element-border text-tools-select', $options, $default_size);
                    ?>
                </span>
            </div>
            <div >
                <span>
                    <?php _e('Style', 'wpd'); ?>
                </span> 
                <div class="mg-r-element ">
                    <input type="checkbox" id="bold-cb" class="custom-cb">
                    <label for="bold-cb" data-placement="top" data-tooltip-title="<?php _e('Bold', 'wpd'); ?>"></label>
                    <input type="checkbox" id="italic-cb" class="custom-cb">
                    <label for="italic-cb" data-placement="top" data-tooltip-title="<?php _e('Italic', 'wpd'); ?>"></label>
                    <span id="txt-color-selector" class=" "  data-placement="top" data-tooltip-title="<?php _e('Text color', 'wpd'); ?>" style="background-color: <?php echo $default_text_color; ?>;"></span>
                    <span id="txt-bg-color-selector" class="bg-color-selector " data-placement="top" data-tooltip-title="<?php _e('Background color', 'wpd'); ?>" style="background-color: <?php echo $default_text_bg_color; ?>;"></span>
                </div>
            </div>
            <div>
                <span ><?php _e('Outline', 'wpd'); ?>
                </span>
                <div>
                    <label  for="o-thickness-slider" class=" width-label"><?php _e('Width', 'wpd'); ?></label>
                    <?php
                    $options = array(
                        0 => "None",
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                        7,
                        8,
                        9,
                        10,
                    );
                    echo wpd_get_html_select('o-thickness-slider', 'o-thickness-slider', 'text-element-border text-tools-select', $options);
                    ?>
                    <div class="color-container">
                        <label for="color" class=" color-label"><?php _e('Color', 'wpd'); ?></label> 
                        <span id="txt-outline-color-selector" class="bg-color-selector " data-placement="top" data-tooltip-title="<?php _e('Background color', 'wpd'); ?>" style="background-color: <?php echo $default_outline_bg_color; ?>;"></span>
                    </div>
                </div>
            </div>
            <div >
                <span><?php _e('Curved', 'wpd'); ?></span>
                <div>
                    <input type="checkbox" id="cb-curved" class="custom-cb checkmark"> 
                    <label for="cb-curved" id="cb-curved-label" ></label>

                    <label for="radius" class="radius-label "><?php _e('Radius', 'wpd'); ?></label>
                    <?php
                    $options = array();
                    for ($i = 1; $i <= 20; $i++) {
                        array_push($options, $i);
                    }
                    echo wpd_get_html_select('spacing', 'curved-txt-spacing-slider', 'text-element-border text-tools-select', $options, 9);
                    ?>
                    <div class="spacing-container">
                        <label for="spacing" class="spacing-label "><?php _e('Spacing', 'wpd'); ?></label>
                        <?php
                        $options = array();
                        for ($i = 0; $i <= 30; $i++) {
                            $options[$i * 10] = $i * 10;
                        }
                        echo wpd_get_html_select('radius', 'curved-txt-radius-slider', 'text-element-border text-tools-select', $options, 150);
                        ?>
                    </div>
                </div>
            </div>
            <div>
                <span ><?php _e('Opacity', 'wpd'); ?></span>
                <span >
                    <?php
                    wpd_get_opacity_dropdown('opacity', 'opacity-slider', 'text-element-border text-tools-select');
                    ?>
                </span>
            </div>
            <div>
                <span><?php _e('Alignment', 'wpd'); ?></span>
                <div class="mg-r-element">
                    <input type="radio" id="txt-align-left" name="radio" class="txt-align" value="left"/>
                    <label for="txt-align-left" ><span></span></label>
                    <input type="radio" id="txt-align-center" name="radio" class="txt-align" value="center"/>
                    <label for="txt-align-center"><span ></span></label>
                    <input type="radio" id="txt-align-right" name="radio" class="txt-align" value="right"/>
                    <label for="txt-align-right"><span ></span></label>
                </div>
            </div>
            <div >
                <span><?php _e('Decoration', 'wpd'); ?></span>
                <div class=" mg-r-element">
                    <input type="radio" id="underline-cb" name="txt-decoration" class="txt-decoration" value="underline">
                    <label for="underline-cb" data-placement="top" data-tooltip-title="<?php _e('Underline', 'wpd'); ?>"><span></span></label>
                    <input type="radio" id="strikethrough-cb" name="txt-decoration" class="txt-decoration" value="line-through">
                    <label for="strikethrough-cb" data-placement="top" data-tooltip-title="<?php _e('Strikethrough', 'wpd'); ?>"><span></span></label>
                    <input type="radio" id="overline-cb" name="txt-decoration" class="txt-decoration" value="overline">
                    <label for="overline-cb" data-placement="top" data-tooltip-title="<?php _e('Overline', 'wpd'); ?>"><span></span></label>
                    <input type="radio" id="txt-none-cb" name="txt-decoration" class="txt-decoration" value="none">
                    <label for="txt-none-cb" data-placement="top" data-tooltip-title="<?php _e('None...', 'wpd'); ?>"><span></span></label>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_shapes_tools($shapes_options, $colors_options) {
        global $wpd_settings;

        $options_array = array('background-color', 'outline-width', 'outline', 'opacity', 'square', 'r-square', 'circle', 'triangle', 'heart', 'polygon', 'star');
        $ui_options = get_proper_value($wpd_settings, 'wpc-ui-options', array());
        $default_shape_color = get_proper_value($ui_options, 'default-shape-background-color');
        $default_shape_outline_bg_color = get_proper_value($ui_options, 'default-shape-outline-background-color');
        foreach ($options_array as $option) {
            $shapes_components[$option] = get_proper_value($shapes_options, $option, 'yes');
        }
        ?>
        <div class="dspl-table">
            <div>
                <span class="text-label"><?php _e('Background', 'wpd'); ?></span>
                <span class="">
                    <span id="shape-color-selector" class="bg-color-selector " data-placement="top" data-tooltip-title="<?php _e('Background color', 'wpd'); ?>" style="background-color: <?php echo $default_shape_color; ?>;"></span>
                </span>
            </div>
            <div>
                <span class="text-label"><?php _e('Outline', 'wpd'); ?></span>
                <span class="">
                    <?php if ($shapes_components['outline-width'] == 'yes') { ?>
                        <label class="width-label"><?php _e('Width', 'wpd'); ?></label>
                        <?php
                        $options = array(
                            0 => 'None',
                            1,
                            2,
                            3,
                            4,
                            5,
                            6,
                            7,
                            8,
                            9,
                            10,
                        );
                        echo wpd_get_html_select('shape-thickness-slider', 'shape-thickness-slider', 'text-element-border text-tools-select', $options);
                    }
                    ?>
                    <div class="color-container">
                        <label class=" color-label"><?php _e('Color', 'wpd'); ?></label> 
                        <span id="shape-outline-color-selector" class="bg-color-selector " data-placement="top" data-tooltip-title="<?php _e('Outline color', 'wpd'); ?>" style="background-color: <?php echo $default_shape_outline_bg_color; ?>;"></span>
                    </div>
                </span>
            </div>
            <div>
                <span class="text-label"><?php _e('Opacity', 'wpd'); ?></span>
                <span class="">
                    <?php
                    echo wpd_get_opacity_dropdown('shape-opacity-slider', 'shape-opacity-slider', '');
                    ?>
                </span>
            </div>
            <div>
                <span class="text-label">
                    <?php _e('Shapes', 'wpd'); ?>
                </span>
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

    private function get_uploads_tools($options) {
        if (isset($options['wpc-uploader'])) {
            $uploader = $options['wpc-uploader'];
        }
        $form_class = 'custom-uploader';
        if ($uploader == 'native') {
            $form_class = 'native-uploader';
        }
        if (!is_admin()) {
            ?>
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

                    <div class="drop">
                        <a><?php _e('Pick a file', 'wpd'); ?></a>
                        <label for="userfile"></label>
                        <input type="file" name="userfile" id="userfile"/>
                        <div class="acd-upload-info"></div>
                    </div>
                    <?php
                }
                ?>
            </form>

            <div id="acd-uploaded-img" class="img-container"></div>
            <?php
        } else {
            echo "<span class='filter-set-label' style='display: inline-block;'></span><a id='wpc-add-img' class='button' style='margin-bottom: 10px;'>" . __('Add image', 'wpd') . '</a>';
        }

        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
        }
        ?>

        <div class="filter-set-container">
            <span class="filter-set-label"><?php _e('Filters', 'wpd'); ?></span>
            <span>
                <div class="mg-r-element ">

                    <?php $this->get_image_filters(2, $options); ?>
                    <div class="clipart-bg-color-container"></div>
                </div>

            </span>
        </div>
        <div>
            <span ><?php _e('Opacity', 'wpd'); ?></span>
            <span >   
                <?php
                wpd_get_opacity_dropdown('img-opacity-slider', 'img-opacity-slider', 'text-element-border text-tools-select');
                ?>
            </span>
        </div>
        <?php
        do_action('wpd_uploads_section_end', $this->editor->wpd_product);
    }

    private function get_image_filters($index, $options) {
        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
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

    private function get_images_tools_old($options) {
        $cliparts_options = get_proper_value($options, 'wpc-images-options', array());
        $use_lazy_load = get_proper_value($cliparts_options, 'lazy', 'yes');
        if ($use_lazy_load == 'yes') {
            $clipart_class = 'o-lazy';
            $src_attr = 'data-original';
        } else {
            $clipart_class = '';
            $src_attr = 'src';
        }

        $opacity = get_proper_value($options, 'opacity', 'yes');

        $transient_key = 'orion_wpd_cliparts_transient';
        $cached_output = get_transient($transient_key);
        if ($cached_output) {
            echo $cached_output;
        } else {

            $output = '';
            $args = array(
                'numberposts' => -1,
                'post_type' => 'wpc-cliparts',
            );
            $cliparts_groups = get_posts($args);
            $output .= '<div id="img-cliparts-accordion" class="Accordion minimal" tabindex="0">';
            foreach ($cliparts_groups as $cliparts_group) {
                $cliparts = get_post_meta($cliparts_group->ID, 'wpc-cliparts', true);
                $cliparts_prices = get_post_meta($cliparts_group->ID, 'wpc-cliparts-prices', true);
                if (!empty($cliparts)) {
                    $output .= '<div class="AccordionPanel">
                                    <div class="AccordionPanelTab">' . $cliparts_group->post_title . ' (' . count($cliparts) . ')</div>
                                    <div class="AccordionPanelContent img-container">';

                    foreach ($cliparts as $i => $clipart_id) {
                        $attachment_url = o_get_proper_image_url($clipart_id);
                        $price = 0;
                        if (isset($cliparts_prices[$i])) {
                            $price = $cliparts_prices[$i];
                        }
                        $custom_attributes = apply_filters('wpd_cliparts_attributes', array(), $clipart_id, $cliparts_group);
                        $custom_attributes = wpd_build_attributes_from_array($custom_attributes);
                        if (empty($price)) {
                            $price = 0;
                        }
                        $output .= "<span class='clipart-img'><img class='$clipart_class' $src_attr='$attachment_url' data-price='$price' " . implode(' ', $custom_attributes) . '></span>';
                    }
                    $output .= '</div>
                            </div>';
                }
            }
            $output .= '</div>';
            set_transient($transient_key, $output, 12 * HOUR_IN_SECONDS);
            echo $output;
        }
        $options_array = array('grayscale', 'invert', 'sepia1', 'sepia2', 'blur', 'sharpen', 'emboss');
        foreach ($options_array as $option) {
            $filters_settings[$option] = get_proper_value($options, $option, 'yes');
        }
        ?>

        <div class="filter-set-container">
            <?php
            if ($filters_settings['grayscale'] == 'yes' || $filters_settings['invert'] == 'yes' || $filters_settings['sepia1'] == 'yes' || $filters_settings['sepia2'] == 'yes' || $filters_settings['blur'] == 'yes' || $filters_settings['sharpen'] == 'yes' || $filters_settings['emboss'] == 'yes') {
                ?>
                <span class="filter-set-label"><?php _e('Filters', 'wpd'); ?></span>
                <?php
            }
            ?>
            <span>
                <div class="mg-r-element ">
                    <?php $this->get_image_filters(1, $options); ?>
                    <div class="clipart-bg-color-container"></div>

                </div>

            </span>

        </div>
        <?php if ($opacity == 'yes') { ?>
            <div>
                <span ><?php _e('Opacity', 'wpd'); ?></span>
                <span >   
                    <?php wpd_get_opacity_dropdown('opacity', 'txt-opacity-slider', 'text-element-border text-tools-select'); ?>
                </span>
            </div>
        <?php } ?>
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

        $groups = "<ul class='wpd-cliparts-groups o-col xl-1-3'>";
        $cliparts_output = '';
        $args = array(
            'numberposts' => -1,
            'post_type' => 'wpc-cliparts',
        );

        $first_display = "style='display: block'";
        $cliparts_groups = get_posts($args);
        foreach ($cliparts_groups as $cliparts_group) {
            $cliparts = get_post_meta($cliparts_group->ID, 'wpd-cliparts-data', true);
            if (!empty($cliparts)) {
                $groups .= '<li data-groupid="' . $cliparts_group->ID . '">' . $cliparts_group->post_title . ' (' . count($cliparts) . ')</li>';
                $cliparts_output .= '<div class="wpd-cliparts-container" data-groupid="' . $cliparts_group->ID . '" ' . $first_display . '>';
                $first_display = '';

                foreach ($cliparts as $i => $clipart) {
                    $attachment_url = o_get_proper_image_url($clipart['id']);
                    $price = get_proper_value($clipart, 'price', 0);
                    $custom_attributes = apply_filters('wpd_cliparts_attributes', array(), $clipart['id'], $cliparts_group);
                    $custom_attributes = wpd_build_attributes_from_array($custom_attributes);
                    $cliparts_output .= "<span class='clipart-img' data-src='$attachment_url' data-tooltip-title='" . get_woocommerce_currency_symbol() . "$price' ><img class='$clipart_class' $src_attr='$attachment_url' data-price='$price' " . implode(' ', $custom_attributes) . '></span>';
                }
                $cliparts_output .= '</div>';
            }
        }
        $groups .= '</ul>';
        $output = "<div id='wpd-cliparts-wrapper' class='o-wrap'>";
        $output .= "<div class='o-col xl-1-3'><input type='text' id='wpd-cliparts-search' placeholder=" . __('Search...', 'wpd') . "></div><div class='o-col xl-2-3'></div>";
        $output .= $groups;
        $output .= "<div class='o-col xl-2-3' id='wpd-search-cliparts-results'></div>";
        $output .= "<div class='o-col xl-2-3' id='wpd-all-cliparts'>" . $cliparts_output . '</div>';
        $output .= '</div>';
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
        ?>
        <div id="wpc-design-btn-box" >
            <div class="title" id="wpc-action-title"><?php _e('ACTIONS', 'wpd'); ?></div>
            <?php
            if (isset($preview_btn) && $preview_btn !== '0') {
                ?>
                <button id="preview-btn" class="wpc-btn-effect"><?php _e('PREVIEW', 'wpd'); ?></button>
                <?php
            }
            if (!is_admin()) {
                if (isset($download_btn) && $download_btn !== '0') {
                    ?>
                    <button id="download-btn" class="wpc-btn-effect"><?php _e('DOWNLOAD', 'wpd'); ?></button>
                    <?php
                }
            }
            ?>
        </div>
        <?php
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
            <div class="title" id="wpd-cart-title"><?php _e('CART', 'wpd'); ?></div>
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
                        $custom_quantity = $purchase_properties['min_to_purchase'];
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            if ($cart_item['variation_id'] == $wpd_variation->variation_id) {
                                $qty_key = "qty_" . $cart_item_key . "_" . $wpd_variation->variation_id;
                                $custom_quantity = get_option($qty_key, $purchase_properties['min_to_purchase']);
                                break;
                            }
                        }
                        $price_html = ' <span class="total_order">' . wc_price($price * $custom_quantity) . '</span>';
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
                                    <input type="number" variation_name="<?= $key ?>" step="<?php echo $purchase_properties['step']; ?>" value="<?php echo $custom_quantity; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties['min']; ?>" max="<?php echo $purchase_properties['max']; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>"  >
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
                                <input type="number" variation_name="<?= $attribute_str ?>" step="<?php echo $purchase_properties['step']; ?>" value="<?php echo $custom_quantity; ?>" class="wpc-custom-right-quantity-input wpd-qty" min="<?php echo $purchase_properties['min']; ?>" max="<?php echo $purchase_properties['max']; ?>" dntmesecondfocus="true" uprice="<?php echo $price; ?>"  >
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
                $custom_quantity = isset($_GET['custom_qty']) ? $_GET['custom_qty'] : $purchase_properties['min_to_purchase'];
                $price_html = ' <span class="total_order">' . wc_price($price * $custom_quantity) . '</span>';
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
        wp_enqueue_script('wpd-accordion-js', WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-block-UI-js', WPD_URL . 'public/js/blockUI/jquery.blockUI.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-lazyload-js', WPD_URL . 'public/js/jquery.lazyload.min.js', array('jquery'), WPD_VERSION, false);
        wp_enqueue_script('wpd-editor-img-js', WPD_URL . 'public/js/editor.img.js', array('jquery', 'wpd-lazyload-js'), WPD_VERSION, false);
        wp_enqueue_script('wp-js-hooks', WPD_URL . 'public/js/wp-js-hooks.min.js', array('jquery'), WPD_VERSION, false);

        wpd_register_upload_scripts();
    }

    private function register_styles() {
        wp_enqueue_style('wpd-spectrum-css', WPD_URL . 'public/css/spectrum.min.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style('wpd-SpryAccordion-css', WPD_URL . 'public/js/SpryAssets/SpryAccordion.min.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style('wpd-flexiblegs', WPD_URL . 'admin/css/flexiblegs.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style('wpd-editor', WPD_URL . 'public/css/editor.css', array(), WPD_VERSION, 'all');
        wp_enqueue_style('wpd-fancyselect-css', WPD_URL . 'public/css/fancySelect.min.css', array(), WPD_VERSION, 'all');
        wpd_register_fonts();
    }

}
