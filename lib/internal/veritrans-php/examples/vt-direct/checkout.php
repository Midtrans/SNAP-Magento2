<?php
require_once(dirname(__FILE__) . '/../../Veritrans.php');

// YOUR CLIENT KEY
// can find in Merchant Portal -> Settings -> Access keys
Veritrans_Config::$clientKey = "<your client key>";

if (Veritrans_Config::$clientKey == "<your client key>") {
  echo "<p style='background: #FFB588; padding: 10px;'>";
  echo "Please set your client key in file " . __FILE__;
  echo "</p>";
}
?>
<html>

<head>
  <title>Checkout</title>
  <link rel="stylesheet" href="jquery.fancybox.css">
</head>

<body>
  <script type="text/javascript" src="https://api.sandbox.veritrans.co.id/v2/assets/js/veritrans.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="jquery.fancybox.pack.js"></script>

  <h1>Checkout</h1>
  <form action="checkout-process.php" method="POST" id="payment-form">
    <fieldset>
      <legend>Checkout</legend>
      <p>
        <label>Card Number</label>
        <input class="card-number" value="4011 1111 1111 1112" size="23" type="text" autocomplete="off" />
      </p>
      <p>
        <label>Expiration (MM/YYYY)</label>
        <input class="card-expiry-month" value="12" placeholder="MM" size="2" type="text" />
        <span> / </span>
        <input class="card-expiry-year" value="2020" placeholder="YYYY" size="4" type="text" />
      </p>
      <p>
        <label>CVV</label>
        <input class="card-cvv" value="123" size="4" type="password" autocomplete="off" />
      </p>

      <p>
        <label>3D Secure</label>
        <input type="checkbox" name="secure" value="true">
      </p>

      <p>
        <label>Save credit card</label>
        <input type="checkbox" name="save_cc" value="true">
      </p>

      <input id="token_id" name="token_id" type="hidden" />
      <button class="submit-button" type="submit">Submit Payment</button>
    </fieldset>
  </form>

  <code>
    <pre>
  <b>Testing cards:</b>

  Visa success      4011 1111 1111 1112
  Visa challenge    4111 1111 1111 1111
  Visa deny by FDS  4211 1111 1111 1110

  MasterCard success      5481 1611 1111 1081
  MasterCard challenge    5110 1111 1111 1119
  MasterCard deny by FDS  5210 1111 1111 1118

  <b>3D Secure:</b>

  Visa        4811 1111 1111 1114
  MasterCard  5211 1111 1111 1117
    </pre>
  </code>

  <!-- Javascript for token generation -->
  <script type="text/javascript">
    $(function () {
      // Sandbox URL
      Veritrans.url = "https://api.sandbox.veritrans.co.id/v2/token";
      // TODO: Change with your client key.
      Veritrans.client_key = "<?php echo Veritrans_Config::$clientKey ?>";
      var card = function () {
        return {
          "card_number": $(".card-number").val(),
          "card_exp_month": $(".card-expiry-month").val(),
          "card_exp_year": $(".card-expiry-year").val(),
          "card_cvv": $(".card-cvv").val(),
          "secure": $('[name=secure]')[0].checked,
          "gross_amount": 200000
        }
      };

      function callback(response) {
        console.log(response);
        if (response.redirect_url) {
          console.log("3D SECURE");
          // 3D Secure transaction, please open this popup
          openDialog(response.redirect_url);

        }
        else if (response.status_code == "200") {
          console.log("NOT 3-D SECURE");
          // Success 3-D Secure or success normal
          closeDialog();
          // Submit form
          $("#token_id").val(response.token_id);
          $("#payment-form").submit();
        }
        else {
          // Failed request token
          console.log(response.status_code);
          alert(response.status_message);
          $('button').removeAttr("disabled");
        }
      }

      function openDialog(url) {
        $.fancybox.open({
          href: url,
          type: "iframe",
          autoSize: false,
          width: 700,
          height: 500,
          closeBtn: false,
          modal: true
        });
      }

      function closeDialog() {
        $.fancybox.close();
      }

      $(".submit-button").click(function (event) {
        console.log("SUBMIT");
        event.preventDefault();
        $(this).attr("disabled", "disabled");
        Veritrans.token(card, callback);
        return false;
      });
    });
  </script>
</body>

</html>