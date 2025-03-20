(function ($) {
    "use strict";
    $(document).ready(function () {
        $(document).on('click', '#font-family-selector > div', function () {
            var vpc_get_font_selected = $(this).attr('style').replace('font-family:', '');
            $(this).parent('#font-family-selector').attr('value',vpc_get_font_selected);
            $(this).parent('#font-family-selector').trigger('change');
            $('#text-panel-content').removeClass('is-active');
             $('.wpc-tools-head').css('transform','');
        });

       
        
        $('ul.wpd-tabs li').click(function(){
            var wpd_tab_id = $(this).attr('data-tab');

            $('ul.wpd-tabs li').removeClass('current');
            $('.wpd-tab-content').removeClass('current');

            $(this).addClass('current');
            $("#"+wpd_tab_id).addClass('current');
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
            $(".wpd-edit-object").remove();
        }
        wpc_slider_nav();
        $(document).on('click','#product-part-slider > span', function () {
            var $next, $selected = $(".active");
            $next = $selected.next('li').length ? $selected.next('li') : $first;
            $selected.removeClass("active");
            $next.addClass('active');
            var $this =$('#wpc-parts-bar li.active');
            $this.trigger("click");
            wpc_slider_nav();
        });
        $(document).on('click','#wpc-parts-bar > li', function () {
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
           // $('#text-panel').trigger('click');
           // $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
           //  $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
           //  $('#text-panel').addClass('is-active');
           //  $('#text-panel').parent().find('.wpc-tools-content').addClass('is-active');
           //  $('#text-panel').parents('.wpc-editor-col').addClass('is-open');
           //  get_tools_content_height();
        }
        $(document).on('click', '#wpc-edit-text', function () {
            //$('#text-panel').removeClass('is-active');
            $('.wpc-tools-wrap[data-tools-head="text-panel"] .wpc-tools-content').removeClass('is-active');
            $('#text-panel').css('transform','scale(1)');
            //$('#text-panel').parents('.wpc-editor-col').removeClass('is-open');
        });
        $(document).on('click', '#text-panel', function () {
            $('#new-text').val('');
            $('#wpc-add-text').removeClass('wpd-hidden');
            $('#wpc-edit-text,#wpc-text-tabs,#text-tool-container').addClass('wpd-hidden');
            
        });
        
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
        
//        $(document).on('touchstart click', '.wpc-tools-head', function () {
//            $(this).css('transform','scale(0)');
//            WPD_EDITOR.canvas.discardActiveObject();
//            $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
//            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
//           // $(this).addClass('is-active');
//           var get_tools_head = $(this).attr('id');
//            var get_tools_content_selector = $('.wpc-tools-wrap[data-tools-head="'+ get_tools_head +'"] .wpc-tools-content');
//            //$(this).parent().find('.wpc-tools-content').addClass('is-active');
//            $(get_tools_content_selector).addClass('is-active');
//            //$(this).parents('.wpc-editor-col').addClass('is-open');
//            get_tools_content_height();
//        });
    
        $('.wpc-tools-head').doubletap(function(e) {
            var id= e.target.id;
            var $this = $("#" + id).parent('.wpc-tools-head');
            $($this).css('transform','scale(0)');
            WPD_EDITOR.canvas.discardActiveObject();
            $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
           // $(this).addClass('is-active');
           var get_tools_head = $($this).attr('id');
            var get_tools_content_selector = $('.wpc-tools-wrap[data-tools-head="'+ get_tools_head +'"] .wpc-tools-content');
            //$(this).parent().find('.wpc-tools-content').addClass('is-active');
            $(get_tools_content_selector).addClass('is-active');
            //$(this).parents('.wpc-editor-col').addClass('is-open');
            get_tools_content_height();
        });
        
        $(document).on('touchstart click', '.wpc-tools-close', function () {
            $('.wpc-tools-head').css('transform','');
            $(this).parents('.wpc-tools-content').removeClass('is-active');
            //$(this).parents('.wpc-tools-wrap').find('.wpc-tools-head').removeClass('is-active');
            //$(this).parents('.wpc-editor-col').removeClass('is-open');
            //$(".wpd-edit-object").remove(); 
            
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
            var tools_panel = screen.height - porto_header_height - wpadminbar - 74 ;
            var tools_panel_content = tools_panel - panel_head - 20;
           
            
            var wpc_container_height = $(window).outerHeight() - porto_header_height - wpadminbar  ;
            var tools_content_height = wpc_container_height- panel_head ;
            var canvas_height = $(window).outerHeight() - porto_header_height - wpadminbar  - $('.wpd-responsive-toolbar-box').outerHeight() - $('#product-part-container').outerHeight() - 20;
            var tools_content_top = porto_header_height + wpadminbar2 ;
            var windowsize = $(window).width();
            
            var related_scroll_wrap = $('#related-products-panel-content');
            var related_scroll_height= $(window).outerHeight() - related_scroll_wrap.find('.wpc-panel-head').outerHeight();
            related_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': related_scroll_height});
            
            var text_scroll_wrap = $('#text-panel-content');
            var text_scroll_height = $(window).outerHeight() - text_scroll_wrap.find('.wpc-panel-head').outerHeight(); 
            text_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': text_scroll_height});
            
            var clipart_scroll_wrap = $('#cliparts-panel-content');
            var clipart_scroll_height = $(window).outerHeight() - clipart_scroll_wrap.find('#wpd-clipart-group-container .wpc-panel-head').outerHeight();
            clipart_scroll_wrap.find('#wpd-cliparts-groups-wrapper.wpd-tools-content-scroll-wrap').css({'max-height': clipart_scroll_height});
            
            var clipart_upload_scroll_height = $(window).outerHeight() - clipart_scroll_wrap.find('#cliparts-panel-content-child .wpc-panel-head').outerHeight();
            clipart_scroll_wrap.find('#tab-clipart-upload-child.wpd-tools-content-scroll-wrap').css({'max-height': clipart_upload_scroll_height});
            
            var image_filters_wrap = $('#wpd-filters-tools-wrap');
            var image_filters_scroll_height = $(window).outerHeight() - image_filters_wrap.find('#wpd-uploads-filters-wrap .wpc-panel-head').outerHeight();
            image_filters_wrap.find('#wpd-uploads-filters-wrap .wpd-tools-content-scroll-wrap').css({'max-height': image_filters_scroll_height});
            
            var cart_scroll_wrap = $('#add-cart-panel-content');
            var cart_scroll_height= $(window).outerHeight() - cart_scroll_wrap.find('.wpc-panel-head').outerHeight() - cart_scroll_wrap.find('#add-to-cart-btn').outerHeight();
            cart_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': cart_scroll_height});
            
            var design_scroll_wrap = $('#user-designs-panel-content');
            var design_scroll_height = $(window).outerHeight() - design_scroll_wrap.find('.wpc-panel-head').outerHeight();
            design_scroll_wrap.find('.wpd-tools-content-scroll-wrap').css({'max-height': design_scroll_height});
            
            var design_content_scroll_height = $(window).outerHeight() - design_scroll_wrap.find('.wpc-panel-head').outerHeight() - design_scroll_wrap.find('.wpd-child-wrap-close').outerHeight() - 60;
            design_scroll_wrap.find('.wpd-my-design-preview.wpd-tools-content-scroll-wrap').css({'max-height': design_content_scroll_height});
            
            var upload_scroll_wrap = $('#uploads-panel-content');
            var upload_scroll_height= $(window).outerHeight() - upload_scroll_wrap.find('.wpc-panel-head').outerHeight() - upload_scroll_wrap.find('#userfile_upload_form').outerHeight();
            upload_scroll_wrap.find('#tab-upload .wpd-tools-content-scroll-wrap').css({'max-height': upload_scroll_height});
            
            var upload_social_scroll_height = $(window).outerHeight() - upload_scroll_wrap.find('.wpc-panel-head').outerHeight() - upload_scroll_wrap.find('.wpc-rs-app').outerHeight();
            upload_scroll_wrap.find('#tab-social-facebook .wpd-tools-content-scroll-wrap,#tab-social-instagram .wpd-tools-content-scroll-wrap').css({'max-height': upload_social_scroll_height});
            
            // if (windowsize >= 768) {

                // $('.wpc-porto-skin .wpc-tab-content-wrapper > div:not(".wpd-group-child-wrap"), #wpd-cliparts-groups-parent-wrapper,#wpd-cliparts-groups-wrapper,#tab-clipart-upload-child,#wpd-team-and-number-wrapper,#wpc-cart-box').css({'height': tools_panel_content, 'overflow-y':'scroll'});
                // $('#tab-clipart-upload-child').css({'height': tools_panel_content - 30, 'overflow-y':'scroll'});
                // $('.wpd-group-child-wrap .font-selector-container').css({'height': tools_panel_content - 50, 'overflow-y':'scroll'});
                // $('#tab-upload #acd-uploaded-img,#tab-social-facebook .img-container,#tab-social-instagram .img-container').css({'height': tools_panel_content - 140, 'overflow-y':'scroll'});
                // $('.wpc-porto-skin.wpc-container .wpd-tab-content').css({'height': tools_panel,'max-height': tools_panel, 'overflow-y' : 'scroll'});
           
                // $('.wpc-porto-skin .wpc-tab-content-wrapper > div:not(".wpd-group-child-wrap"), #wpd-cliparts-groups-parent-wrapper,#wpd-cliparts-groups-wrapper,#tab-clipart-upload-child,#wpd-team-and-number-wrapper,#wpc-cart-box').css({'height': tools_panel_content});
                // $('#tab-clipart-upload-child').css({'height': tools_panel_content - 30});
                // $('.wpd-group-child-wrap .font-selector-container').css({'height': tools_panel_content - 50});
                // $('#tab-upload #acd-uploaded-img,#tab-social-facebook .img-container,#tab-social-instagram .img-container').css({'height': tools_panel_content - 140});
                // $('.wpc-porto-skin.wpc-container .wpd-tab-content').css({'height': tools_panel,'max-height': tools_panel});
            // }
        }

//        $(".wpc-porto-skin #add-cart-content.is-open > div").mCustomScrollbar({
//            axis: "y",
//            theme: "minimal-dark",
//            scrollInertia: 300,
//            advanced: {
//                autoScrollOnFocus: false,
//                updateOnContentResize: true
//            }
//        });
        $( window ).resize(function() {
            get_tools_content_height();
        });
        $(window).load(function () {
            $('.wpc-porto-skin .wpc-tools-head').removeClass('is-active');
            $('.wpc-porto-skin .wpc-tools-content').removeClass('is-active'); 
            // $('.wpc-tools-wrap:first-child .wpc-tools-head').addClass('is-active');
            // $('.wpc-tools-wrap:first-child .wpc-tools-head').parent().find('.wpc-tools-content').addClass('is-active');
            // $('.wpc-tools-wrap:first-child .wpc-tools-head').parents('.wpc-editor-col').addClass('is-open');
            
            get_tools_content_height();
            var windowsize = $(window).width();
            //if (windowsize >= 768) {
               // alert('toto');
                
                // $(".wpc-porto-skin .wpc-tools-content > div:last-child,.wpc-porto-skin.wpc-container .wpd-tab-content").mCustomScrollbar({
                // axis: "y",
                // theme: "minimal-dark",
                // scrollInertia: 300,
                // advanced: {
                //     autoScrollOnFocus: false,
                //     updateOnContentResize: true
                // }
                // });

                // $(".wpc-porto-skin .wpc-tools-content > div:not('.wpd-uploads-editing-wrap,#debug-wrap'):last-child,.wpc-porto-skin.wpc-container .wpd-tab-content").mCustomScrollbar({
                // axis: "y",
                // theme: "minimal-dark",
                // scrollInertia: 300,
                // advanced: {
                //     autoScrollOnFocus: false,
                //     updateOnContentResize: true
                // }
                // });

                // $('.wpd-team-name-and-number-row-contianer,.wpc-porto-skin .wpc-tab-content-wrapper > div:not(".wpd-group-child-wrap"),#wpd-cliparts-groups-parent-wrapper,#wpd-cliparts-groups-wrapper,#tab-clipart-upload-child,#wpd-team-and-number-wrapper,#wpc-cart-box,#tab-clipart-upload-child,.wpd-group-child-wrap .font-selector-container,#tab-upload #acd-uploaded-img,#tab-social-facebook .img-container,#tab-social-instagram .img-container,.wpc-porto-skin.wpc-container .wpd-tab-content').mCustomScrollbar({
                // axis: "y",
                // theme: "minimal-dark",
                // scrollInertia: 300,
                // advanced: {
                //     autoScrollOnFocus: false,
                //     updateOnContentResize: true
                // }
                // });  
                $('.wpc-porto-skin .wpd-tools-content-scroll-wrap').mCustomScrollbar({
                    axis: "y",
                    theme: "minimal-dark",
                    scrollInertia: 300,
                    advanced: {
                        autoScrollOnFocus: false,
                        updateOnContentResize: true
                    }
                 });                
            //}
            
            $('#debug').bind('DOMNodeInserted', function(e){
                $(this).parents('#debug-wrap').addClass('is-open');
                if($('#debug').is(':empty')){
                $(this).parents('#debug-wrap').removeClass('is-open');
                }
                else{
                    $(this).parents('#debug-wrap').addClass('is-open');
                }
            });
            
            $(document).on('click', '#debug-wrap.is-open .debug-icon', function () {
                $(this).parents('#debug-wrap').removeClass('is-open');
            });
           
            wp.hooks.addAction('WPD_EDITOR.after_adding_text_on_canvas', wpd_active_added_text);
            function wpd_active_added_text(text,wpd_canvas){
                wpd_canvas.setActiveObject(text);
                

            }

            
            $(document).on('click', '.wpd-number-input button.minus', function () {
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
                //console.log($(this).parent('.wpd-number-input').find("input[type=number]"));
            });
        
            $(document).on('click', '.wpd-number-input button.plus', function () {
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

//            $(document).on('click', '.wpd-font-group-wrap .wpd-font-group', function () {
//                $(this).parents('.wpc-tab-content-wrapper').find('.wpd-group-child-wrap').addClass('is-open');
//            });
//            $(document).on('click', '#tab-saved-design .wpc_order_item', function () {
//                $(this).parents('.wpc-tab-content-wrapper').find('.wpd-group-child-wrap').addClass('is-open');
//            });
            $(document).on('touchstart click', '#tab-saved-design .wpc_order_item', function () {
                //var order_item_id = $(this).attr("data-order-item-id");
                var variation_id = $(this).attr("data-item");
                var save_time = $(this).attr("data-save-time");

                var open = $(this).parents('.wpc-tab-content-wrapper').find('.wpd-group-child-wrap[data-variation-id = "' + variation_id + '"][data-save-time = "' + save_time + '"]');
                open.addClass('is-open');
                //$(this).parents('.wpc-tab-content-wrapper').find('.wpd-group-child-wrap').addClass('is-open');
            });


//            $(document).on('click', '.wpd-cliparts-groups-parent > li,#wpd-search-cliparts-group-parent-results > li', function () {
//                var group_parent_id = $(this).data("group-parent-id");
//                var group_parent_name= $(this).html();
//                $('#wpd-clipart-group-parent-selected').html(group_parent_name);
//                $(this).parents('.wpc-tools-content').find('.wpd-group-parent-child-wrap').addClass('is-open');
//
//            });
            $(document).on('click', '.wpd-child-wrap-close', function () {
                $(this).parents('.wpd-group-child-wrap').removeClass('is-open');
            });
//            $(document).on('click', '.wpd-parent-wrap-close', function () {
//                $(this).parents('.wpd-group-parent-child-wrap').removeClass('is-open');
//            });
//            $(document).on('click', '.wpd-clipart-edit-close', function () {
//                $(this).parents('#wpd-cliparts-filters-wrap').removeClass('is-open');
//            });
//            $(document).on('click', '.wpd-upload-edit-close', function () {
//                $(this).parents('#wpd-uploads-filters-wrap').removeClass('is-open');
//            });
            $(document).on('click', '.wpd-shape-edit-close', function () {
                $(this).parents('#wpd-shapes-filters-wrap').removeClass('is-open');
            });
            wp.hooks.addAction('WPD_EDITOR.object_deselected', wpd_object_deselected);
            function wpd_object_deselected(options,image_type){
                var wpd_object_type= options.target.type;

                $('#wpd-text-align-row').addClass('wpd-hide-row');
                $('.wpd-editor-bottom-tools-wrap').addClass('wpd_btn_hidden');
                $('.wpc-button-bar .align_btn_wrap').removeClass("is-open");

                if(wpd_object_type == "i-text" || (wpd_object_type == "group") && options.target.get("originalText") ){
                    $('.wpd-font-view').html('Hello');
                }
                

            }
            wp.hooks.addAction('WPD_EDITOR.after_click_clipart', wpd_after_click_clipart);
            function wpd_after_click_clipart(){
                $('.wpc-tools-head,.wpc-tools-content').removeClass('is-active');
                $('#cliparts-panel').css('transform','');
                $('.wpd-group-child-wrap').removeClass('is-open');
                $('.wpd-editor-bottom-tools-wrap').addClass('wpd_btn_hidden');
            }
            
            wp.hooks.addAction('WPD_EDITOR.after_adding_shape_on_canvas',wpd_after_adding_shape);
            function wpd_after_adding_shape(shape,wpd_canvas){
                $('.wpc-tools-head,.wpc-tools-content').removeClass('is-active');
                $('#shapes-panel').css('transform','');
                //$('.wpd-editor-bottom-tools-wrap').addClass('wpd_btn_hidden');
                wpd_canvas.setActiveObject(shape);
            }
            wp.hooks.addAction('WPD_EDITOR.object_selected', wpd_any_object_selected);
            function wpd_any_object_selected(options){
                $(".wpd-editor-bottom-tools-wrap").removeClass('wpd_btn_hidden');
                
            }
//            var selected_object = WPD_EDITOR.canvas.getActiveObject();
//            if(!selected_object){
//                $(".wpd-editor-bottom-tools-wrap").Class('wpd_btn_hidden');
//            }
            
            wp.hooks.addAction('WPD_EDITOR.after_adding_filter_on_image', wpd_after_adding_filter);
            function wpd_after_adding_filter(){
                $('#wpd-filters-tools-wrap').removeClass('is-active');
                //$('#wpd-uploads-filters-wrap').removeClass('is-open');
                $('.wpd-editor-bottom-tools-wrap').addClass('wpd_btn_hidden');
            }
//            wp.hooks.addAction('WPD_EDITOR.element_color_changed', wpd_after_color_changed);
//            function wpd_after_color_changed(id, selected_object, hex){
//                $('#wpd-filters-tools-wrap').removeClass('is-active');
//                $('#text-panel-content').removeClass('is-active');
//                $('#wpd-shapes-filters-wrap').removeClass('is-open');
//                $('#shapes-panel').css('transform','');
//                $('#text-panel').css('transform','');
//                $('.wpd-editor-bottom-tools-wrap').addClass('wpd_btn_hidden');
//                
//            }
            wp.hooks.addAction('WPD_EDITOR.after_adding_img_on_canvas', wpd_after_img_on_canvas);
            function wpd_after_img_on_canvas(img,wpd_canvas){
                $('#uploads-panel-content').removeClass('is-active');
                $('#uploads-panel').css('transform','');
                wpd_canvas.setActiveObject(img);
            }
            
      });  
      
      
    var mySwiper = new Swiper('.swiper-container', {
        slidesPerView: 4,
        //loop: true,
        spaceBetween: 40,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
      });
      
    });
    
})(jQuery);

