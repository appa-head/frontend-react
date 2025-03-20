(function ($) {
    'use strict';

    $(document).ready(function () {

        $(".wpd-qty").keypress(function (e) {
            if (e.which < 48 || e.which > 57) {
                return(false);
            }
        });

        $(".wpc-customize-product, .wpd-buttons-wrap-variation .btn-choose").on("click", function (event) {
            // Fired when the user selects all the required dropdowns / attributes
            event.preventDefault();

            var link = $(this).attr("href");
            var quantity = $(".input-text").val();
            if (typeof quantity !== 'undefined') {
                if (quantity.length > 0) {
                    if (link.indexOf('?') > -1) {
                        link += "&custom_qty=" + quantity;
                    } else {
                        link += "?custom_qty=" + quantity;
                    }
                }
            }
            if ($('.variations_form').length > 0) {
                var attributes = wpd_retrieve_selected_attributes();
                $.post(
                        ajax_object.ajax_url,
                        {
                            action: "wpd_store_variation_attributes",
                            data: attributes
                        }, function () {
                    window.location.href = link;
                });
            } else {
                window.location.href = link;
            }



        });


        /**
         * Get chosen attributes from form.
         * @return array
         */
        function wpd_retrieve_selected_attributes() {
            var data = {};
            var count = 0;
            var chosen = 0;

            $('.variations_form').find('.variations select').each(function () {
                var attribute_name = $(this).data('attribute_name') || $(this).attr('name');
                var value = $(this).val() || '';

                if (value.length > 0) {
                    chosen++;
                }

                count++;
                data[ attribute_name ] = value;
            });

            return {
                'data': data
            };
        }
        ;


        $(".single_variation_wrap").on("show_variation", function (event, variation) {
            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            var variation_id = $("input[name='variation_id']").val();
            if (variation_id)
            {
                $(".wpd-buttons-wrap-variation").hide();
                $(".wpd-buttons-wrap-variation[data-id='" + variation_id + "']").show();

                if (typeof hide_cart_button !== 'undefined') {
                    if ($(".wpd-buttons-wrap-variation[data-id='" + variation_id + "']").length > 0 && hide_cart_button === 1) {
                        $(".wpd-buttons-wrap-variation").parent().find('.add_to_cart_button').hide();
                        $(".wpd-buttons-wrap-variation").parent().find('.single_add_to_cart_button').hide();
                    } else {
                        $(".wpd-buttons-wrap-variation").parent().find('.add_to_cart_button').show();
                        $(".wpd-buttons-wrap-variation").parent().find('.single_add_to_cart_button').show();
                    }
                }

            }
        });

        $(".single_variation_wrap").on("hide_variation", function (event, variation) {
            console.log("hide");
            $(".wpd-buttons-wrap-variation").hide();
        });

        $(".wpd-delete-design").click(function ()
        {
            var index = $(this).data("index");
            $.get(
                    ajax_object.ajax_url,
                    {
                        action: "delete_saved_design",
                        design_index: index,

                    },
                    function (data) {
                        if (data.success)
                        {
                            alert(data.success_message);
                            location.reload();
                        } else
                            alert(data.failure_message);
                    }
            , "json"
                    );
        });

        $(document).on("touchstart click", ".single-product #btn-tpl", function ()
        {
            var e = $(this);
            get_product_select_id(e, template_pages_urls, template_page_message);

        });

        $(document).on("touchstart click", ".wpd-add-to-cart-after-upload", function ()
        {
            $(".single_add_to_cart_button").click();

        });

        function get_product_select_id(e, datas_array, message) {
            var variation_id = 0;
            var type = e.data("type");
            if (type == "simple")
                variation_id = e.data("id");
            else if (type == "variable")
                variation_id = $("input[name='variation_id']").val();
            if (!variation_id)
            {
                alert("Select the product options first");
                return;
            } else
            {
                var page_url = datas_array[variation_id];
                var post_id = e.data("id");
                if (page_url === undefined) {
                    page_url = datas_array[post_id];
                    if (page_url === undefined) {
                        if (message)
                            alert(message);
                    } else
                        $(location).attr('href', page_url);
                } else
                    $(location).attr('href', page_url);
            }
        }

        $(".native-uploader .user-custom-design").change(function () {
            var file = $(this).val().toLowerCase();
            if (file != "")
            {
                $("#custom-upload-form").ajaxForm({
                    success: upload_custom_design_callback
                }).submit();
            }
        });

        if ($('.custom-upload-form.custom-uploader').length)
        {
            $(".custom-upload-form.custom-uploader").each(function (index, value)
            {
                var id = $(this).attr("id");
                var form_obj = $('#' + id + '.custom-uploader');
                var custom_upload_ul = $('#' + id + '.custom-uploader .acd-upload-info');
                // Initialize the jQuery File Upload plugin
                console.log(form_obj);
                form_obj.fileupload({
                    // This element will accept file drag/drop uploading
                    dropZone: form_obj.find('.drop'),
                    url: ajax_object.ajax_url,
                    // This function is called when a file is added to the queue;
                    // either via the browse button, or via drag/drop:
                    add: function (e, data) {
                        var tpl = $('<div class="working"><div class="acd-info"></div><div class="acd-progress-bar"><div class="acd-progress"></div></div></div>');

                        // Append the file name and file size
                        tpl.find('.acd-info').text(data.files[0].name).append('<i>' + formatFileSize(data.files[0].size) + '</i>');

                        // Add the HTML to the UL element
                        custom_upload_ul.html("");
                        data.context = tpl.appendTo(custom_upload_ul);

                        // Initialize the knob plugin
                        tpl.find('input').knob();

                        // Listen for clicks on the cancel icon
                        tpl.find('span').click(function () {

                            if (tpl.hasClass('working')) {
                                jqXHR.abort();
                            }

                            tpl.fadeOut(function () {
                                tpl.remove();
                            });

                        });

                        // Automatically upload the file once it is added to the queue
                        var jqXHR = data.submit();



                    },
                    progress: function (e, data) {

                        // Calculate the completion percentage of the upload
                        var progress = parseInt(data.loaded / data.total * 100, 10);

                        // Update the hidden input field and trigger a change
                        // so that the jQuery knob plugin knows to update the dial
                        //data.context.find('input').val(progress).change();
                        data.context.find('.acd-progress').css("width", progress + "%");

                        if (progress == 100) {
                            data.context.removeClass('working');
                        }
                    },
                    fail: function (e, data) {
                        // Something has gone wrong!
                        data.context.addClass('error');
                    },
                    done: function (e, data) {
                        upload_custom_design_callback(data.result, false, false, false);
                    }
                });
            });

        }



        //Ninja form
        if ($('.wpd-design-opt .ninja-forms-form').length > 0) {

            //Upload my own design form
            if ($('.cart').length > 0) {
                $(document).on('change', '.ninja-forms-form .ninja-forms-field', function () {
                    if ($('#wpd-design-opt-price').length < 1) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'wpd-design-opt-price',
                            name: 'wpd-design-opt'
                        }).appendTo('.cart');
                    }

                    var total = $('.ninja-forms-form .calc-wrap input[type = "text"]').val();
                    var wpc_desing_opt = get_design_options();
                    wpc_desing_opt['opt_price'] = total;
                    $('#wpd-design-opt-price').val(JSON.stringify(wpc_desing_opt));
                    if ($('.amount').length > 0)
                        $('.amount').html($('.wpd-design-opt').data('currency_symbol') + ' ' + (parseFloat($('.wpd-design-opt').data('regular_price')) + parseFloat(total)));
                });
            } else {
                $(document).on('change', '.ninja-forms-form .calc-wrap input[type = "text"]', function () {
                    var total = $(this).val();
                    if ($(".wpd-qty").length > 0) {
                        $(".wpd-qty").attr("opt_price", total);
                        $(".wpd-qty").trigger('change');
                    } else
                        return total;
                });
            }
        }

        function upload_custom_design_callback(responseText, statusText, xhr, form)
        {
            if (is_json(responseText))
            {
                var response = $.parseJSON(responseText);

                if (response.success)
                {
                    $(".wpc-uploaded-file").html(response.message);
                    //If the customer hides the add to cart for custom products, we re display it.
                    $(".wpd-hide-cart-button").removeClass("wpd-hide-cart-button");
                } else
                    alert(response.message);
            } else
                console.log(responseText);
            $(".user-custom-design:visible").val("");
        }

        function formatFileSize(bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }

            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }

            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }

            return (bytes / 1000).toFixed(2) + ' KB';
        }
    });

})(jQuery);

