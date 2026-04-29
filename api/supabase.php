<?php
require_once 'env.php';

class Supabase {
    /**
     * Authenticates a user against the Supabase Auth REST API
     */
    public static function authenticate($email, $password) {
        $url = SUPABASE_URL . '/auth/v1/token?grant_type=password';
        
        $data = json_encode([
            'email' => $email,
            'password' => $password
        ]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . SUPABASE_KEY,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpcode === 200 && isset($result['access_token'])) {
            // Success
            // We can assume a role might be in user_metadata, otherwise default to 'admin'
            $role = $result['user']['user_metadata']['role'] ?? 'admin';
            
            return [
                'success' => true,
                'user_id' => $result['user']['id'],
                'role' => $role,
                'token' => $result['access_token']
            ];
        } else {
            // Failure
            return [
                'success' => false,
                'error' => $result['error_description'] ?? 'Invalid credentials'
            ];
        }
    }

    /**
     * Helper to perform REST API requests
     */
    public static function request($method, $endpoint, $body = null) {
        $url = SUPABASE_URL . '/rest/v1/' . ltrim($endpoint, '/');
        
        $headers = [
            'apikey: ' . SUPABASE_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        if (isset($_SESSION['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        } else {
            $headers[] = 'Authorization: Bearer ' . SUPABASE_KEY;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        } else if ($method === 'GET') {
            // default is GET
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpcode,
            'data' => json_decode($response, true)
        ];
    }
}
