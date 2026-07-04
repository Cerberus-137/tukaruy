<?php
/**
 * Saweria API Client
 * Integration for Saweria payment system
 */

class SaweriaAPI {
    private $baseUrl = 'https://backend.saweria.co';
    private $token;
    
    public function __construct() {
        $this->token = getSaweriaAPIToken();
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
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
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
        
        // Log for debugging
        error_log('Saweria API Request: ' . $url . ' | Method: ' . $method);
        error_log('Saweria API Response Code: ' . $httpCode);
        error_log('Saweria API Response: ' . $response);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorMessage = 'API request failed';
            if ($decoded) {
                $errorMessage = $decoded['message'] ?? $decoded['error'] ?? $errorMessage;
            }
            throw new Exception($errorMessage . ' (HTTP ' . $httpCode . ')');
        }
        
        return $decoded;
    }
    
    /**
     * Get user profile/stream info
     */
    public function getProfile() {
        try {
            $response = $this->makeRequest('/stream');
            
            if (!isset($response['data'])) {
                throw new Exception('Invalid profile response');
            }
            
            return $response['data'];
        } catch (Exception $e) {
            error_log('Saweria getProfile Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate payment link (simplified version without creating donation)
     */
    public function generatePaymentLink($amount, $message = '', $donatorName = 'Anonymous') {
        try {
            // Get username from profile
            $profile = $this->getProfile();
            $username = $profile['username'] ?? $profile['user']['username'] ?? '';
            
            if (empty($username)) {
                throw new Exception('Unable to get Saweria username from profile');
            }
            
            // Generate unique ID for tracking
            $donationId = 'saw_' . time() . '_' . substr(md5(uniqid()), 0, 8);
            
            // Saweria uses direct payment links with amount parameter
            $paymentUrl = "https://saweria.co/{$username}?amount={$amount}&message=" . urlencode($message);
            
            return [
                'donation_id' => $donationId,
                'payment_url' => $paymentUrl,
                'amount' => $amount,
                'message' => $message,
                'donator_name' => $donatorName,
                'username' => $username,
                'created_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log('Saweria generatePaymentLink Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create payment donation
     */
    public function createDonation($amount, $message = '', $donatorName = 'Anonymous') {
        $data = [
            'amount' => (int)$amount,
            'message' => $message ?: 'Top up credits - Tukeruy',
            'donator_name' => $donatorName
        ];
        
        $response = $this->makeRequest('/donations', 'POST', $data);
        
        if (!isset($response['data'])) {
            throw new Exception('Invalid response from Saweria API');
        }
        
        return $response['data'];
    }
    
    /**
     * Get donation by ID
     */
    public function getDonation($donationId) {
        $response = $this->makeRequest("/donations/{$donationId}");
        
        if (!isset($response['data'])) {
            throw new Exception('Donation not found');
        }
        
        return $response['data'];
    }
    
    /**
     * Get donation list
     */
    public function getDonations($limit = 10, $offset = 0) {
        $queryParams = [
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $response = $this->makeRequest('/donations?' . http_build_query($queryParams));
        
        return $response['data'] ?? [];
    }
    
    /**
     * Get user profile/stream info
     */
    public function getProfile() {
        $response = $this->makeRequest('/stream');
        
        if (!isset($response['data'])) {
            throw new Exception('Failed to get profile information');
        }
        
        return $response['data'];
    }
    
    /**
     * Get overlays (donation alerts)
     */
    public function getOverlays() {
        $response = $this->makeRequest('/overlays');
        
        return $response['data'] ?? [];
    }
    
    /**
     * Check if donation is paid
     */
    public function isDonationPaid($donationId) {
        try {
            $donation = $this->getDonation($donationId);
            return isset($donation['status']) && $donation['status'] === 'paid';
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate payment link
     */
    public function generatePaymentLink($amount, $message = '', $donatorName = 'Anonymous') {
        // Saweria uses direct donation links
        $profile = $this->getProfile();
        $username = $profile['username'] ?? '';
        
        if (empty($username)) {
            throw new Exception('Unable to get Saweria username');
        }
        
        // Create donation first to get ID
        $donation = $this->createDonation($amount, $message, $donatorName);
        
        // Return payment URL
        return [
            'donation_id' => $donation['id'],
            'payment_url' => "https://saweria.co/{$username}",
            'amount' => $amount,
            'message' => $message,
            'donator_name' => $donatorName,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}