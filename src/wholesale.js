'use strict';

var $ = window.jQuery;

if (isProduction) {
  var stripeApiKey = 'pk_live_z1b82Qu7RdTwWiygHLRAlTCt';
} else {
  var stripeApiKey = 'pk_test_QWW4kznLc6joeLUta22RLOk0';
}

$(document).ready(function() {
  var Elm = require('./Wholesale.elm');
  var node = document.getElementById('elm-wholesale');
  if (node) {
    var app = Elm.Wholesale.embed(node);

    var stripeHandler = StripeCheckout.configure({
      key: stripeApiKey,
      locale: 'auto',
      name: 'Fellowship for Intentional Community',
      image: '/wp-content/themes/fic-theme/img/logo-large-fic.png',
      zipCode: true,
      token: function(token, args) {
        /** Stripe Subscription **/
        app.ports.stripeTokenReceived.send({
          token: token.id,
          checkoutArgs: args,
        });
      }
    });

    /* Open Stripe Checkout Popup */
    app.ports.collectStripeToken.subscribe(function(portData) {
      var customerEmail = portData[0];
      var checkoutTotal = portData[1];
      stripeHandler.open({
        amount: checkoutTotal,
        email: customerEmail,
        billingAddress: true,
        shippingAddress: true,
      });
      window.addEventListener('popstate', function() {
        stripeHandler.close();
      });
    });
  }
});
