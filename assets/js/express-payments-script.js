(function ($) {
    $(document).ready(function () {
        $('input[name=extra_product_name], input[name=extra_product_price]').on('change focusout', function() {
          $('#express_order').attr("disabled","disabled");
            $(".rpdo-error").hide();
            $(".rpdo_loader").show();   
              $.ajax({
                  url: express_ajaxurl,
                  type: "POST",
                  data: {
                    action: 'gbp_express_pay',
                    'amount' : jQuery('#extra_product_price').val(),
                    'aname' : jQuery('#extra_product_name').val() },
                    dataType: 'json',
                  success: function (result) {
                      $(".rpdo_loader").hide();
                      $('body').trigger('update_checkout');

                      if ($("[name='update_cart']").length > 0) {
                          $("[name='update_cart']").trigger("click");
                      }     
                      if($(".donate-remove").length>0){
                      }
                      if(result.info_display){                  
                        setTimeout(function(){  
                          $('#gbprimepay_express-info').html(result.info_display);
                          $('body').trigger('wc_fragment_refresh');
                        }, 700);
                      }
                      if ($("[name='update_cart']").length > 0) {
                          $("[name='update_cart']").trigger("click");
                      }
                      
  if ($("input[type=radio][name='payment_method'][value='gbprimepay_installment']").length > 0) {
    if ($("input[type=radio][name='payment_method'][value='gbprimepay_installment']").prop('checked') == true) {
        $("#payment .payment_methods .payment_method_gbprimepay_installment label").trigger('click');
        $('select[name=gbprimepay_installment-bankcode]').val($("select[name=gbprimepay_installment-bankcode] option:first").val()).change();
        $('body').trigger('payment_method_selected');    
        setTimeout(function(){  
        if($('select[name=gbprimepay_installment-bankcode]').find('option').length < 3){
          $("#payment .payment_methods .payment_method_gbprimepay_installment label").trigger('click');
          }
        }, 600);
    }
  }                 
                      return false;
                  }
              });
        

          
        });
    });

                          

jQuery('body').on('payment_method_selected', function(){
  if ($('#wc-gbprimepay-payment-token-new').length) {
    if ($('#wc-gbprimepay-payment-token-new').prop("checked") == false) {
      $('#wc-gbprimepay-payment-token-new').attr('checked', true);
    }
  }
});

window.addEventListener('DOMContentLoaded', function(){
var $referrer = document.referrer.split('/').filter(Boolean).pop();
var $current = window.location.href.split('/').filter(Boolean).pop();
if($referrer=="express-payments"){
  if($current!=="express-payments"){
    $('body').trigger('wc_fragment_refresh');
  }
}

});
})(jQuery);