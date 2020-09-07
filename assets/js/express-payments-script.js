(function ($) {
    $(document).ready(function () {
        $('input[name=extra_product_name], input[name=extra_product_price]').on('focusin focusout', function() {
            
            var varPass = 0;
            
          $('#express_order').attr("disabled","disabled");
            $(".rpdo-error").hide();
            $(".rpdo_loader").show();
            var epp = jQuery('#extra_product_price').val();
            var epn = jQuery('#extra_product_name').val();
            if(epn == "") {
                $(".rpdo-error").html("Please enter valid value.").show();
                return false;
            }
            if (isNaN(epp) === true) {
                $(".rpdo-error").html("Please enter valid value.").show();
                return false;
            }
            if ((isNaN(epp) === true) && (epn == "")) {}else{
            varPass++;
            var varAname = epn;
            var varAamount = (parseFloat(epp));
            var isDecimal = (varAamount - Math.floor(varAamount)) !== 0;
            var varAamountFix;
            if (isDecimal){
              varAamountFix = varAamount.toFixed(2);
            }else{
              varAamountFix = varAamount;
            };
        }


            var infotxt = '';
infotxt += '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="2px;" style="border: none !important;padding: 0;"></td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';

infotxt += '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 10px 0 0 0;"><h1 style="border: none !important;width:100%;margin:0;padding: 0 5px 0 0;line-height: 1;"><strong>'+varAname+'</strong></h1></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 10px 0 0 0;"><span style="border: none !important;margin:0;padding:0 15px 0 0;line-height: 1 !important;">Total Amount </span><span style="border: none !important;margin:0;padding:0;line-height: 1 !important;"><strong>'+currency_format(varAamountFix)+'</strong></span></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';

infotxt += '</td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';


            if(varAname){
              $('#gbprimepay_express-info').html(infotxt);
              $('#express_order').removeAttr("disabled");
              setTimeout(function(){  

              $.ajax({
                  url: express_ajaxurl,
                  type: "POST",
                  data: {
                    action: 'gbp_express_pay',
                    'amount' : jQuery('#extra_product_price').val(),
                    'aname' : jQuery('#extra_product_name').val() },
                  success: function (result) {
                      $(".rpdo_loader").hide();
                      if ($("[name='update_cart']").length > 0) {
                          $("[name='update_cart']").trigger("click");
                      }
                      
                      $('body').trigger('update_checkout');
                      if($(".donate-remove").length>0){
                      }
                      
                      return false;
                  }
              });
            }, 1000);

              
            }else{
              $('#gbprimepay_express-info').html('');
            }
        });
        $('input[name=extra_product_price]').mouseleave(function(){
            
          var varPass = 0;
          
        $('#express_order').attr("disabled","disabled");
          $(".rpdo-error").hide();
          $(".rpdo_loader").show();
          var epp = jQuery('#extra_product_price').val();
          var epn = jQuery('#extra_product_name').val();
          if(epn == "") {
              $(".rpdo-error").html("Please enter valid value.").show();
              return false;
          }
          if (isNaN(epp) === true) {
              $(".rpdo-error").html("Please enter valid value.").show();
              return false;
          }
          if ((isNaN(epp) === true) && (epn == "")) {}else{
          varPass++;
          var varAname = epn;
          var varAamount = (parseFloat(epp));
          var isDecimal = (varAamount - Math.floor(varAamount)) !== 0;
          var varAamountFix;
          if (isDecimal){
            varAamountFix = varAamount.toFixed(2);
          }else{
            varAamountFix = varAamount;
          };
      }


          var infotxt = '';
infotxt += '<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="2px;" style="border: none !important;padding: 0;"></td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';

infotxt += '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 0;line-height: 1 !important;">';
infotxt += '<tbody>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 10px 0 0 0;"><h1 style="border: none !important;width:100%;margin:0;padding: 0 5px 0 0;line-height: 1;"><strong>'+varAname+'</strong></h1></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;text-align: center; vertical-align: middle;margin:0;padding: 10px 0 0 0;"><span style="border: none !important;margin:0;padding:0 15px 0 0;line-height: 1 !important;">Total Amount </span><span style="border: none !important;margin:0;padding:0;line-height: 1 !important;"><strong>'+currency_format(varAamountFix)+'</strong></span></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';

infotxt += '</td>';
infotxt += '<td cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '<tr cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;">';
infotxt += '<td colspan="3" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border: none !important;padding: 0;"></td>';
infotxt += '</tr>';
infotxt += '</tbody>';
infotxt += '</table>';


          if(varAname){
            $('#gbprimepay_express-info').html(infotxt);
            $('#express_order').removeAttr("disabled");
            setTimeout(function(){  

            $.ajax({
                url: express_ajaxurl,
                type: "POST",
                data: {
                  action: 'gbp_express_pay',
                  'amount' : jQuery('#extra_product_price').val(),
                  'aname' : jQuery('#extra_product_name').val() },
                success: function (result) {
                    $(".rpdo_loader").hide();
                    if ($("[name='update_cart']").length > 0) {
                        $("[name='update_cart']").trigger("click");
                    }
                    
                    $('body').trigger('update_checkout');
                    if($(".donate-remove").length>0){
                    }
                    
                    return false;
                }
            });
          }, 1200);

            
          }else{
            $('#gbprimepay_express-info').html('');
          }
      });
      
        function currency_format(num) {
            return '' + num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
          }
    });
})(jQuery);