<?php

class ApiClient {
    private $baseUrl = 'http://localhost:8000/api';
    private $token = null;

    public function __construct() {
        // session_start() já é chamado no config.php
        if (isset($_SESSION['token'])) {
            $this->token = $_SESSION['token'];
        }
    }

    private function makeRequest($method, $endpoint, $data = null, $files = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $headers = [
            'Accept: application/json',
        ];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($files) {
                    // Para upload de arquivos
                    $postData = $data ?: [];
                    foreach ($files as $key => $file) {
                        if (is_array($file)) {
                            foreach ($file as $index => $singleFile) {
                                $postData[$key . '[' . $index . ']'] = new CURLFile($singleFile['tmp_name'], $singleFile['type'], $singleFile['name']);
                            }
                        } else {
                            $postData[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                        }
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                } else if ($data) {
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $result,
            'http_code' => $httpCode,
            'message' => $result['message'] ?? ($httpCode >= 400 ? 'Erro na requisição' : 'Sucesso')
        ];
    }

    // Autenticação
    public function login($email, $password) {
        $result = $this->makeRequest('POST', '/login', [
            'email' => $email,
            'password' => $password
        ]);
        
        if ($result['success'] && isset($result['data']['token'])) {
            $this->token = $result['data']['token'];
            $_SESSION['token'] = $this->token;
            
            // Buscar dados completos do usuário
            $userResult = $this->getUser();
            if ($userResult['success']) {
                $_SESSION['user'] = $userResult['data'];
            } else {
                // Fallback para os dados básicos retornados no login
                $_SESSION['user'] = $result['data']['user'] ?? $result['data'] ?? [];
            }
        }
        
        return $result;
    }

    public function register($userData) {
        return $this->makeRequest('POST', '/register', $userData);
    }

    public function logout() {
        $result = $this->makeRequest('POST', '/logout');
        $this->token = null;
        unset($_SESSION['token']);
        unset($_SESSION['user']);
        session_destroy();
        return $result;
    }

    public function getUser() {
        return $this->makeRequest('GET', '/me');
    }

    // Caronas
    public function getRides($filters = []) {
        $query = http_build_query($filters);
        $endpoint = '/rides' . ($query ? '?' . $query : '');
        return $this->makeRequest('GET', $endpoint);
    }

    public function getRide($id) {
        return $this->makeRequest('GET', '/rides/' . $id);
    }

    public function createRide($rideData, $files = null) {
        return $this->makeRequest('POST', '/rides', $rideData, $files);
    }

    public function updateRide($id, $rideData, $files = null) {
        return $this->makeRequest('POST', '/rides/' . $id, array_merge($rideData, ['_method' => 'PUT']), $files);
    }

    public function deleteRide($id) {
        return $this->makeRequest('DELETE', '/rides/' . $id);
    }

    public function getMyRides() {
        return $this->makeRequest('GET', '/rides/my-rides');
    }

    // Solicitações
    public function requestRide($rideId, $message = null) {
        return $this->makeRequest('POST', '/ride-requests', [
            'ride_id' => $rideId,
            'message' => $message
        ]);
    }

    public function getMyRequests() {
        return $this->makeRequest('GET', '/ride-requests/my-requests');
    }

    public function getRideRequests($rideId) {
        return $this->makeRequest('GET', '/rides/' . $rideId . '/requests');
    }

    public function updateRequestStatus($requestId, $status) {
        return $this->makeRequest('PUT', '/ride-requests/' . $requestId, [
            'status' => $status
        ]);
    }

    public function cancelRequest($requestId) {
        return $this->makeRequest('DELETE', '/ride-requests/' . $requestId);
    }

    public function cancelRide($rideId) {
        return $this->makeRequest('PUT', '/rides/' . $rideId, ['status' => 'cancelled']);
    }

    public function getRideDetails($rideId) {
        return $this->makeRequest('GET', '/rides/' . $rideId);
    }

    public function approveRequest($requestId) {
        return $this->updateRequestStatus($requestId, 'approved');
    }

    public function rejectRequest($requestId) {
        return $this->updateRequestStatus($requestId, 'rejected');
    }

    // Helpers
    public function isLoggedIn() {
        return !empty($this->token) && isset($_SESSION['user']);
    }

    public function getLoggedUser() {
        return $_SESSION['user'] ?? null;
    }

    public function isDriver() {
        $user = $this->getLoggedUser();
        // Verifica tanto 'role' (Laravel) quanto 'user_type' (frontend) para compatibilidade
        $userRole = $user['role'] ?? $user['user_type'] ?? null;
        return $user && isset($userRole) && ($userRole === 'driver' || $userRole === 'both');
    }
} 