function is_json(data)
{
    if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
            replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
        return true;
    else
        return false;
}

//Serialize ninja form
function get_design_options() {
    var wpc_desing_opt = {};
    var result = {};
    jQuery(".wpc-container .ninja-forms-form-wrap").find("input[type=text], input[type=checkbox], input[type=radio], input[type=number], select, textarea, input[name=_form_id]").each(function (index) {
        var name = jQuery(this).attr("name");
        if (name != "" && typeof name != "undefined")
        {
            var type = jQuery(this).attr("type");
            var val = "";
            if (type == "radio")
            {
                if (jQuery(".ninja-forms-form-wrap [name=" + name + "]:checked").length)
                    val = jQuery(".ninja-forms-form-wrap [name=" + name + "]:checked").val();
                else
                    val = ""
            } else if (type == "checkbox")
            {
                if (jQuery(this).parents('.list-checkbox-wrap').length > 0) {
                    val = (result.hasOwnProperty(name)) ? result[name] : '';
                    if ((jQuery(this).is(":checked"))) {
                        val += jQuery(this).val() + ': checked; ';
                    } else {
                        val += jQuery(this).val() + ': unchecked; ';
                    }
                } else {
                    if ((jQuery(this).is(":checked"))) {
                        val = ' checked';
                    } else {
                        val = ' unchecked';
                    }
                }



            } else if (jQuery.isArray(jQuery(this).val()))
            {
                var tpm_val = jQuery(this).val();
                jQuery.each(tpm_val, function (index, single_val) {
                    if (index < (tpm_val.length - 1)) {
                        val += single_val + ' | ';
                    } else {
                        val += single_val;
                    }
                });
            } else {
                val = jQuery(this).val();
            }

            result[name] = val;
        }
    });
    var output = {};
    if (jQuery(".wpd-qty").length > 0) {
        var opt_price = jQuery(".wpd-qty").first().attr("opt_price");
        if (opt_price != 'undefined') {
            output['opt_price'] = opt_price;
        }
        output['wpc_design_opt_list'] = result;
        return output;
    } else {
        output['wpc_design_opt_list'] = result;
        return output;
    }

}
