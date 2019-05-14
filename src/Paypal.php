<?php

declare(strict_types=1);

namespace Rixafy\Paypal;

use Rixafy\Paypal\Exception\PaypalRequestException;
use Rixafy\Paypal\Exception\PaypalVerificationException;

class Paypal
{
    const IPN_DOMAIN = 'notify.paypal.com';
    const IPN_URL = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    const IPN_URL_SANDBOX = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
    const RESPONSE_VERIFIED = 'VERIFIED';
    const RESPONSE_INVALID = 'INVALID';

    /** @var bool */
    private $sandboxMode;

    public function __construct(bool $sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
    }

    /**
     * @throws PaypalVerificationException
     * @throws PaypalRequestException
     */
    public function verifyRequest(array $rawPostData): PaypalData
    {
        if (gethostbyaddr($_SERVER['REMOTE_ADDR']) !== Paypal::IPN_DOMAIN) {
            throw new PaypalRequestException('Received request from unknown domain');
        }

        $requestData = http_build_query(['cmd' => '_notify-validate'] + $rawPostData);

        $session = curl_init($this->sandboxMode ? Paypal::IPN_URL_SANDBOX : Paypal::IPN_URL);

        curl_setopt($session, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($session, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($session, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($session, CURLOPT_HTTPHEADER, ['Connection: Close']);

        $result = curl_exec($session);

        curl_close($session);

        if ($result !== false && is_string($result)) {
            $result = $result === Paypal::RESPONSE_INVALID && $this->sandboxMode ? Paypal::RESPONSE_VERIFIED : $result;

            switch ($result) {
                case Paypal::RESPONSE_VERIFIED:
                    return new PaypalData($rawPostData);

                case Paypal::RESPONSE_INVALID:
                    throw new PaypalVerificationException('Paypal request is invalid');

                default:
                    throw new PaypalVerificationException('Unknown paypal response "' . $result . '"');
            }
        }

        throw new PaypalVerificationException('Verification request to PayPal server failed');
    }
}
