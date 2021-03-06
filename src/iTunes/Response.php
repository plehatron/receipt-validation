<?php

namespace Plehatron\ReceiptValidation\iTunes;

use Exception;

class Response
{
    /**
     * Status is OK.
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
     * Receipt that was sent for verification.
     *
     * @var array
     */
    protected $receipt = [];

    /**
     * Available only for iOS 6 style transaction receipts for auto-renewable subscriptions.
     * The base-64 encoded transaction receipt for the most recent renewal.
     *
     * @var mixed
     */
    protected $latestReceipt;

    /**
     * Available only for iOS 6 style transaction receipts for auto-renewable subscriptions.
     * The JSON representation of the receipt for the most recent renewal.
     *
     * @var mixed
     */
    protected $latestReceiptInfo;

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
     * @return bool
     */
    public function valid()
    {
        if ($this->statusCode == self::STATUS_OK) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getReceipt()
    {
        return $this->receipt;
    }

    /**
     * @return mixed
     */
    public function getLatestReceipt()
    {
        return $this->latestReceipt;
    }

    /**
     * @return mixed
     */
    public function getLatestReceiptInfo()
    {
        return $this->latestReceiptInfo;
    }

    /**
     * @param $responseData
     * @return $this
     * @throws Exception
     */
    protected function parseResponse($responseData)
    {
        if (!is_object($responseData)) {
            throw new Exception('Invalid response data. Expected object, got ' . gettype($responseData));
        }

        if (property_exists($responseData, 'status')) {
            $this->statusCode = $responseData->status;
        } else {
            $this->statusCode = self::STATUS_UNKNOWN;
        }

        if (property_exists($responseData, 'receipt')) {
            $this->receipt = $responseData->receipt;
        }

        if (property_exists($responseData, 'latest_receipt')) {
            $this->latestReceipt = $responseData->latest_receipt;
        }

        if (property_exists($responseData, 'latest_receipt_info')) {
            $this->latestReceiptInfo = $responseData->latest_receipt_info;
        }

        return $this;
    }
}