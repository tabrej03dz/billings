<?php

namespace App\Services;

use GuzzleHttp\Client;

class MediaManagerService
{
    protected string $apiUrl;
    protected ?string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.media_manager.url', env('MEDIA_MANAGER_URL', 'https://media-manager.1automations.com/api/uploadfile'));
        $this->token  = config('services.media_manager.token') ?: env('MEDIA_MANAGER_TOKEN');
    }

    public function upload(string $absoluteFilePath): array
    {
        if (!file_exists($absoluteFilePath)) {
            return $this->fail(0, 'Local file not found: ' . $absoluteFilePath);
        }

        // ✅ TEMP hardcode (remove later)
        if (empty($this->token)) {
            $this->token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiI2OTkyZWZkMTM4NjVjN2ZiNzZjMjQwNmIiLCJpYXQiOjE3NzEyMzczNDF9.jJmTHBE2TrNevtElE0xd4v_VCnxzwML_XbVDLiAs-g4";
        }

        if (empty($this->token) || $this->token === 'PUT_YOUR_TOKEN_HERE') {
            return $this->fail(0, 'MEDIA_MANAGER_TOKEN missing/invalid. Set it in .env and run: php artisan optimize:clear');
        }

        $verifySSL = app()->environment('local') ? false : true;

        $client = new Client([
            'timeout' => 120,
            'connect_timeout' => 20,
            'http_errors' => false,
            'verify' => $verifySSL,
        ]);

        $headerVariants = [
            ['Authorization' => 'Bearer ' . $this->token],
            ['Authorization' => $this->token],
            ['x-api-key' => $this->token],
        ];

        $last = null;

        foreach ($headerVariants as $headers) {

            $res = $client->post($this->apiUrl, [
                'headers' => array_merge($headers, [
                    'Accept' => 'application/json',
                ]),
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($absoluteFilePath, 'r'),
                        'filename' => basename($absoluteFilePath),
                    ],
                ],
            ]);

            $status = $res->getStatusCode();
            $raw    = (string) $res->getBody();
            $json   = json_decode($raw, true);

            $remoteUrl = $this->extractRemoteUrl($json);

            if ($status >= 200 && $status < 300) {
                return [
                    'ok' => true,
                    'status' => $status,
                    'json' => $json,
                    'raw' => $raw,
                    'used_headers' => $headers,
                    'remote_url' => $remoteUrl,
                ];
            }

            $last = [
                'ok' => false,
                'status' => $status,
                'json' => $json,
                'raw' => $raw,
                'used_headers' => $headers,
                'remote_url' => $remoteUrl,
            ];

            // ✅ unauthorized => try next variant
            if (
                $status == 401 ||
                $status == 403 ||
                str_contains($raw, 'Token not Valid') ||
                str_contains($raw, 'Unauthorized')
            ) {
                continue;
            }

            return $last;
        }

        return $last ?: $this->fail(0, 'Unknown error');
    }

    private function extractRemoteUrl($json): ?string
    {
        if (!is_array($json)) return null;

        // common keys
        $remoteUrl = $json['url'] ?? $json['fileUrl'] ?? null;

        // nested patterns
        if (!$remoteUrl && isset($json['data']) && is_array($json['data'])) {
            $remoteUrl = $json['data']['url'] ?? ($json['data']['file_url'] ?? ($json['data']['path'] ?? ($json['data']['fileUrl'] ?? null)));
        }

        if (!$remoteUrl && isset($json['file']) && is_array($json['file'])) {
            $remoteUrl = $json['file']['url'] ?? ($json['file']['file_url'] ?? ($json['file']['fileUrl'] ?? null));
        }

        if (!$remoteUrl && isset($json['path']) && is_string($json['path'])) {
            $remoteUrl = $json['path'];
        }

        // ✅ your response example: {"file":{"fileUrl":"..."}} already handled
        return $remoteUrl;
    }

    private function fail(int $status, string $raw): array
    {
        return [
            'ok' => false,
            'status' => $status,
            'raw' => $raw,
            'json' => null,
            'used_headers' => null,
            'remote_url' => null,
        ];
    }
}
