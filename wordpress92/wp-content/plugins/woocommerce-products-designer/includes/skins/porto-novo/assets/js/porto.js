(function ($) {
    "use strict";
    $(document).ready(function () {
        $(document).on('touchstart click', '#font-family-selector > div', function () {
            var vpc_get_font_selected = $(this).attr('style').replace('font-family:', '');
            $(this).parent('#font-family-selector').attr('value',vpc_get_font_selected);
            $(this).parent('#font-family-selector').trigger('change');
        });
        
        $('ul.wpc-tabs li').click(function(){
            var wpc_tab_id = $(this).attr('data-tab');

            //$('ul.wpc-tabs li').removeClass('current');
            $(this).parents('ul.wpc-tabs').find('li').removeClass('current');
            //$('.wpc-tab-content').removeClass('current');
            $(this).parents('.wpc-tools-content').find('.wpc-tab-content').removeClass('current');

            $(this).addClass('current');
            $("#"+wpc_tab_id).addClass('current');
        });
        
        
        $(document).on('click', '.wpc-button-bar #align_btn', function () {
            $(".wpc-button-bar .align_btn_wrap").toggleClass('is-open');
        });


        $(document).on('click', function(e){
            var $this = $(e.target);
            if($this.closest('.wpc-button-bar #align_btn').length === 0 ){
              $(".wpc-button-bar .align_btn_wrap").removeClass('is-open');
            }
            if($this.closest('.wpc-button-bar .align_btn_wrap').length === 1 ){
                 $(".wpc-button-bar .align_btn_wrap").addClass('is-open');
            }
        });

        var $first = $('li:first', 'ul#wpc-parts-bar'), $last = $('li:last', 'ul#wpc-parts-bar');
        $first.addClass('first');
        $last.addClass('last');
        var wpd_editor = {};
        wpd_editor.selected_part = -1;
        function wpc_slider_nav(){
            if ($('#wpc-parts-bar li.active').hasClass('first')) {
            $('.wpd-part-prev').hide();
            $('.wpd-part-next').show();
            } else if ($('#wpc-parts-bar li.active').hasClass('last')) {
                $('.wpd-part-next').hide();
                $('.wpd-part-prev').show();
            } else {
                $('.wpd-part-prev').show();
                $('.wpd-part-next').show();
            }
        }
        wpc_slider_nav();
        $(document).on('touchstart click','#product-part-slider > span', function () {
            var $next, $selected = $(".active");
            $next = $selected.next('li').length ? $selected.next('li') : $first;
            $selected.removeClass("active");
            $next.addClass('active');
            var $this =$('#wpc-parts-bar li.active');
            $this.trigger("click");
            wpc_slider_nav();
        });
        $(document).on('touchstart click','#wpc-parts-bar > li', function () {
            wpc_slider_nav();
        });
        function add_text_empty_msg(){
            $("#debug").html("");
            $("#debug").html("<div>"+ wpd.translated_strings.empty_txt_area_msg + "</div>");
        }
        function wpd_hide_debug_box(){
            $('#debug-wrap').removeClass('is-open');
        }
        wp.hooks.addAction('wpd.add_text_empty_msg',add_text_empty_msg );
        wp.hooks.addFilter('wpd.add_not_curved_action_before', wpd_hide_debug_box );
        wp.hooks.addFilter('wpd.add_curved_action_before', wpd_hide_debug_box );

        wp.hooks.addFilter('wpd.add_text_selected', wpd_recup_selected_text);
        function wpd_recup_selected_text(options){
            if(options){
                var wpd_selected_text = options.target.text;
                if(wpd_selected_text){
                    $('.wpd-font-view').html(wpd_selected_text);
                }
                var count = wpd_get_text_nb_line(wpd_selected_text,'\n') + wpd_get_text_nb_line(wpd_selected_text,'|n') + 1;
                if(count > 1){
                    $('#wpd-text-align-row').removeClass('wpd-hide-row');
                }
                else{
                    $('#wpd-text-align-row').addClass('wpd-hide-row');
                }
            } 
           
           $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
            $('#text-panel').addClass('is-active');
            $('#text-panel').parent().find('.wpc-tools-content').addClass('is-active');
            $('#text-panel').parents('.wpc-editor-col').addClass('is-open');
            get_tools_content_height();
        }
        function wpd_get_text_nb_line(string, substring){
           var n=0;
           var pos=0;

           while(true){
               pos=string.indexOf(substring,pos);
               if(pos!==-1){ n++; pos+=substring.length;}
               else{break;}
           }
           return(n);
        }
        
        $(document).on('click touchstart', '.wpc-tools-head', function () {
            WPD_EDITOR.canvas.discardActiveObject();
            $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
            $(this).addClass('is-active');
            $(this).parent().find('.wpc-tools-content').addClass('is-active');
            $(this).parents('.wpc-editor-col').addClass('is-open');
            get_tools_content_height();
        });
        
        $(document).on('click touchstart', '.wpc-tools-close', function () {
            $(this).parents('.wpc-tools-content').removeClass('is-active');
            $(this).parents('.wpc-tools-wrap').find('.wpc-tools-head').removeClass('is-active');
            $(this).parents('.wpc-editor-col').removeClass('is-open');
            
        });
        function get_tools_content_height(){
            var porto_header_height = $('#header-wrap').outerHeight();
            var header_height = $('header').outerHeight();
            var wpadminbar = $('#wpadminbar').outerHeight();
            var panel_head = $('.wpc-tools-content.is-active .wpc-panel-head').outerHeight();
            if(wpadminbar != null && wpadminbar != 'undefined'){
                    var wpadminbar2 = wpadminbar ;
                    wpadminbar = wpadminbar + 21;
            }
            else{
                
                wpadminbar = 0;
                var wpadminbar2 = 0;
            }
            if(porto_header_height != null && porto_header_height != 'undefined'){
                porto_header_height = porto_header_height;
            }
            else{
                porto_header_height = header_height;
            }
            
            if(panel_head != null && panel_head != 'undefined'){
                panel_head = panel_head;
            }
            else{
                panel_head = 41;
            }

            
            
            var tools_panel = $(window).outerHeight() - porto_header_height - wpadminbar;
            var tools_panel_content = tools_panel - panel_head;
            
            var canvas_height = $(window).outerHeight() - porto_header_height - wpadminbar - $('.wpd-responsive-toolbar-box').outerHeight() - $('#product-part-container').outerHeight() - 20;
            var tools_content_top = porto_header_height + wpadminbar2;
            var windowsize = $(window).width();
//            if (windowsize >= 768) {
               
//                $('.wpc-porto-skin .wpc-tab-content-wrapper > div:not(".wpd-group-child-wrap"),#product-variation-scroll-wrap,#wpd-cliparts-groups-wrapper,#wpc-cart-box').css({'height': tools_panel_content});
//                $('#tab-clipart-upload-child').css({'height': tools_panel_content - 30});
//                $('.wpd-group-child-wrap .font-selector-container').css({'height': tools_panel_content - 50});
//                $('.wpd-my-design-preview').css({'height': tools_panel_content - 100});
//                $('#tab-upload #acd-uploaded-img,#tab-social-facebook .img-container,#tab-social-instagram .img-container').css({'height': tools_panel_content - 140});
                $('.wpc-porto-skin.wpc-container .wpd-tab-content-main,.wpc-button-bar-wrap').css({'height': tools_panel, 'max-height': tools_panel});
                $('.wpc-porto-skin .wpc-tools-content').css({'height': tools_panel, 'max-height': tools_panel, 'overflow' :'hidden'});
                var related_scroll_wrap = $('#related-products-panel-content');
                var related_scroll_height= tools_panel - related_scroll_wrap.find('.wpc-panel-head').outerHeight();
                related_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': related_scroll_height});
            
                var text_scroll_wrap = $('#text-panel-content');
                var text_scroll_height = tools_panel - text_scroll_wrap.find('.wpc-panel-head').outerHeight(); 
                text_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': text_scroll_height});
            
                var clipart_scroll_wrap = $('#cliparts-panel-content');
                var clipart_scroll_height = tools_panel - clipart_scroll_wrap.find('#wpd-clipart-group-container .wpc-panel-head').outerHeight();
                clipart_scroll_wrap.find('#wpd-cliparts-groups-wrapper.wpd-tools-content-scroll-wrap').css({'max-height': clipart_scroll_height});
            
                var clipart_upload_scroll_height = tools_panel - clipart_scroll_wrap.find('#cliparts-panel-content-child .wpc-panel-head').outerHeight();
                clipart_scroll_wrap.find('#tab-clipart-upload-child.wpd-tools-content-scroll-wrap').css({'max-height': clipart_upload_scroll_height});
            
                var image_filters_wrap = $('#wpd-filters-tools-wrap');
                var image_filters_scroll_height = tools_panel - image_filters_wrap.find('#wpd-uploads-filters-wrap .wpc-panel-head').outerHeight();
                image_filters_wrap.find('#wpd-uploads-filters-wrap .wpd-tools-content-scroll-wrap').css({'max-height': image_filters_scroll_height});
            
                var cart_scroll_wrap = $('#add-cart-panel-content');
                var cart_scroll_height= tools_panel - cart_scroll_wrap.find('.wpc-panel-head').outerHeight() - cart_scroll_wrap.find('#add-to-cart-btn').outerHeight() - 10;
                cart_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': cart_scroll_height});
            
                var design_scroll_wrap = $('#user-designs-panel-content');
                var design_scroll_height = tools_panel - design_scroll_wrap.find('.wpc-panel-head').outerHeight();
                design_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': design_scroll_height});
            
                var design_content_scroll_height = tools_panel - design_scroll_wrap.find('.wpc-panel-head').outerHeight() - design_scroll_wrap.find('.wpd-child-wrap-close').outerHeight() - 60;
                design_scroll_wrap.find('.wpd-my-design-preview.wpd-tools-content-scroll-wrap').css({'max-height': design_content_scroll_height});
            
                var upload_scroll_wrap = $('#uploads-panel-content');
                var upload_scroll_height= tools_panel - upload_scroll_wrap.find('.wpc-panel-head').outerHeight() - upload_scroll_wrap.find('#userfile_upload_form').outerHeight();
                upload_scroll_wrap.find('#tab-upload .wpd-tools-content-scroll-wrap').css({'max-height': upload_scroll_height});
            
                var upload_social_scroll_height = tools_panel - upload_scroll_wrap.find('.wpc-panel-head').outerHeight() - upload_scroll_wrap.find('.wpc-rs-app').outerHeight();
                upload_scroll_wrap.find('#tab-social-facebook .wpd-tools-content-scroll-wrap,#tab-social-instagram .wpd-tools-content-scroll-wrap').css({'max-height': upload_social_scroll_height});
            
            
            
//            }
            
            
            
        }

         


        $( window ).resize(function() {
            get_tools_content_height();
        });
        $(window).load(function () {
            $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
            $('.wpc-tools-wrap:first-child .wpc-tools-head').addClass('is-active');
            $('.wpc-tools-wrap:first-child .wpc-tools-head').parent().find('.wpc-tools-content').addClass('is-active');
            $('.wpc-tools-wrap:first-child .wpc-tools-head').parents('.wpc-editor-col').addClass('is-open');
            
            get_tools_content_height();
            var windowsize = $(window).width();
//            if (windowsize >= 768) {


//                $('#product-variation-scroll-wrap,.wpd-my-design-preview,.wpc-porto-skin .wpc-tab-content-wrapper > div:not(".wpd-group-child-wrap"),#wpd-cliparts-groups-parent-wrapper,#wpd-cliparts-groups-wrapper,#tab-clipart-upload-child,#wpc-cart-box,#tab-clipart-upload-child,.wpd-group-child-wrap .font-selector-container,#tab-upload #acd-uploaded-img,#tab-social-facebook .img-container,#tab-social-instagram .img-container').mCustomScrollbar({
//                    axis: "y",
//                    theme: "minimal-dark",
//                    scrollInertia: 300,
//                    advanced: {
//                        autoScrollOnFocus: false,
//                        updateOnContentResize: true
//                    }
//                });
                
                $('.wpc-porto-skin .wpd-tools-content-scroll-wrap,.wpc-porto-skin.wpc-container .wpd-tab-content-main').mCustomScrollbar({
                    axis: "y",
                    theme: "minimal-dark",
                    scrollInertia: 300,
                    advanced: {
                        autoScrollOnFocus: false,
                        updateOnContentResize: true
                    }
                 });      
                
                
                
                
//            }
            
            $('#debug').bind('DOMNodeInserted', function(e){
                //$('#add-cart-wrap #add-cart-content').removeClass('is-open');
                $(this).parents('#debug-wrap').addClass('is-open');
                if($('#debug').is(':empty')){
                $(this).parents('#debug-wrap').removeClass('is-open');
                }
                else{
                    //$('#add-cart-wrap #add-cart-content').removeClass('is-open');
                    $(this).parents('#debug-wrap').addClass('is-open');
                }
            });
            
            $(document).on('click touchstart', '#debug-wrap.is-open .debug-icon', function () {
                $(this).parents('#debug-wrap').removeClass('is-open');
            });


           
            wp.hooks.addAction('WPD_EDITOR.after_adding_text_on_canvas', wpd_active_added_text);
            function wpd_active_added_text(text,wpd_canvas){
                wpd_canvas.setActiveObject(text);
            }
            //wp.hooks.doAction('WPD_EDITOR.after_adding_shape_on_canvas');
            wp.hooks.addAction('WPD_EDITOR.after_adding_shape_on_canvas', wpd_active_added_shape);
            function wpd_active_added_shape(shape,wpd_canvas){
                wpd_canvas.setActiveObject(shape);
            }
            
            $(document).on('touchstart click', '.wpd-number-input button.minus', function () {
                var recup_val = $(this).parent('.wpd-number-input').find("input[type=number]").val();
                var recup_min = $(this).parent('.wpd-number-input').find("input[type=number]").attr('min');
                var recup_step = $(this).parent('.wpd-number-input').find("input[type=number]").attr('step');
                var recup_opacity = $(this).parent('.wpd-number-input').find("input[type=number]").data('opacity');
                if(recup_opacity !== true){
                    var new_val= parseInt(recup_val) - parseInt(recup_step);
                }
                else{
                   var new_val= (parseFloat(recup_val) - parseFloat(recup_step)).toFixed(1); 
                   var recup_min =0.0;
                }
                if(new_val >= recup_min){
                    if(recup_opacity === true && new_val == recup_min){
                       var new_val= 0; 
                    }
                    $(this).parent('.wpd-number-input').find("input[type=number]").val(new_val);
                }
                $(this).parent('.wpd-number-input').find("input[type=number]").trigger('change');
            });
        
            $(document).on('touchstart click', '.wpd-number-input button.plus', function () {
                var recup_val = $(this).parent('.wpd-number-input').find("input[type=number]").val();
                var recup_max = $(this).parent('.wpd-number-input').find("input[type=number]").attr('max');
                var recup_step = $(this).parent('.wpd-number-input').find("input[type=number]").attr('step');
                var recup_opacity = $(this).parent('.wpd-number-input').find("input[type=number]").data('opacity');
                if(recup_opacity !== true){
                    var new_val= parseInt(recup_val) + parseInt(recup_step);
                }
                else{
                    var new_val= (parseFloat(recup_val) + parseFloat(recup_step)).toFixed(1); 
                    var recup_max =1.0;
                }
                if(new_val <= recup_max){
                    if(recup_opacity === true && new_val == recup_max){
                       var new_val= 1; 
                    }
                    $(this).parent('.wpd-number-input').find("input[type=number]").val(new_val);
                }
                $(this).parent('.wpd-number-input').find('input[type=number]').trigger('change');
            });

            $(document).on('touchstart click', '#tab-saved-design .wpc_order_item', function () {
                var variation_id = $(this).attr("data-item");
                var save_time = $(this).attr("data-save-time");
                
                var open = $(this).parents('.wpc-tab-content-wrapper').find('.wpd-group-child-wrap[data-variation-id = "' + variation_id + '"][data-save-time = "' + save_time + '"]');
                open.addClass('is-open');
            });


            $(document).on('touchstart click', '.wpd-child-wrap-close', function () {
                $(this).parents('.wpd-group-child-wrap').removeClass('is-open');
            });
            $(document).on('touchstart click', '.wpd-parent-wrap-close', function () {
                $(this).parents('.wpd-group-parent-child-wrap').removeClass('is-open');
            });

            wp.hooks.addAction('WPD_EDITOR.object_deselected', wpd_object_deselected);
            function wpd_object_deselected(options,image_type){
                var wpd_object_type= options.target.type;
                
                $('#wpd-text-align-row').addClass('wpd-hide-row');
                $('#copy_paste_btn,#align_btn,#clear_all_btn,#delete_btn').addClass('wpd_btn_hidden');
                $('.wpc-button-bar .align_btn_wrap').removeClass("is-open");

                if(wpd_object_type == "i-text" || (wpd_object_type == "group") && options.target.get("originalText") ){
                    $('.wpd-font-view').html('Hello');
                }
                
                if(wpd_object_type == "path-group" || wpd_object_type == "image"){
                    $('#wpd-filters-tools-wrap').removeClass('is-active');
                }
                if (image_type === "cliparts-panel") {
                    $('#wpd-cliparts-filters-wrap').removeClass('is-open');
                }
                else if(image_type === "uploads-panel"){
                    $('#wpd-uploads-filters-wrap').removeClass('is-open');
                }
                
            }
            wp.hooks.addAction('WPD_EDITOR.image_selected', wpd_open_clipart_editor);
            function wpd_open_clipart_editor (target, image_type){
                //$('.wpc-tools-content').removeClass('is-active');
                $('#wpd-filters-tools-wrap').addClass('is-active');
                $('#wpd-uploads-filters-wrap').addClass('is-open');
                
                
            }
            wp.hooks.addAction('WPD_EDITOR.object_selected', wpd_any_object_selected);
            function wpd_any_object_selected(options){
                $(".wpc-button-bar > .wpd_btn_hidden").removeClass('wpd_btn_hidden');
            }
//            
//            
            
            
      });  
           
    });
    
})(jQuery);

