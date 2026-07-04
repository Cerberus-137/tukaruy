<?php
/**
 * QRISPay API Client
 * Integration for QRIS payment system
 */

class QRISPayAPI {
    private $baseUrl;
    private $apiToken;
    
    public function __construct() {
        $this->baseUrl = QRISPAY_API_URL;
        $this->apiToken = getQRISPayAPIToken();
    }
    
    /**
     * Make HTTP request to API
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Token: ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? 'API request failed';
            throw new Exception($errorMessage);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Generate QRIS code for payment
     */
    public function generateQRIS($amount, $paymentReference = null, $returnUrl = null) {
        $data = [
            'amount' => (int)$amount
        ];
        
        if ($paymentReference) {
            $data['payment_reference'] = $paymentReference;
        }
        
        if ($returnUrl) {
            $data['return_url'] = $returnUrl;
        }
        
        $response = $this->makeRequest('/api/payment/qris/generate', 'POST', $data);
        
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to generate QRIS');
        }
        
        return $response['data'];
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus($qrisId) {
        $response = $this->makeRequest("/api/payment/qris/{$qrisId}/status");
        
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to check payment status');
        }
        
        return $response['data'];
    }
    
    /**
     * Cancel QRIS payment
     */
    public function cancelQRIS($qrisId) {
        $response = $this->makeRequest("/api/payment/qris/{$qrisId}/cancel", 'POST');
        
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to cancel QRIS');
        }
        
        return true;
    }
    
    /**
     * Get transaction list
     */
    public function getTransactions($filters = []) {
        $queryString = http_build_query($filters);
        $endpoint = '/api/payment/transactions' . ($queryString ? '?' . $queryString : '');
        
        $response = $this->makeRequest($endpoint);
        
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to get transactions');
        }
        
        return $response['data'];
    }
    
    /**
     * Get merchant balance
     */
    public function getBalance() {
        $response = $this->makeRequest('/api/payment/balance');
        
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to get balance');
        }
        
        return $response['data'];
    }
}
