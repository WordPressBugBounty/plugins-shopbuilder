!function(){var t;(t=jQuery).fn.rtsb_quick_view=function(){var o=new RtsbModal({footer:!1,header:!0,title:!1,wrapClass:"quick-view-modal",maxWidth:QvModalParams.modal_width>0?QvModalParams.modal_width:950});t(document).off("click",".rtsb-quick-view-btn").on("click",".rtsb-quick-view-btn",(function(a){a.preventDefault(),a.stopPropagation();var e=t(this),i=e.data("product_id");e.data("title"),i?t.ajax({url:rtsbQvParams.ajaxurl,data:{action:"rtsb_load_product_quick_view",product_id:i,lang:rtsbQvParams.lang},dataType:"json",type:"POST",beforeSend:function(){o.addModal().addLoading()},success:function(a){o.body.addClass("woocommerce single-product"),o.addTitle(""),o.content(a.data.html);var e=o.body.find(".variations_form");e.each((function(){t(this).wc_variation_form()})),e.trigger("check_variations"),e.trigger("reset_image"),void 0!==t.fn.wc_product_gallery&&o.body.find(".woocommerce-product-gallery").each((function(){t(this).wc_product_gallery()})),o.body.find(".elementor-element")&&o.body.addClass("rtsb-build-with-elementor"),t(document).trigger("rtsbQv.success"),setTimeout((function(){o.removeLoading()}),150)},fail:function(){t(document).trigger("rtsbQv.error")},always:function(){t(document).trigger("rtsbQv.loaded")}}):console.log("No product selected")}))},t(document).on("yith_infs_adding_elem yith-wcan-ajax-filtered",(function(){t.fn.rtsb_quick_view()})),t((function(){t.fn.rtsb_quick_view()}))}();