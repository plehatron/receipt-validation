<?php

namespace Plehatron\ReceiptValidation\iTunes;

use Exception;
use Guzzle\Http\Client;
use InvalidArgumentException;
use RuntimeException;

class Validator
{
    const URI_PRODUCTION = 'https://buy.itunes.apple.com';
    const URI_SANDBOX = 'https://sandbox.itunes.apple.com';

    /**
     * iTunes base URI.
     *
     * @var string
     */
    private $baseUri = self::URI_SANDBOX;

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
     * @var Client
     */
    private $httpClient;

    /**
     * @param string $uri
     */
    public function __construct($uri = null)
    {
        if (!is_null($uri)) {
            $this->baseUri = $uri;
        }
    }

    /**
     * Sets app's shared secret.
     *
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Gets receipt data.
     *
     * @return string
     */
    public function getReceiptData()
    {
        return $this->receiptData;
    }

    /**
     * Sets receipt data.
     *
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
     * @return Client
     */
    protected function getClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->baseUri);
        }
        return $this->httpClient;
    }

    /**
     * Encodes receipt data and shared app secret as json.
     *
     * @return string
     */
    private function encodePayload()
    {
        $data = [
            'receipt-data' => $this->getReceiptData()
        ];
        if (!is_null($this->secret)) {
            $data['password'] = $this->secret;
        }
        return json_encode($data);
    }

    /**
     * Validates receipt data by submitting it to iTunes server and returns response object containing
     * status code, detailed receipt and purchase data.
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function validate()
    {
        if (is_null($this->receiptData)) {
            throw new InvalidArgumentException('Receipt data is not set');
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
