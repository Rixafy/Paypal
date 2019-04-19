<?php

declare(strict_types=1);

namespace Rixafy\Paypal;

class PaypalData
{
    /** @var array */
    private $rawData;

    /** @var float */
    private $price;

    /** @var string */
    private $currency;

    /** @var string */
    private $custom;

    /** @var string */
    private $payerEmail;

    /** @var array */
    private $customParameters = [];

    public function __construct(array $data)
    {
        $this->rawData = $data;
        $this->price = isset($data['mc_gross']) ? (float) $data['mc_gross'] : 0;
        $this->currency = isset($data['mc_currency']) ? (string) $data['mc_currency'] : 'null';
        $this->custom = isset($data['custom']) ? (string) $data['custom'] : 'null';
        $this->payerEmail = isset($data['payer_email']) ? (string) $data['payer_email'] : 'null';

        foreach ($data as $key => $value) {
            if (substr($key, 0, 11) === 'option_name') {
                $optionValueName = str_replace('option_name', 'option_value', $key);
                if (isset($data[$optionValueName])) {
                    $this->customParameters[$value] = (string)$data[$optionValueName];
                }
            }
        }
    }

    public function getCustomParameter(string $key): ?string
    {
        return isset($this->customParameters[$key]) ? $this->customParameters[$key] : null;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCustom(): string
    {
        return $this->custom;
    }

    public function getPayerEmail(): string
    {
        return $this->payerEmail;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }
}
