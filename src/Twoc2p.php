<?php

namespace Laraditz\Twoc2p;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laraditz\Twoc2p\Models\Twoc2pPayment;
use LogicException;

class Twoc2p
{
    private $merchantID;

    private $merchantSecret;

    private $sandboxMode = true;

    private $baseUrl;

    public function __construct($merchantID = null, $merchantSecret = null)
    {
        $this->setMerchantID($merchantID ?? config('2c2p.merchant_id'));
        $this->setMerchantSecret($merchantSecret ?? config('2c2p.merchant_secret'));
        $this->setSandboxMode(config('2c2p.sandbox.mode'));
        $this->setCurrencyCode(config('2c2p.currency_code'));
        $this->setBaseUrl();
    }

    public function createPayment(array $requestPayload = [])
    {
        $requestPayload = array_merge(
            [
                'merchantID' => $this->getMerchantID(),
                'currencyCode' => $this->getCurrencyCode(),
                'backendReturnUrl' => route('twoc2p.backend'),
            ],
            $requestPayload
        );

        $twoc2pPayment = Twoc2pPayment::create([
            'action' => Str::after(__METHOD__, '::'),
            'request' => $requestPayload
        ]);

        throw_if(!$twoc2pPayment, LogicException::class, 'Cant create request in database table.');

        try {

            $jwt = JWT::encode($requestPayload, $this->getMerchantSecret());

            $response = Http::acceptJson()->post($this->getUrl('paymentToken'), [
                'payload' => $jwt,
            ]);

            $response->throw();

            if ($response->successful()) {

                if (data_get($response->json(), 'payload')) {
                    $responsePayload = $response->json()['payload'];

                    $decoded = $this->decodeJWT($responsePayload);

                    $response_code = data_get($decoded, 'respCode');

                    if ($response_code) {
                        $twoc2pPayment->response = $decoded;
                        $twoc2pPayment->save();

                        if ($response_code === '0000') {
                            return [
                                'id' => $twoc2pPayment->id,
                                'currency_code' => $this->getCurrencyCode(),
                                'payment_url' => data_get($decoded, 'webPaymentUrl'),
                            ];
                        }
                    }
                } else {
                    $twoc2pPayment->response = $response->json();
                    $twoc2pPayment->save();

                    throw new LogicException(data_get($response->json(), 'respDesc') ?? 'Error.');
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function paymentInquiry(string $payment_id)
    {
        $payment = Twoc2pPayment::findOrFail($payment_id);

        $data = [
            'paymentToken' => data_get($payment->response, 'paymentToken'),
            'merchantID' => data_get($payment->request, 'merchantID'),
            'invoiceNo' => data_get($payment->request, 'invoiceNo'),
            'locale' => null,
        ];

        try {

            $jwt = $this->encodeJWT($data);

            $response = Http::acceptJson()->post($this->getUrl('paymentInquiry'), [
                'payload' => $jwt,
            ]);

            $response->throw();

            if ($response->successful()) {

                if (data_get($response->json(), 'payload')) {
                    $responsePayload = $response->json()['payload'];
                    $responseObj = $this->decodeJWT($responsePayload);

                    return json_decode(json_encode($responseObj), true);
                } else {
                    throw new LogicException(data_get($response->json(), 'respDesc') ?? 'Error.');
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function encodeJWT(array $content)
    {
        return JWT::encode($content, $this->getMerchantSecret());
    }

    public function decodeJWT(string $content)
    {
        return JWT::decode($content, $this->getMerchantSecret(), ['HS256']);
    }

    public function setBaseUrl()
    {
        if ($this->getSandboxMode() === true) {
            $this->baseUrl = config('2c2p.sandbox.base_url');
        } else {
            $this->baseUrl = config('2c2p.base_url');
        }
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
    }

    public function getSandboxMode()
    {
        return $this->sandboxMode;
    }

    public function setMerchantID($merchantID)
    {
        $this->merchantID = $merchantID;
    }

    public function getMerchantID()
    {
        return $this->merchantID;
    }

    public function setMerchantSecret($merchantSecret)
    {
        $this->merchantSecret = $merchantSecret;
    }

    public function getMerchantSecret()
    {
        return $this->merchantSecret;
    }

    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    public function getUrl($route)
    {
        $route = config('2c2p.routes.' . $route);
        return $this->getBaseUrl() . '/' . $route;
    }
}
