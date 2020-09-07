(function ($) {
    $(document).ready(function () {
        $(".donate-btn").click(function () {
            $(".rpdo-error").hide();
            $(".rpdo_loader").show();
            var ele = $(this).parent();
            var amt = ele.find('.extra_product_price').val();
            if (isNaN(amt) === true) {
                $(".rpdo-error").html("Please enter valid value.").show();
                return false;
            }

            $.ajax({
                url: express_ajaxurl,
                type: "POST",
                data: {amount: amt, action: 'gbp_express_pay'},
                success: function (result) {
                    $(".rpdo_loader").hide();
                    if ($("[name='update_cart']").length > 0) {
                        $("[name='update_cart']").removeAttr("disabled");
                        $("[name='update_cart']").trigger("click");
                    }
                    
                    $('body').trigger('update_checkout');
                    if($(".donate-remove").length>0){
                        $(".donate-remove").addClass("show_remove");
                    }
                    
                    return false;
                }
            });
        });
        $(".donate-remove").click(function () {
            $(".rpdo_loader").show();
            var amt = 0;
            $.ajax({
                url: express_ajaxurl,
                type: "POST",
                data: {amount: 0, action: 'gbp_express_pay'},
                success: function (result) {
                    $(".rpdo_loader").hide();
                    if ($("[name='update_cart']").length > 0) {
                        $("[name='update_cart']").removeAttr("disabled");
                        $("[name='update_cart']").trigger("click");
                    }
                    if($(".donate-remove").length>0){
                        $(".donate-remove").removeClass("show_remove");
                    }
                    
                    $('body').trigger('update_checkout');
                    return false;
                }
            });
        });
    });
})(jQuery);