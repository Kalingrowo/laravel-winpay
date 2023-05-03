<?php

namespace MuHasan\LaravelWinpay;

/**
 * Class Winpay
 * @package MuHasan\LaravelWinpay
 */
class Winpay
{
    private $host;
    private $pk1;
    private $pk2;
    private $mk;
    private $listener;

    public function __construct($host, $pk1, $pk2, $mk, $listener)
    {
        $this->host = $host;
        $this->pk1 = $pk1;
        $this->pk2 = $pk2;
        $this->mk = $mk;
        $this->listener = $listener;
    }

    private function getBasicAuth()
    {
        return base64_encode($this->pk1 . ":" . $this->pk2);
    }

    private function sendRequest($path, $usingBasic = false, $payload = null)
    {
        $headers = [];
        if (is_array($payload)) {
            array_push($headers, 'Content-Type: application/json');
        } else {
            array_push($headers, 'Content-Type: application/x-www-form-urlencoded');
        }
        if ($usingBasic) array_push($headers, 'Authorization: Basic ' . $this->getBasicAuth());

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_IPRESOLVE_V4);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $result;
    }

    public function getToolbar()
    {
        return $this->sendRequest('/toolbar', true);
    }

    private function getToken()
    {
        return $this->sendRequest('/token', true);
    }

    public function getPaymentCode($paymentChannel, BillingTransaction $transaction, BillingUser $user, array $rawItems)
    {
        return $this->sendRequest('/apiv2/' . $paymentChannel, false, 'orderdata=' . $this->createPayload($transaction, $user, $rawItems, str_contains(strtolower($paymentChannel), 'qris')));
    }

    private function createPayload(BillingTransaction $transaction, BillingUser $user, array $rawItems, $isQris = false)
    {
        $items = [];
        foreach ($rawItems as $item) {
            if ($item instanceof BillingItem) {
                $parsedItem = [
                    'name' => $item->getBillItemName(),
                    'qty' => $item->getBillItemQty(),
                    'unitPrice' => $item->getBillItemUnitPrice(),
                ];
                if ($item->getBillItemSku()) $parsedItem['sku'] = $item->getBillItemSku();
                if ($item->getBillItemDesc()) $parsedItem['desc'] = $item->getBillItemDesc();
                array_push($items, $parsedItem);
            } else {
                $parsedItem = [
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'unitPrice' => $item['unit_price'],
                ];
                if ($item['sku']) $parsedItem['sku'] = $item['sku'];
                if ($item['description']) $parsedItem['desc'] = $item['description'];
                array_push($items, $parsedItem);
            }
        }
        $transaction_reff = $transaction->getBillTransactionReff();
        $amount = $transaction->getBillTransactionAmount();
        $token = $this->getToken();
        $signature = $this->createSignature($transaction_reff, $amount);
        $payload = [
            'cms' => 'WINPAY API',
            // 'spi_callback' => 'https://sandbox-payment.winpay.id/sandbox',
            'url_listener' => $this->listener,
            'spi_currency' => 'IDR',
            'spi_item' => $items,
            'spi_amount' => $amount,
            'spi_signature' => $signature,
            // 'spi_signature' => 0.0,
            'spi_token' => $this->pk1 . $this->pk2,
            'spi_merchant_transaction_reff' => $transaction_reff,
            'spi_billingPhone' => $user->getBillUserPhone(),
            'spi_billingName' => $user->getBillUserName(),
            'spi_paymentDate' => $transaction->getBillTransactionEndAt()->format('YmdHis'),
            'get_link' => 'no',
        ];
        if ($user->getBillUserEmail()) $payload['spi_billingEmail'] = $user->getBillUserEmail();
        if ($isQris) {
            $payload['spi_qr_type'] = 'dynamic';
            // $payload['spi_qr_subname'] = $user->getBillUserPhone();
        }

        return $this->encryptPayload($token['data']['token'], $payload);
    }

    private function createSignature(string $transaction_reff, int $amount)
    {
        $merchant_key = $this->mk;
        $spi_token = $this->pk1 . $this->pk2;
        $spi_amount = number_format(doubleval($amount), 2, ".", "");
        return strtoupper(sha1($spi_token . '|' . $merchant_key . '|' . $transaction_reff . '|' . $spi_amount . '|0|0'));
    }

    private function encryptPayload(string $token, array $payload)
    {
        $json_string = json_encode($payload);
        $messageEncrypted = $this->openSSLEncrypt($json_string, $token);
        return substr($messageEncrypted, 0, 10) . $token . substr($messageEncrypted, 10);
    }

    private function openSSLEncrypt($message, $key)
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = $key;
        $secret_iv = $key;
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        $output = openssl_encrypt($message, $encrypt_method, $key, 0, $iv);
        $output = trim(base64_encode($output));
        return $output;
    }

    /**
    * @param string $reffId reff_id from generated payment-code response
    */
    public function checkTransactionStatusNonQris($reffId)
    {
        return $this->sendRequest('/transaction/check-wpi-transaction?id_transaction_inquiry=' . $reffId, true);
    }

    /**
    * @param string $merchantId
    * @param int $offset total displayed data, max = 100
    */
    public function getListQris($merchantId, $offset = 0)
    {
        return $this->sendRequest('/qris/get_list_qr', true, 'merchant_id=' . $merchantId . '&offset=' . $offset);
    }
}
