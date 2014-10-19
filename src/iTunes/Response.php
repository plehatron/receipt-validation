<?php

namespace Plehatron\ReceiptValidation\iTunes;

use Exception;

class Response
{
    /**
     *
     */
    const STATUS_OK = 0;

    /**
     * The App Store could not read the JSON object you provided.
     */
    const STATUS_UNREADABLE_JSON_OBJECT = 21000;

    /**
     * The data in the receipt-data property was malformed or missing.
     */
    const STATUS_MALFORMED_RECEIPT = 21002;

    /**
     * The receipt could not be authenticated.
     */
    const STATUS_RECEIPT_NOT_AUTHENTICATED = 21003;

    /**
     * The shared secret you provided does not match the shared secret on file for your account. Only returned for iOS 6
     * style transaction receipts for auto-renewable subscriptions.
     */
    const STATUS_SHARED_SECRET_MISMATCH = 21004;

    /**
     * The receipt server is not currently available.
     */
    const STATUS_RECEIPT_SERVER_UNAVAILABLE = 21005;

    /**
     * This receipt is valid but the subscription has expired. When this status code is returned to your server,
     * the receipt data is also decoded and returned as part of the response. Only returned for iOS 6 style transaction
     * receipts for auto-renewable subscriptions.
     */
    const STATUS_RECEIPT_VALID_SUBSCRIPTION_EXPIRED = 21006;

    /**
     * This receipt is from the test environment, but it was sent to the production environment for verification.
     * Send it to the test environment instead.
     */
    const STATUS_RECEIPT_FROM_TEST_SENT_TO_PRODUCTION = 21007;

    /**
     * This receipt is from the production environment, but it was sent to the test environment for verification.
     * Send it to the production environment instead.
     */
    const STATUS_RECEIPT_FROM_PRODUCTION_SENT_TO_TEST = 21008;

    /**
     * Unknown status.
     */
    const STATUS_UNKNOWN = -1;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $receipt = [];

    /**
     * @var array
     */
    protected $purchases = [];

    /**
     * @param $responseData
     */
    public function __construct($responseData)
    {
        $this->parseResponse($responseData);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * @return array
     */
    public function getReceipt()
    {
        return $this->receipt;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->statusCode == self::STATUS_OK) {
            return true;
        }
        return false;
    }

    /**
     * @param $responseData
     * @return $this
     * @throws Exception
     */
    public function parseResponse($responseData)
    {
        if (!is_array($responseData)) {
            throw new Exception('Invalid response data. Expected array, got ' . substr(var_export($responseData, true), 0, 100));
        }

        if (array_key_exists('status', $responseData)) {
            $this->statusCode = $responseData['status'];
        } else {
            $this->statusCode = self::STATUS_UNKNOWN;
        }

        if (array_key_exists('receipt', $responseData)) {
            $this->receipt = $responseData['receipt'];
        }

        if (array_key_exists('purchases', $responseData)) {
            $this->purchases = $responseData['purchases'];
        }

        return $this;
    }
}