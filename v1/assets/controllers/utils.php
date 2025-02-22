<?php
namespace cUtils;

use Exception;

class cUtils {
    
    // Load environment variables from .env file
    public static function loadEnv($filePath)
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue; // Ignore comments
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv("$key=$value");
        }
    }

    // Call telegram api
    public static function callTelegramAPI($url, $data)
    {
        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("cURL Error: " . $error); // Log the cURL error
            throw new Exception("Failed to call Telegram API: " . $error);
        }
    
        // Get the HTTP status code
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Decode the response to check for Telegram API errors
        $responseData = json_decode($response, true);

        // Check if the response indicates an error (ok: false)
        if (isset($responseData['ok']) && $responseData['ok'] === false) {
            // Log the Telegram API error
            error_log("Telegram API Error: " . print_r($responseData, true));
            throw new Exception("Telegram API Error: " . $responseData['description']);
        }

        // Check for non-200 HTTP status codes
        if ($http_code != 200) {
            // Log the HTTP error
            error_log("HTTP Error: Status Code $http_code - Response: " . print_r($responseData, true));
            throw new Exception("Telegram API returned an error: HTTP $http_code");
        }
    }
}

?>
