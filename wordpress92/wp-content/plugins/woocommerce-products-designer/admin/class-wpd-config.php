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
class WPD_Config {

    public function register_cpt_config() {

        $labels = array(
            'name' => _x('Configurations', 'wpd'),
            'singular_name' => _x('Configurations', 'wpd'),
            'add_new' => _x('New configuration', 'wpd'),
            'add_new_item' => _x('New configuration', 'wpd'),
            'edit_item' => _x('Edit configuration', 'wpd'),
            'new_item' => _x('New configuration', 'wpd'),
            'view_item' => _x('View configuration', 'wpd'),
            'not_found' => _x('No configuration found', 'wpd'),
            'not_found_in_trash' => _x('No configuration in the trash', 'wpd'),
            'menu_name' => _x('Product Designer', 'wpd'),
            'all_items' => _x('Configurations', 'wpd'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Configurations',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
        );

        register_post_type('wpd-config', $args);
    }

    public function get_config_metabox() {

        $screens = array('wpd-config');

        foreach ($screens as $screen) {

            // add_meta_box(
            //         'wpd-config-basic-config', __('Basic settings', 'wpd'), array($this, 'get_config_basic_page'), $screen
            // );

//            add_meta_box(
//                    'wpd-metas-canvas', __('Canvas', 'wpd'), array($this, 'get_config_canvas_page'), $screen
//            );

            add_meta_box(
                    'wpd-metas-parts', __('Parts', 'wpd'), array($this, 'get_config_parts_page'), $screen
            );

            add_meta_box(
                    'wpd-metas-output', __('Output settings', 'wpd'), array($this, 'get_config_output_page'), $screen
            );
        }
    }

    /**
     * Register text settings
     */
    public function get_text_settings() {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-text-settings',
        );

        $use_global_fonts = array(
            'title' => __('Use global fonts', 'wpd'),
            'name' => 'wpd-metas[use-global-fonts]',
            'type' => 'select',
            'id' => 'wpd-use-global-fonts',
            'options' => array(
                'yes' => __('Yes', 'wpd'),
                'no' => __('No', 'wpd'),
            ),
        );

        $fonts = get_option('wpc-fonts');

        $display_fonts = array();
        foreach ($fonts as $font) {
            if (isset($font[0]) && !empty($font[0])) {
                $display_fonts[wp_json_encode($font)] = __($font[0], 'wpd');
            }
        }

        $select_font = array(
            'title' => __('Select fonts', 'wpd'),
            'name' => 'wpd-metas[select-fonts]',
            'type' => 'multiselect',
            'id' => 'select-fonts',
            'desc' => __('Select usable fonts on this configuration', 'wpd'),
            'options' => $display_fonts,
        );


        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $use_global_fonts,
            $select_font,
            $end,
        );
        echo o_admin_fields($settings);
        ?>
        <input type="hidden" name="securite_nonce" value="<?php echo wp_create_nonce('securite-nonce'); ?>"/>
        <?php
    }

    public function close_output_metabox($classes) {
        array_push($classes, 'closed');

        return $classes;
    }

    public function get_config_basic_page() {

        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-basic-settings-container',
        );
        // $design_from_blank = array(
        //     'title' => __('Design from blank', 'wpd'),
        //     'desc' => __('If enabled, the plugin will display the <strong>Design from blank</strong> button on the product page.', 'wpd'),
        //     'name' => 'wpd-metas[can-design-from-blank]',
        //     'type' => 'checkbox',
        //     'value' => 1,
        // );
        $upload_design = array(
            'title' => __('Custom designs uploads', 'wpd'),
            'desc' => __('If enabled, the plugin will display the <strong>Upload your own design</strong> button on the product page.', 'wpd'),
            'name' => 'wpd-metas[can-upload-custom-design]',
            'type' => 'checkbox',
            'value' => 1,
        );

        $end = array(
            'type' => 'sectionend',
        );

        $settings = array(
            $begin,
            $design_from_blank,
            $end,
        );
        echo o_admin_fields($settings);
    }

    public function get_config_templates() {
        $args = array('post_status' => 'publish');
        $pages = get_pages($args);
        $options = array();
        $options[''] = 'Default';
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-templates-container',
        );

        $template_page = array(
            'title' => __('Templates page', 'wpd'),
            'desc' => __(' Templates selection page when multiple templates are assigned to a custom product with this configuration.', 'wpd'),
            'name' => 'wpd-metas[templates-page]',
            'type' => 'select',
            'options' => $options,
            'default' => 'default',
        );

        $end = array(
            'type' => 'sectionend',
        );

        $settings = array(
            $begin,
            $template_page,
            $end,
        );
        echo o_admin_fields($settings);
    }

    public function get_config_form_builder() {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-form-builder-container',
        );

        $args = array(
            'post_type' => 'ofb',
            'nopaging' => true,
        );

        $forms = array(
            'title' => __('Form Builder', 'wpd'),
            'desc' => __('The selected form will be displayed on the customization page to gather additional informations from the customer.', 'wpd'),
            'name' => 'wpd-metas[form-builder]',
            'type' => 'post-type',
            'default' => 'default',
            'args' => $args,
        );

        $end = array(
            'type' => 'sectionend',
        );

        $settings = array(
            $begin,
            $forms,
            $end,
        );
        echo o_admin_fields($settings);
    }

    public function get_config_canvas_page() {
        wp_enqueue_media();
        global $wpd_settings;
        $canvas_global_settings = get_proper_value($wpd_settings, 'wpc-general-options', array());

        $args = array('post_status' => 'publish');
        $pages = get_pages($args);
        $pages_arr = array();

        foreach ($pages as $page) {
            $pages_arr[$page->ID] = $page->post_title;
        }
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-canvas-settings',
        );
        $max_width = array(
            'title' => __('Canvas max width (px)', 'wpd'),
            'name' => 'wpd-metas[canvas-w]',
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => 800,
        );
        $max_height = array(
            'title' => __('Canvas max height (px)', 'wpd'),
            'name' => 'wpd-metas[canvas-h]',
            'type' => 'number',
            'desc' => __('in pixels. Canvas max width. Leave this field empty to use the value defined in the global settings.', 'wpd'),
            'default' => 500,
        );

        $dimensions = array(
            'title' => __('Dimensions', 'wpd'),
            'type' => 'groupedfields',
            'desc' => __('Width and Height of the design area in the browser. <span style="color: red">It can be smaller than the expected output files\'s in order to fit inside the website pages but need to be a scaled version of the expected output.</span>', 'wpd'),
            'fields' => array($max_width, $max_height),
        );

        $watermark = array(
            'title' => __('Watermark', 'wpd'),
            'name' => 'wpd-metas[watermark]',
            'type' => 'image',
            'desc' => __('Image which will be applied on top of all previews to prevent the copy of the designs from the website.', 'wpd'),
        );

        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $dimensions,
            $end,
        );
        echo o_admin_fields($settings);
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    public function get_config_bounding_page() {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-bbox-settings',
        );

        $box_x = array(
            'title' => __('X', 'wpd'),
            'name' => 'wpd-metas[bounding_box][x]',
            'type' => 'number',
            'desc' => __('in pixels. Box X coordinate on the canvas.', 'wpd'),
        );
        $box_y = array(
            'title' => __('Y', 'wpd'),
            'name' => 'wpd-metas[bounding_box][y]',
            'type' => 'number',
            'desc' => __('in pixels. Box Y coordinate on the canvas.', 'wpd'),
        );
        $Coordinates = array(
            'title' => __('Coordinates', 'wpd'),
            'type' => 'groupedfields',
            'fields' => array($box_x, $box_y),
            'desc' => __('Determine the position of the bounding box on the canvas', 'wpd'),
        );

        $width = array(
            'title' => __('Width', 'wpd'),
            'name' => 'wpd-metas[bounding_box][width]',
            'type' => 'number',
            'desc' => __('Box width on the canvas.', 'wpd'),
        );
        $height = array(
            'title' => __('Height', 'wpd'),
            'name' => 'wpd-metas[bounding_box][height]',
            'type' => 'number',
            'desc' => __('Box height on the canvas.', 'wpd'),
        );
        $dimensions = array(
            'title' => __('Dimensions', 'wpd'),
            'type' => 'groupedfields',
            'fields' => array($width, $height),
            'desc' => __('Dimensions of the bounding box on the canvas', 'wpd'),
        );
        $radius_rec = array(
            'title' => __('Radius (Rounded rect)', 'wpd'),
            'name' => 'wpd-metas[bounding_box][r_radius]',
            'type' => 'number',
            'default' => 0,
            'desc' => __('Box radius (used to create rounded rectangles box).', 'wpd'),
        );
        $radius_circ = array(
            'title' => __('Radius (Circle)', 'wpd'),
            'name' => 'wpd-metas[bounding_box][radius]',
            'type' => 'text',
            'default' => 0,
            'desc' => __('Box radius. Used if the box shape is a circle', 'wpd'),
        );
        $type = array(
            'title' => __('Box shape', 'wpd'),
            'name' => 'wpd-metas[bounding_box][type]',
            'type' => 'select',
            'options' => array(
                'rect' => __('Rectangle', 'wpd'),
                'arc' => __('Circle', 'wpd'),
            ),
            'desc' => __('Shape of the bounding box', 'wpd'),
        );
        $border_color = array(
            'title' => __('Border color', 'wpd'),
            'name' => 'wpd-metas[bounding_box][border_color]',
            'type' => 'text',
            'desc' => __('Box border color (ex. #fff', 'wpd'),
        );
        $include_bounding_box_in_output = array(
            'title' => __('Include bounding box in the output files', 'wpd'),
            'name' => 'wpd-metas[bounding_box][include_in_output]',
            'default' => 'no',
            'type' => 'radio',
            'desc' => __('This option allows you include or not the bounding box in the output files', 'wpd'),
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd'),
            ),
        );

        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $Coordinates,
            $dimensions,
            $radius_rec,
            $radius_circ,
            $type,
            $border_color,
            $include_bounding_box_in_output,
            $end,
        );
        echo o_admin_fields($settings);
    }

    public function get_config_parts_page() {
        wp_enqueue_media();
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-bbox-settings',
        );

        $name = array(
            'title' => __('Name', 'wpd'),
            'type' => 'text',
            'name' => 'name',
        );

        $part_icon = array(
            'title' => __('Icon', 'wpd'),
            'name' => 'icon',
            'type' => 'image',
            'set' => 'Set',
            'remove' => 'Remove',
        );
        $bg_inc = array(
            'title' => __('Bg. (inc.)', 'wpd'),
            'name' => 'bg-inc',
            'type' => 'image',
            'set' => 'Set',
            'remove' => 'Remove',
            'desc' => __('Canvas background image included in the output.', 'wpd'),
        );
        $bg_not_inc = array(
            'title' => __('Bg. (not inc.)', 'wpd'),
            'name' => 'bg-not-inc',
            'type' => 'image',
            'set' => 'Set',
            'remove' => 'Remove',
            'desc' => __('Canvas background image not included in the output.', 'wpd'),
        );

        $overlay = array(
            'title' => __('Overlay', 'wpd'),
            'name' => 'ov-img',
            'type' => 'image',
            'set' => 'Set',
            'remove' => 'Remove',
        );

        $overlay_inc = array(
            'title' => __('Inc. overlay in output', 'wpd'),
            'name' => 'ov-inc',
            'type' => 'checkbox',
            'value' => 1,
        );

        $parts = array(
            'title' => __('Parts', 'wpd'),
            'name' => 'wpd-metas[parts]',
            'desc' => __('Product parts settings. <br><strong>Icon</strong>: Part icon <br><strong>Bg</strong>: Background <br><strong>Ov</strong>: Overlay<br><strong>Inc</strong>: Included', 'wpd'),
            'type' => 'repeatable-fields',
            'fields' => array($name, $part_icon, $bg_inc, $bg_not_inc, $overlay, $overlay_inc),
            'ignore_desc_col' => false,
            'add_btn_label' => __('Add Part', 'wpd'),
        );
        $end = array('type' => 'sectionend');

        $settings = array(
            $begin,
            $parts,
            $end,
        );
        echo o_admin_fields($settings);
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    public function get_config_pricing_page() {
        $post_id = get_the_ID();
        $wpc_metas = get_post_meta($post_id, 'wpd-metas', true);
        $params = array(
            'part_used' => __('Part used', 'wpd'),
            'txt_nb_chars' => __('NB chars in text', 'wpd'),
            'txt_nb_lines' => __('NB lines in text', 'wpd'),
            'img_nb' => __('NB images', 'wpd'),
            'path_nb' => __('NB vectors', 'wpd'),
        );
        $first_rule = $this->get_rule_tpl($params, true);
        $rule_tpl = $this->get_rule_tpl($params, false);
        ?>
        <div class='wpc-rules-table-container'>
            <textarea id='wpc-rule-tpl' style='display: none;'>
                <?php echo $rule_tpl; ?>
            </textarea>
            <textarea id='wpc-first-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <?php
            $pricing_rules = array();
            if (isset($wpc_metas['pricing-rules'])) {
                $pricing_rules = $wpc_metas['pricing-rules'];
            }
            if (is_array($pricing_rules) && !empty($pricing_rules)) {
                $rule_group = 0;
                foreach ($pricing_rules as $rules_group) {
                    $rule_index = 0;
                    $rules = $rules_group['rules'];
                    $a_price = $rules_group['a_price'];
                    $scope = $rules_group['scope'];
                    ?>
                    <table class="wpc-rules-table widefat">
                        <tbody>
                            <?php
                            foreach ($rules as $rule_arr) {
                                if ($rule_index == 0) {
                                    $rule_html = $this->get_rule_tpl($params, true, $rule_arr['param'], $rule_arr['operator'], $rule_arr['value'], $a_price, $scope, count($rules));
                                } else {
                                    $rule_html = $this->get_rule_tpl($params, false, $rule_arr['param'], $rule_arr['operator'], $rule_arr['value']);
                                }
                                $r1 = str_replace('{rule-group}', $rule_group, $rule_html);
                                $r2 = str_replace('{rule-index}', $rule_index, $r1);
                                echo $r2;
                                $rule_index++;
                            }
                            $rule_group++;
                            ?>
                        </tbody>
                    </table>
                    <?php
                }
            } else {
                ?>
                <table class="wpc-rules-table widefat">
                    <tbody>
                        <?php
                        $rule_group = 0;
                        $rule_index = 0;
                        $r1 = str_replace('{rule-group}', $rule_group, $first_rule);
                        $r2 = str_replace('{rule-index}', $rule_index, $r1);
                        echo $r2;
                        ?>
                    </tbody>
                </table>
                <?php
            }
            ?>

        </div>
        <a class="button wpc-add-group mg-bot-10i">Add rule group</a>
        <?php
    }

    private function get_rule_tpl($params, $with_price = false, $default_param = false, $default_operator = '<', $default_value = '', $default_price = '', $default_scope = 'item', $count = 1) {
        ob_start();
        $operators = array(
            '<' => __('is less than', 'wpd'),
            '<=' => __('is less or equal to', 'wpd'),
            '==' => __('equals', 'wpd'),
            '>' => __('more than', 'wpd'),
            '>=' => __('more or equal to', 'wpd'),
        );
        $scopes = array(
            'item' => __('Per item', 'wpd'),
            'additional-items' => __('Per additional item', 'wpd'),
            'design' => __('On whole design', 'wpd'),
        );
        ?>
        <tr data-id="rule_{rule-group}">
            <td class="param">
                <select id="wpc-group_{rule-group}_rule_{rule-index}_param" class="select wpc-pricing-group-param" name="wpd-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][param]">
                    <?php
                    foreach ($params as $param_key => $param_val) {
                        if ($param_key == $default_param) {
                            ?>
                            <option value='<?php echo $param_key; ?>' selected="selected"><?php echo $param_val; ?></option>
                            <?php
                        } else {
                            ?>
                            <option value='<?php echo $param_key; ?>'><?php echo $param_val; ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="operator">
                <select id="wpc-pricing-group_{rule-group}_rule_{rule-index}_operator" class="select wpc-pricing-group_operator operator_{rule-group}" name="wpd-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][operator]">
                    <?php
                    foreach ($operators as $operator_key => $operator_val) {
                        if ($operator_key == $default_operator) {
                            ?>
                            <option value='<?php echo $operator_key; ?>' selected="selected"><?php echo $operator_val; ?></option>
                            <?php
                        } else {
                            ?>
                            <option value='<?php echo $operator_key; ?>'><?php echo $operator_val; ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="value">
                <input type="text" name="wpd-metas[pricing-rules][group_{rule-group}][rules][rule_{rule-index}][value]" value="<?php echo $default_value; ?>" placeholder="number">
            </td>
            <?php
            if ($with_price) {
                ?>
                <td class="a_price" rowspan="<?php echo $count; ?>">
                    <input type="number" name="wpd-metas[pricing-rules][group_{rule-group}][a_price]" value="<?php echo $default_price; ?>" placeholder="price" step="any">
                    <select id="wpc-pricing-group_{rule-group}_rule_{rule-index}_scope" class="select pricing_rule_scope scope_{rule-group}" name="wpd-metas[pricing-rules][group_{rule-group}][scope]">
                        <?php
                        foreach ($scopes as $scope_key => $scope_val) {
                            if ($scope_key === $default_scope) {
                                ?>
                                <option value='<?php echo $scope_key; ?>' selected="selected"><?php echo $scope_val; ?></option>
                                <?php
                            } else {
                                ?>
                                <option value='<?php echo $scope_key; ?>'><?php echo $scope_val; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
                <?php
            }
            ?>
            <td class="add">
                <a class="wpc-add-rule button" data-group='{rule-group}'><?php echo __('and', 'wpd'); ?></a>
            </td>
            <td class="remove">
                <a class="wpc-remove-rule acf-button-remove"></a>
            </td>
        </tr>
        <?php
        $rule_tpl = ob_get_contents();
        ob_end_clean();
        return $rule_tpl;
    }

    public function get_config_output_page() {
        global $wpd_settings;
        $output_global_settings = get_proper_value($wpd_settings, 'wpc-output-options', array());

        $options = array();
        $output_format = array(
            'title' => __('Output Format', 'wpd'),
            'name' => 'wpd-metas[output-settings][output-format]',
            'type' => 'radio',
            'row_class' => 'config-output-format',
            'options' => array(
                'png' => __('PNG', 'wpd'),
                'jpg' => __('JPG', 'wpd'),
            ),
            'default' => 'png',
        );
        $output_w = array(
            'title' => __('Output width (px)', 'wpd'),
            'name' => 'wpd-metas[output-settings][wpc-min-output-width]',
            'type' => 'text',
            'row_class' => 'hide-if-pdf',
            'default' => get_proper_value($output_global_settings, 'wpc-min-output-width'),
        );
        $output_loop_delay = array(
            'title' => __('Output loop delay (milliseconds)', 'wpd'),
            'name' => 'wpd-metas[output-settings][wpc-output-loop-delay]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'wpc-output-loop-delay'),
        );

        $zip_output = array(
            'title' => __('Zip output file', 'wpd'),
            'name' => 'wpd-metas[output-settings][zip-output]',
            'type' => 'radio',
            'row_class' => 'zip-output',
            'options' => array(
                'yes' => __('Yes', 'wpd'),
                'no' => __('No', 'wpd'),
            ),
            'default' => 'no',
        );
        $zip_folder_name = array(
            'title' => __('Zip output folder prefix', 'wpd'),
            'name' => 'wpd-metas[output-settings][zip-folder-name]',
            'type' => 'text',
            'row_class' => 'show-if-zip',
        );
        $cmyk_conversion = array(
            'title' => __('CMYK conversion (Requires ImageMagick)', 'wpd'),
            'name' => 'wpd-metas[output-settings][wpc-cmyk-conversion]',
            'type' => 'radio',
            'row_class' => 'show-if-jpg',
            'options' => array(
                'no' => __('No', 'wpd'),
                'yes' => __('Yes', 'wpd'),
            ),
            'default' => get_proper_value($output_global_settings, 'wpc-cmyk-conversion'),
        );

        $pdf_format = array(
            'title' => __('PDF Format', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-format]',
            'type' => 'groupedselect',
            'desc' => __('Output PDF format and dimensions', 'wpd'),
            'data-id' => '',
            'row_class' => 'show-if-pdf',
            'options' => get_wpd_pdf_formats(),
            'default' => get_proper_value($output_global_settings, 'pdf-format'),
        );

        $pdf_w = array(
            'title' => __('Width', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-width]',
            'type' => 'number',
            'default' => get_proper_value($output_global_settings, 'pdf-width'),
        );

        $pdf_h = array(
            'title' => __('Height', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-height]',
            'type' => 'number',
            'default' => get_proper_value($output_global_settings, 'pdf-height'),
        );

        $pdf_unit = array(
            'title' => __('Unit', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-unit]',
            'type' => 'select',
            'class' => 'pdf-unit',
            'options' => array(
                'pt' => __('Point', 'wpd'),
                'mm' => __('Millimeter', 'wpd'),
                'cm' => __('Centimeter', 'wpd'),
                'px' => __('Pixels', 'wpd'),
                'in' => __('Inch', 'wpd'),
            ),
            'default' => get_proper_value($output_global_settings, 'pdf-unit'),
        );

        $pdf_custom_dimensions = array(
            'title' => 'Custom PDF dimensions',
            'desc' => __('These dimensions will only be used if the PDF format is set to Custom.', 'wpd'),
            'type' => 'groupedfields',
            'row_class' => 'show-if-pdf',
            'fields' => array($pdf_w, $pdf_h, $pdf_unit),
        );
        $pdf_resolution = array(
            'title' => 'PDF resolution (dpi)',
            'desc' => __('Output PDF resolution.', 'wpd'),
            'type' => 'number',
            'name' => 'wpd-metas[output-settings][resolution]',
            'row_class' => 'hide-if-pixels show-if-pdf',
            'default' => 300,
        );

        $pdf_margin_top_bottom = array(
            'title' => __('PDF Margin Top & Bottom', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-margin-tb]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'pdf-margin-tb', 20),
        );

        $pdf_margin_left_right = array(
            'title' => __('PDF Margin Left & Right', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-margin-lr]',
            'type' => 'text',
            'default' => get_proper_value($output_global_settings, 'pdf-margin-lr', 20),
        );

        $margins = array(
            'title' => __('Margins', 'wpd'),
            'type' => 'groupedfields',
            'desc' => __('Vertical and Horizontal margins in the PDF file.', 'wpd'),
            'row_class' => 'show-if-pdf',
            'fields' => array($pdf_margin_top_bottom, $pdf_margin_left_right),
        );

        $crop_x = array(
            'title' => __('Center Abcissa', 'wpd'),
            'name' => 'x',
            'type' => 'number',
        );
        $crop_y = array(
            'title' => __('Center Ordinate', 'wpd'),
            'name' => 'y',
            'type' => 'number',
        );
        $crop_w = array(
            'title' => __('Width', 'wpd'),
            'name' => 'w',
            'type' => 'number',
        );
        $crop_h = array(
            'title' => __('Height', 'wpd'),
            'name' => 'h',
            'type' => 'number',
        );
        $type = array(
            'title' => __('Type', 'wpd'),
            'name' => 'type',
            'type' => 'text',
        );

        $crop_marks = array(
            'title' => __('Crop marks', 'wpd'),
            'name' => 'wpd-metas[output-settings][crop-marks]',
            'desc' => __('Crop marks settings.<br> <strong>Type</strong>: One symbol per type separated by comma: <ul><li>T = TOP</li><li>F = BOTTOM</li> <li>L = LEFT</li> <li>R = RIGHT</li><li>TL = A = TOP-LEFT</li><li>TR = B = TOP-RIGHT</li><li>BL = C = BOTTOM-LEFT</li><li>BR = D = BOTTOM-RIGHT</li></ul>', 'wpd'),
            'type' => 'repeatable-fields',
            'fields' => array($crop_x, $crop_y, $crop_w, $crop_h, $type),
            'row_class' => 'hide-if-pixels show-if-pdf',
            'ignore_desc_col' => false,
            'add_btn_label' => __('Add Mark', 'wpd'),
        );

        $bleed_x = array(
            'title' => __('H. spacing', 'wpd'),
            'name' => 'h',
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
        );
        $bleed_y = array(
            'title' => __('V. spacing', 'wpd'),
            'name' => 'v',
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
        );

        $bleed_color = array(
            'title' => __('Border color', 'wpd'),
            'name' => 'color',
            'type' => 'text',
            'custom_attributes' => array('placeholder' => '#FF0000 for ex.'),
        );

        $bleed_dash_spacing = array(
            'title' => __('Dash spacing', 'wpd'),
            'name' => 'dash-spacing',
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
        );

        $bleed_border_width = array(
            'title' => __('Border width', 'wpd'),
            'name' => 'border-width',
            'type' => 'number',
            'custom_attributes' => array('step' => '0.01'),
        );

        $bleed = array(
            'title' => __('Bleed, Trim & Safe zones', 'wpd'),
            'name' => 'wpd-metas[output-settings][zones]',
            'desc' => __('<ul><li><strong>H. spacing</strong>: Space between the left and right borders of the document and the area.</li><li><strong>V. spacing</strong>: Space between the top and bottom borders of the document and the area.</li></ul>', 'wpd'),
            'type' => 'repeatable-fields',
            'fields' => array($bleed_x, $bleed_y, $bleed_color, $bleed_dash_spacing, $bleed_border_width),
            'row_class' => 'hide-if-pixels show-if-pdf',
            'ignore_desc_col' => false,
            'add_btn_label' => __('Add zone', 'wpd'),
        );

        $pdf_orientation = array(
            'title' => __('PDF Orientation', 'wpd'),
            'name' => 'wpd-metas[output-settings][pdf-orientation]',
            'default' => 'P',
            'type' => 'select',
            'row_class' => 'show-if-pdf',
            'options' => array(
                'P' => __('Portrait', 'wpd'),
                'L' => __('Landscape', 'wpd'),
            ),
            'default' => get_proper_value($output_global_settings, 'pdf-orientation'),
        );
	$output_width	 = array(
	    'title'		 => __( 'Output width', 'wpd' ),
	    'name'		 => 'wpd-metas[output-settings][output-w]',
	    'class'		 => 'wpd-output-dimension',
	    'id'		 => 'wpd-output-w',
	    'type'		 => 'number',
	    'default'	 => 800,
	);
	$output_height	 = array(
	    'title'		 => __( 'Output height', 'wpd' ),
	    'name'		 => 'wpd-metas[output-settings][output-h]',
	    'class'		 => 'wpd-output-dimension',
	    'id'		 => 'wpd-output-h',
	    'type'		 => 'number',
	    'default'	 => 500,
	);
	$output_unit	 = array(
	    'title'		 => __( 'Unit', 'wpd' ),
	    'name'		 => 'wpd-metas[output-settings][output-unit]',
	    'id'		 => 'wpd-output-unit',
	    'type'		 => 'select',
	    'class'		 => 'wpd-output-dimension output-unit',
	    'options'	 => array(
		'pt'	 => __( 'Point', 'wpd' ),
		'mm'	 => __( 'Millimeter', 'wpd' ),
		'cm'	 => __( 'Centimeter', 'wpd' ),
		'px'	 => __( 'Pixels', 'wpd' ),
		'in'	 => __( 'Inch', 'wpd' ),
	    ),
	    'default'	 => get_proper_value( $output_global_settings, 'output-unit', 'px' ),
	);

	$dimensions	 = array(
	    'title'	 => __( 'Output files dimensions', 'wpd' ),
	    'type'	 => 'groupedfields',
	    'desc'	 => __( 'What are your desired output files dimensions?', 'wpd' ),
	    'fields' => array( $output_width, $output_height, $output_unit ),
	);
        $output_options_begin = array(
            'type' => 'sectionbegin',
            'title' => __('Output Settings', 'wpd'),
            'table' => 'metas',
            'id' => 'wpc_product_output',
        );

        $output_options_end = array(
            'type' => 'sectionend',
            'id' => 'wpc_product_output',
        );

        array_push($options, $output_options_begin);
	array_push( $options, $dimensions );
        array_push($options, $output_format);
        array_push($options, $output_loop_delay);
        array_push($options, $zip_folder_name);
        array_push($options, $zip_output);
        array_push($options, $output_options_end);
        ?>

        <?php
        echo o_admin_fields($options);
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl = <?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    public function save_config( $post_id ) {
	$meta_key	 = 'wpd-metas';
	$checkboxes	 = array( 'can-design-from-blank', 'can-upload-custom-design' );
	$tab_to_merged	 = array( 'output-settings' );
	if ( isset( $_POST[ $meta_key ] ) ) {
	    $old_metas = get_post_meta( $post_id, $meta_key, true );
	    if ( empty( $old_metas ) ) {
		$old_metas = array();
	    }
	    $new_metas = $_POST[ $meta_key ];
	    foreach ( $tab_to_merged as $value ) {
		if ( is_array( $new_metas[ $value ] ) ) {
		    if ( isset( $old_metas[ $value ] ) ) {
			$new_metas[ $value ] = array_merge( $old_metas[ $value ], $new_metas[ $value ] );
		    }
		}
	    }
	    foreach ( $old_metas as $key => $value ) {

		if ( ! isset( $new_metas[ $key ] ) ) {
		    $new_metas[ $key ] = $value;
		}
	    }
	    //$new_metas = array_replace($old_metas, $_POST[$meta_key]);
	    //die( print_r( $new_metas ) );
	    foreach ( $checkboxes as $checkboxes_key ) {
		if ( ! isset( $_POST[ $meta_key ][ $checkboxes_key ] ) && isset( $new_metas[ $checkboxes_key ] ) ) {
		    unset( $new_metas[ $checkboxes_key ] );
		}
	    }
	    update_post_meta( $post_id, $meta_key, $new_metas );
	}
    }

    private function get_part_name($part_key) {
        $parts = get_option('wpc-parts');
        foreach ($parts as $part) {
            if (sanitize_title($part) == $part_key) {
                return $part;
            }
        }
    }

    public function get_product_config_selector() {
        $id = get_the_ID();

        $args = array(
            'post_type' => 'wpd-config',
            'nopaging' => true,
        );
        $configs = get_posts($args);
        $configs_ids = array('' => 'None');
        foreach ($configs as $config) {
            $configs_ids[$config->ID] = $config->post_title;
        }
        ?>
        <div id="vpc_config_data" class="show_if_simple">
            <?php
            $this->get_product_tab_row($id, $configs_ids, 'Configuration');
            ?>
        </div>
        <?php
    }

    private function get_product_tab_row($pid, $configs_ids, $title) {
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wpd-metas-data',
        );

        $configurations = array(
            'title' => $title,
            'name' => "wpd-metas[$pid][config-id]",
            'type' => 'select',
            'options' => $configs_ids,
        );

        $end = array('type' => 'sectionend');
        $settings = apply_filters(
                'vpc_product_tab_settings', array(
            $begin,
            $configurations,
            $end,
                )
        );

        echo "<div class='vpc-product-config-row'>" . o_admin_fields($settings) . '</div>';
    }

    /*
     *  set Variables product configuration form*
     */

    public function get_variation_product_config_selector($loop, $variation_data, $variation) {
        $id = $variation->ID;

        $args = array(
            'post_type' => 'wpd-config',
            'nopaging' => true,
        );
        $configs = get_posts($args);
        $configs_ids = array('' => 'None');
        foreach ($configs as $config) {
            $configs_ids[$config->ID] = $config->post_title;
        }
        ?>
        <tr>
            <td>
                <?php
                $this->get_product_tab_row($id, $configs_ids, 'Configuration');
                ?>
            </td>
        </tr>
        <?php
    }

    public function get_metabox_order($order) {
        $order['advanced'] = 'wpd-config-basic-config,wpd-metas-output,wpd-metas-bounding-box,wpd-metas-parts,wpd-text-settings,wpd-metas-pricing-rules,wpd-config-templates,wpd-config-form-builder,submitdiv';
        return $order;
    }

    function get_duplicate_post_link($actions, $post) {
        if ($post->post_type == 'wpd-config' && current_user_can('edit_posts')) {
            $actions['duplicate'] = '<a href="admin.php?action=wpd_duplicate_config&amp;post=' . $post->ID . '&amp;duplicate_nonce=' . wp_create_nonce(basename(__FILE__)) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }

    /*
     * Function creates post duplicate as a draft and redirects then to the edit post screen
     */

    function wpd_duplicate_config() {
        global $wpdb;
        if (!( isset($_GET['post']) || isset($_POST['post']) || ( isset($_REQUEST['action']) && 'wpd_duplicate_config' == $_REQUEST['action'] ) )) {
            wp_die('No post to duplicate has been supplied!');
        }

        /*
         * Nonce verification
         */
        if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
            return;
        }

        /*
         * get the original post id
         */
        $post_id = ( isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']) );
        /*
         * and all the original post data then
         */
        $post = get_post($post_id);

        /*
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        /*
         * if post data exists, create the post duplicate
         */
        if (isset($post) && $post != null) {

            /*
             * new post data array
             */
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $post->post_parent,
                'post_password' => $post->post_password,
                'post_status' => 'draft',
                'post_title' => $post->post_title . __(' - copy', 'wpd'),
                'post_type' => $post->post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order,
            );

            /*
             * insert the post by wp_insert_post() function
             */
            $new_post_id = wp_insert_post($args);

            /*
             * get all current post terms ad set them to the new post draft
             */
            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            /*
             * duplicate all post meta just in two SQL queries
             */
            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug') {
                        continue;
                    }
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(' UNION ALL ', $sql_query_sel);
                $wpdb->query($sql_query);
            }

            /*
             * finally, redirect to the edit post screen for the new draft
             */
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    public function save_variation_settings_fields($variation_id) {
        $meta_key = 'wpd-metas';
        if (isset($_POST[$meta_key])) {
            $variation = wc_get_product($variation_id);
            $old_metas = get_post_meta($variation->parent->id, $meta_key, true);
            if (empty($old_metas)) {
                $old_metas = array();
            }
            $new_metas = array_replace($old_metas, $_POST[$meta_key]);
            update_post_meta($variation->parent->id, $meta_key, $new_metas);
        }
    }

    public function get_wpd_config_screen_layout_columns($columns) {
        $columns['wpd-config'] = 1;
        return $columns;
    }

    public function get_wpd_config_config_screen_layout() {
        return 1;
    }
    
     function migrate_metas_to_v7() {
	$configs=get_posts(array('post_type'=>'wpd-config'));
	foreach ( $configs as $config ) {
	    $old_metas=get_post_meta($config->ID , 'wpd-metas',true);
	   if(isset($old_metas['output-settings']['wpc-min-output-width']) && ! empty($old_metas['output-settings']['wpc-min-output-width']) && isset($old_metas['canvas-w']) && ! empty($old_metas['canvas-w'])&& isset($old_metas['canvas-h']) && ! empty($old_metas['canvas-h']))
	{
	    $old_metas['output-settings']['output-w']=$old_metas['output-settings']['wpc-min-output-width'];
	    $old_metas['output-settings']['output-h']=($old_metas['output-settings']['wpc-min-output-width']*$old_metas['canvas-h'])/$old_metas['canvas-w'];
	    $old_metas['output-settings']['output-unit']='px';
	}elseif ( isset($old_metas['output-settings']['pdf-width']) && ! empty($old_metas['output-settings']['pdf-width']) && isset($old_metas['output-settings']['pdf-height']) && ! empty($old_metas['output-settings']['pdf-height'])&& isset($old_metas['output-settings']['pdf-unit']) && ! empty($old_metas['output-settings']['pdf-unit'])) {
	    $old_metas['output-settings']['output-w']=$old_metas['output-settings']['pdf-width'];
	    $old_metas['output-settings']['output-h']=$old_metas['output-settings']['pdf-height'];
	    $old_metas['output-settings']['output-unit']=$old_metas['output-settings']['pdf-unit'];
	}elseif ( isset ( $old_metas['output-settings']['pdf-format']) && 'custom'!==$old_metas['output-settings']['pdf-format'] ) {
	    $dimensions = TCPDF_STATIC::getPageSizeFromFormat($old_metas['output-settings']['pdf-format']);
	    if(isset($old_metas['output-settings']['resolution']) && ! empty($old_metas['output-settings']['resolution']))
	    {
		$resolution=$old_metas['output-settings']['resolution'];
	    } else {
		$resolution=300;
	    }
	    $old_metas['output-settings']['output-w'] = 0.352778 * $dimensions[0] * $resolution / 25.4;
	    $old_metas['output-settings']['output-h'] = 0.352778 * $dimensions[1] * $resolution / 25.4;
	     $old_metas['output-settings']['output-unit']='px';
	}
update_post_meta( $config->ID, 'wpd-metas', $old_metas );
	}
	update_option( 'wpd-db-version', '7.0' );
    }

}
