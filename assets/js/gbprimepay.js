jQuery(function($) {
    'use strict';

    // window.console.log('gbprimepay');
    // onject to handle GB Prime Pay
    var se_gbprimepay_form = {
        init: function() {
            if ($('form.woocommerce-checkout').length) {
                this.form = $('form.woocommerce-checkout');
            }
            $('form.woocommerce-checkout').on('submit', this.onSubmit);
        },

        isGbprimepayDefault: function() {
            if ($('#wc-gbprimepay-payment-token-new').length) {
                $('#wc-gbprimepay-payment-token-new').attr('checked', true).trigger('change');
            }
        },

        block: function() {
            se_gbprimepay_form.form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        isGbprimepayChosen: function() {
            return $( '#payment_method_gbprimepay' ).is( ':checked' );
        },

        unblock: function() {
            se_gbprimepay_form.form.unblock();
        },

        onSubmit: function(e) {
            if (se_gbprimepay_form.isGbprimepayChosen()) {
                e.preventDefault();
                // se_gbprimepay_form.block(); // block it !!!!!!

                var card = $('#gbprimepay-card-number').val().replace(/ /g,'');
                var expires = $('#gbprimepay-card-expiry').val();
                var cvc =  $('#gbprimepay-card-cvc').val();
                // window.console.log('gbprimepay-form-submit');

            }
        }
    };

    se_gbprimepay_form.isGbprimepayDefault();


// addDynamicEventListener(document.body, 'click', '.woocommerce-SavedPaymentMethods-new, li', function (e) {
//         if(e.target.id = 'payment_method_gbprimepay'){
//         if(e.target.checked = true){
//             se_gbprimepay_form.isGbprimepayDefault();
//             // $('#wc-gbprimepay-payment-token-new').attr('checked', true).trigger('change');
//     }}
// });

});
! function (e) {
	"use strict";
	Element.prototype.matches || (Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector || function (e) {
		for (var t = (this.document || this.ownerDocument).querySelectorAll(e), r = t.length; --r >= 0 && t.item(r) !== this;);
		return r > -1
	}), e.addDynamicEventListener = function (e, t, r, o, n) {
		e.addEventListener(t, function (e, t) {
			return function (r) {
				if (r.target && r.target.matches(e)) return r.delegatedTarget = r.target, void t.apply(this, arguments);
				var o = event.path || event.composedPath && event.composedPath();
				if (o)
					for (var n = 0; n < o.length; ++n) {
						var a = o[n];
						if (a.matches(e) && (r.delegatedTarget = a, t.apply(this, arguments)), a === r.currentTarget) return
					}
			}
		}(r, o), n)
	}
}(this);
jQuery(document.body).on('removed_from_cart updated_cart_totals', function () {
    // location.reload();
  window.console.log('');
  window.console.log('reload triggered');
  window.console.log('');
});

