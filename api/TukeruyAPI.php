<?php
/**
 * Tukeruy API Client
 * Wrapper for TrackTaco API v2
 */

class TukeruyAPI {
    private $baseUrl;
    private $apiKey;
    private $cursor = null;
    
    public function __construct() {
        $this->baseUrl = API_BASE_URL;
        $this->apiKey = API_KEY;
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
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new Exception($error['error']['message'] ?? 'API request failed');
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Search tracking numbers
     */
    public function search($filters = [], $pageSize = ITEMS_PER_PAGE, $cursor = null) {
        $filter = $this->buildFilter($filters);
        
        // Ensure filter is always an object, not an array
        if (empty($filter)) {
            $filter = new stdClass();
        }
        
        $searchQuery = [
            'filter' => $filter,
            'page_size' => min($pageSize, MAX_ITEMS_PER_PAGE)
        ];
        
        if ($cursor) {
            $searchQuery['cursor'] = $cursor;
        }
        
        $data = [
            'searches' => [$searchQuery]
        ];
        
        $response = $this->makeRequest('/v2/tns/search', 'POST', $data);
        
        if (isset($response['searches'][0]['error'])) {
            throw new Exception($response['searches'][0]['error']['message']);
        }
        
        return $response['searches'][0];
    }
    
    /**
     * Build filter array from request parameters
     */
    private function buildFilter($filters) {
        $filter = new stdClass();
        
        // Carrier filter
        if (!empty($filters['carrier']) && !in_array('all', $filters['carrier'])) {
            $filter->carrier = array_values($filters['carrier']);
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $filter->status = array_values($filters['status']);
        }
        
        // Destination filter
        if (!empty($filters['dest_country'])) {
            $filter->dest = new stdClass();
            $filter->dest->country = $filters['dest_country'];
            
            if (!empty($filters['dest_state'])) {
                $filter->dest->state = $filters['dest_state'];
            }
            
            if (!empty($filters['dest_city'])) {
                $filter->dest->city = strtoupper($filters['dest_city']);
            }
        }
        
        // Origin filter
        if (!empty($filters['origin_country'])) {
            $filter->origin = new stdClass();
            $filter->origin->country = $filters['origin_country'];
            
            if (!empty($filters['origin_state'])) {
                $filter->origin->state = $filters['origin_state'];
            }
            
            if (!empty($filters['origin_city'])) {
                $filter->origin->city = strtoupper($filters['origin_city']);
            }
        }
        
        // Date range filters
        if (!empty($filters['delivery_from']) || !empty($filters['delivery_to'])) {
            $filter->est_delivery_between = new stdClass();
            
            if (!empty($filters['delivery_from'])) {
                $filter->est_delivery_between->from = $filters['delivery_from'];
            }
            
            if (!empty($filters['delivery_to'])) {
                $filter->est_delivery_between->to = $filters['delivery_to'];
            }
        }
        
        if (!empty($filters['ship_from']) || !empty($filters['ship_to'])) {
            $filter->shipped_between = new stdClass();
            
            if (!empty($filters['ship_from'])) {
                $filter->shipped_between->from = $filters['ship_from'];
            }
            
            if (!empty($filters['ship_to'])) {
                $filter->shipped_between->to = $filters['ship_to'];
            }
        }
        
        // Signature and photo options
        if (isset($filters['signature_required']) && $filters['signature_required']) {
            $filter->signature_required = true;
        }
        
        if (isset($filters['photo_confirmed']) && $filters['photo_confirmed']) {
            $filter->photo_confirmed = true;
        }
        
        // Weight range
        if (!empty($filters['weight_min']) || !empty($filters['weight_max'])) {
            $filter->weight_grams = new stdClass();
            
            if (!empty($filters['weight_min'])) {
                $filter->weight_grams->min = (int)$filters['weight_min'];
            }
            
            if (!empty($filters['weight_max'])) {
                $filter->weight_grams->max = (int)$filters['weight_max'];
            }
        }
        
        return $filter;
    }
    
    /**
     * Reveal tracking numbers
     */
    public function reveal($tnIds) {
        if (!is_array($tnIds)) {
            $tnIds = [$tnIds];
        }
        
        $data = [
            'tn_ids' => $tnIds
        ];
        
        $response = $this->makeRequest('/v2/tns/reveal', 'POST', $data);
        
        return $response;
    }
    
    /**
     * Get account information
     */
    public function getAccount($limit = 50, $cursor = null) {
        $endpoint = '/v2/account?limit=' . $limit;
        
        if ($cursor) {
            $endpoint .= '&cursor=' . $cursor;
        }
        
        return $this->makeRequest($endpoint);
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStats() {
        // Get account info for credits
        $account = $this->getAccount(1);
        
        // Get sample data to estimate totals
        $allSearch = $this->search([], 1);
        $fedexSearch = $this->search(['carrier' => ['fedex']], 1);
        $dhlSearch = $this->search(['carrier' => ['dhl']], 1);
        $upsSearch = $this->search(['carrier' => ['ups']], 1);
        
        return [
            'credits' => $account['credits']['balance'] ?? 0,
            'total' => $allSearch['total'] ?? 0,
            'fedex' => $fedexSearch['total'] ?? 0,
            'dhl' => $dhlSearch['total'] ?? 0,
            'ups' => $upsSearch['total'] ?? 0
        ];
    }
    
    /**
     * Format carrier name for display
     */
    public static function formatCarrier($carrier) {
        return strtoupper($carrier);
    }
    
    /**
     * Format status for display
     */
    public static function formatStatus($status) {
        return ucwords(str_replace('-', ' ', $status));
    }
    
    /**
     * Get status badge class
     */
    public static function getStatusBadgeClass($status) {
        $classes = [
            'pre-transit' => 'badge-pre-transit',
            'transit' => 'badge-transit',
            'delivered' => 'badge-delivered',
            'exception' => 'badge-exception'
        ];
        
        return $classes[$status] ?? 'badge-pre-transit';
    }
}
