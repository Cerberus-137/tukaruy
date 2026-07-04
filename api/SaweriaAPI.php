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
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? 'API request failed';
            throw new Exception($errorMessage);
        }
        
        return json_decode($response, true);
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