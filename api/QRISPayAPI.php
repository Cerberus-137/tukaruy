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
        
        // Log request for debugging
        error_log('QRIS API Request: ' . $url . ' | Method: ' . $method);
        error_log('QRIS API Response Code: ' . $httpCode);
        error_log('QRIS API Response Body: ' . $response);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorMessage = $decoded['message'] ?? $decoded['error'] ?? 'API request failed';
            throw new Exception($errorMessage);
        }
        
        return $decoded;
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
        
        // Log full response for debugging
        error_log('QRIS Generate Full Response: ' . json_encode($response));
        
        // Handle different response formats
        // Format 1: {status: "success", data: {...}}
        if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
            $qrisData = $response['data'];
        }
        // Format 2: Direct data without wrapper
        else if (isset($response['qris_id']) || isset($response['id'])) {
            $qrisData = $response;
        }
        // Format 3: {success: true, data: {...}}
        else if (isset($response['success']) && $response['success'] && isset($response['data'])) {
            $qrisData = $response['data'];
        }
        else {
            error_log('QRIS Unknown Response Format: ' . json_encode($response));
            throw new Exception('Unknown QRIS response format');
        }
        
        // Normalize response format - handle multiple possible field names
        // Convert expired_at to MySQL TIMESTAMP format if it's in ISO 8601 format
        $expiredAt = $qrisData['expired_at'] ?? $qrisData['expires_at'] ?? $qrisData['expiry_time'] ?? date('Y-m-d H:i:s', strtotime('+15 minutes'));
        if (!empty($expiredAt)) {
            try {
                $dateTime = new DateTime($expiredAt, new DateTimeZone('UTC'));
                $expiredAt = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                error_log('Failed to parse expired_at: ' . $expiredAt);
                $expiredAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
        }
        
        $normalized = [
            'qris_id' => $qrisData['qris_id'] ?? $qrisData['id'] ?? $qrisData['transaction_id'] ?? null,
            'qris_image_url' => $qrisData['qris_image_url'] ?? $qrisData['image_url'] ?? $qrisData['qr_url'] ?? $qrisData['qr_image'] ?? $qrisData['qr_code_url'] ?? '',
            'qris_string' => $qrisData['qris_string'] ?? $qrisData['qr_string'] ?? $qrisData['qr_code'] ?? '',
            'amount' => $qrisData['amount'] ?? $amount,
            'payment_reference' => $qrisData['payment_reference'] ?? $qrisData['reference'] ?? $paymentReference,
            'expired_at' => $expiredAt,
            'expires_in_seconds' => $qrisData['expires_in_seconds'] ?? $qrisData['expires_in'] ?? 900,
            'status' => $qrisData['status'] ?? 'pending'
        ];
        
        // Validate required fields
        if (empty($normalized['qris_id'])) {
            throw new Exception('QRIS ID not found in response');
        }
        
        if (empty($normalized['qris_image_url']) && empty($normalized['qris_string'])) {
            error_log('Warning: No QR image URL or QR string in response');
        }
        
        return $normalized;
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus($qrisId) {
        $response = $this->makeRequest("/api/payment/qris/{$qrisId}/status");
        
        // Log full response for debugging
        error_log('QRIS Status Check Response: ' . json_encode($response));
        
        // Handle different response formats
        // Format 1: {status: "success", data: {...}}
        if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
            $statusData = $response['data'];
        }
        // Format 2: Direct data without wrapper
        else if (isset($response['qris_id']) || isset($response['id']) || isset($response['status'])) {
            $statusData = $response;
        }
        else {
            error_log('QRIS Status Unknown Response Format: ' . json_encode($response));
            throw new Exception('Unknown QRIS status response format');
        }
        
        // Normalize status field
        $normalized = [
            'qris_id' => $statusData['qris_id'] ?? $statusData['id'] ?? $qrisId,
            'status' => strtolower($statusData['status'] ?? $statusData['payment_status'] ?? 'pending'),
            'amount' => $statusData['amount'] ?? 0,
            'payment_reference' => $statusData['payment_reference'] ?? $statusData['reference'] ?? '',
            'paid_at' => $statusData['paid_at'] ?? $statusData['payment_time'] ?? null
        ];
        
        return $normalized;
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
