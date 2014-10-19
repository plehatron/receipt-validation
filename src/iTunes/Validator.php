<?php

namespace Plehatron\ReceiptValidation\iTunes;

use Exception;
use Guzzle\Http\Client as GuzzleClient;
use InvalidArgumentException;
use RuntimeException;

class Validator
{
    const URL_PRODUCTION = 'https://buy.itunes.apple.com';
    const URL_SANDBOX = 'https://sandbox.itunes.apple.com';

    /**
     * iTunes base URI.
     *
     * @var string
     */
    private $baseUri;

    /**
     * App's shared secret.
     *
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $receiptData;

    /**
     * @var
     */
    private $httpClient;

    /**
     * @param string $uri
     */
    public function __construct($uri = self::URL_SANDBOX)
    {
        $this->baseUri = $uri;
    }

    /**
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getReceiptData()
    {
        return $this->receiptData;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setReceiptData($data)
    {
        $this->receiptData = $data;
        return $this;
    }

    /**
     * Returns HTTP client
     *
     * @return GuzzleClient
     */
    protected function getClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new GuzzleClient($this->baseUri);
        }
        return $this->httpClient;
    }

    /**
     * @return string
     */
    private function encodePayload()
    {
        return json_encode([
            'receipt-data' => $this->getReceiptData(),
            'password' => $this->secret
        ]);
    }

    /**
     * @param null $receiptData
     * @param null $secret
     * @return Response
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function validate($receiptData = null, $secret = null)
    {
        if (!is_null($receiptData)) {
            $this->setReceiptData($receiptData);
        }
        if (!is_null($secret)) {
            $this->setSecret($secret);
        }

        if (is_null($this->receiptData)) {
            throw new InvalidArgumentException('Receipt data is not set');
        }

        if (is_null($this->secret)) {
            throw new InvalidArgumentException('App\'s shared secret is not set');
        }

        $payload = $this->encodePayload();

        $httpResponse = $this->getClient()->post('/verifyReceipt', null, $payload, ['verify' => false])->send();

        if ($httpResponse->getStatusCode() != 200) {
            throw new RuntimeException(sprintf('Invalid HTTP response code (%s) from iTunes server', $httpResponse->getStatusCode()));
        }

        $response = new Response($httpResponse->json());

        return $response;
    }
}
