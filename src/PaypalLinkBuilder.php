<?php

declare(strict_types=1);

namespace Rixafy\Paypal;

class PaypalLinkBuilder
{
    /** @var string */
    private $baseUrl;

    /** @var array */
    private $parameters = [];

    /** @var bool */
    private $isShoppingCart;

    /** @var int */
    private $itemIndex = 1;

    /** @var int */
    private $customOptions = 0;

    public function __construct($baseUrl = 'https://www.paypal.com/cgi-bin/webscr?', $isShoppingCart = false)
    {
        $this->baseUrl = $baseUrl;
        $this->isShoppingCart = $isShoppingCart;

        if ($isShoppingCart) {
            $this->parameters['cmd'] = '_cart';

        } else {
            $this->parameters['cmd'] = '_xclick';
        }

        $this->parameters['currency_code'] = 'USD';
        $this->parameters['no_shipping'] = true;
        $this->parameters['no_note'] = true;
        $this->parameters['charset'] = 'utf-8';
    }

    /**
     * @param string $url PayPal IPN url, paypal will send here a POST request with info about payment
     * @param bool $instantCallback Url will be requested by paypal immediately after payment with POST data (even if you webserver is offline), IPN callback will be executed too, so you must count with that
     * @param int $callBackTimeOut Valid value is 1-6 (seconds), default 3
     */
    public function setCallBack(string $url, bool $instantCallback = false, int $callBackTimeOut = 3)
    {
        $this->parameters['notify_url'] = $url;

        if ($instantCallback) {
            $this->parameters['callback_url'] = $url;
            $this->parameters['callback_timeout'] = $callBackTimeOut;
        }
    }

    /**
     * @param string $url Url where paypal sends user after successful payment
     */
    public function setSuccessUrl(string $url)
    {
        $this->parameters['return'] = $url;
    }

    /**
     * @param string $url Url where paypal sends user after cancelling the payment
     */
    public function setFailUrl(string $url)
    {
        $this->parameters['cancel_return'] = $url;
    }

    /**
     * @param string $currencyCode Currency code, max length is 3 characters
     */
    public function setCurrencyCode(string $currencyCode)
    {
        $this->parameters['currency_code'] = $currencyCode;
    }

    /**
     * @param $custom string Custom parameter such as user identifier
     */
    public function setCustom(string $custom)
    {
        $this->parameters['custom'] = $custom;
    }

    /**
     * @param string $company
     * @param null|string $service
     * @param null|string $product
     * @param null|string $country
     */
    public function setStoreInfo(string $company, ?string $service = null, ?string $product = null, ?string $country = null)
    {
        $info = $company;

        if ($service) {
            $info .= '_' . $service;
        }

        if ($product) {
            $info .= '_' . $product;
        }

        if ($country) {
            $info .= '_' . $country;
        }

        $this->parameters['bn'] = $info;
    }

    /**
     * @param string $language Default language is not set, use format en_GB
     */
    public function setLanguage(string $language)
    {
        $this->parameters['lc'] = $language;
    }

    /**
     * @param string $charset Default charset is utf-8
     */
    public function setCharset(string $charset)
    {
        $this->parameters['charset'] = $charset;
    }

    /**
     * @param bool $enableNote
     */
    public function setNote(bool $enableNote)
    {
        $this->parameters['no_note'] = $enableNote;
    }

    /**
     * @param bool $enableShipping
     */
    public function setShipping(bool $enableShipping)
    {
        $this->parameters['no_shipping'] = $enableShipping;
    }

    /**
     * @param string $account Receiver email or ID
     */
    public function setAccount(string $account)
    {
        $this->parameters['business'] = $account;
        $this->parameters['receiver_email'] = $account;
    }

    /**
     * @param string $cartName Name for whole cart
     */
    public function setCartName(string $cartName)
    {
        $this->parameters['item_name'] = $cartName;
        $this->parameters['item_name_1'] = $cartName . 'ss';
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->parameters['quantity'] = $quantity;
        $this->parameters['quantity_1'] = $quantity;
    }

    /**
     * @param float $amount
     */
    public function setCartAmount(float $amount)
    {
        $this->parameters['amount'] = $amount;
        $this->parameters['amount_1'] = $amount;
    }

    /**
     * @param string $itemName
     * @param int $quantity
     * @param float $price
     * @return string
     */
    public function addItem(string $itemName, int $quantity, float $price): string
    {
        $this->parameters['upload'] = 1;
        $this->parameters['item_name_' . $this->itemIndex] = $itemName;
        $this->parameters['amount_' . $this->itemIndex] = $price;
        $this->parameters['quantity_' . $this->itemIndex] = $quantity;

        return 'i' . $this->itemIndex++;
    }

    /**
     * @param int $index Must be int 0-6
     * @param string $parameterName Name of the parameter, max length is 64 characters
     * @param int|string|bool $value Boolean value will be converted to int 0/1, max length is 64 characters
     */
    public function setCustomParameter(int $index, string $parameterName, $value)
    {
        $this->customOptions += 1;
        $this->parameters['on' . $index] = $parameterName;
        $this->parameters['os' . $index] = $value;
        $this->parameters['option_index'] = $this->customOptions;
    }

    /**
     * PayPal image in the top left corner
     * @param string $url Image dimensions: 150x50px
     */
    public function setImage(string $url)
    {
        $this->parameters['image_url'] = $url;
    }

    /**
     * @param bool $isShoppingCart
     */
    public function setIsShoppingCart(bool $isShoppingCart)
    {
        $this->isShoppingCart = $isShoppingCart;

        $this->parameters['cmd'] = '_cart';
    }

    /**
     * @return bool
     */
    public function isShoppingCart(): bool
    {
        return $this->isShoppingCart;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->baseUrl . http_build_query($this->parameters);
    }
}
