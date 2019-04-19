# Paypal
💳 Lightweight PHP paypal library for creating and handling payments

# Installation
```
composer require rixafy/paypal
```

# Example usage

## Create payment URL for user

```php
$paypalBuilder = new PaypalLinkBuilder();

$paypalBuilder->setIsShoppingCart(true);
$paypalBuilder->setAccount('yourEmail@gmail.com');
$paypalBuilder->setCurrencyCode('EUR');
$paypalBuilder->setCallBack('https://api.yoursite.com/ipn-receiver');
$paypalBuilder->setLanguage('en_US');
$paypalBuilder->setCustom('customValue'); // should be payment id
$paypalBuilder->setImage('https://example.com/image.png');
$paypalBuilder->setStoreInfo('YourBusinessName', 'BuyNow', 'WPS', 'US');
$paypalBuilder->addItem("Product 1", 5, 10); // qty 5, price 10
$paypalBuilder->addItem("Product 2", 1, 15); // qty 1, price 15
$paypalBuilder->setCustomParameter(0, 'Order ID:', 1885); // up to 6 custom parameters

echo 'Paypal URL is ' . $paypalBuilder; // redirect user to this URL
```

## Accept IPN request from paypal

```php
$paypal = new Paypal(false); // debug mode = false

try {
  $paypalData = $paypal->verifyRequest($postData);

} catch (PaypalValidationException $e) {
  // payment error, invalid payment or some problem with transaction

} catch (PaypalRequestException $e) {
  // request is not from paypal domain, probably fake
}

$paymentId = $paypalData->getCustom(); // customValue
```
