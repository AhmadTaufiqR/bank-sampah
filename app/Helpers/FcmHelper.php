<?php

namespace App\Helpers;

use Google_Client;
use Illuminate\Support\Facades\Storage;

class FcmHelper
{
    public static function sendNotificationToDeviceAdmin($fcmToken, $title, $body)
    {
        if (!$fcmToken) {
            return [
                'success' => false,
                'message' => 'No FCM token provided',
            ];
        }

        // Path ke file JSON
        $credentialsFilePath = Storage::path('json/banksampah-admin-firebase-adminsdk-fbsvc-78356166cd.json');

        // Ambil project_id dari file JSON
        $serviceAccount = json_decode(file_get_contents($credentialsFilePath), true);
        $projectId = $serviceAccount['project_id'];

        // Setup Google Client
        $client = new Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $client->fetchAccessTokenWithAssertion();

        $accessToken = $client->getAccessToken()['access_token'];

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ]
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [
                'success' => false,
                'message' => 'Curl Error: ' . $err
            ];
        } else {
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => json_decode($response, true)
            ];
        }
    }
    public static function sendNotificationToDeviceUser($fcmToken, $title, $body)
    {
        if (!$fcmToken) {
            return [
                'success' => false,
                'message' => 'No FCM token provided',
            ];
        }

        // Path ke file JSON
        $credentialsFilePath = Storage::path('json/banksampah-user-firebase-adminsdk-fbsvc-9a04fa0fb4.json');

        // Ambil project_id dari file JSON
        $serviceAccount = json_decode(file_get_contents($credentialsFilePath), true);
        $projectId = $serviceAccount['project_id'];

        // Setup Google Client
        $client = new Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $client->fetchAccessTokenWithAssertion();

        $accessToken = $client->getAccessToken()['access_token'];

        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ]
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [
                'success' => false,
                'message' => 'Curl Error: ' . $err
            ];
        } else {
            return [
                'success' => true,
                'message' => 'Notification sent successfully',
                'response' => json_decode($response, true)
            ];
        }
    }
}
