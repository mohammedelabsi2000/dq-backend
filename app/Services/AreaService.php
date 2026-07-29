<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class AreaService
{
    public function __construct(
        private readonly Client $httpClient
    ) {}

    /**
     * @throws \RuntimeException
     */
    protected function fetch(string $subURL): array
    {
        $config = config('thirdParty.areas_api');

        $url = rtrim($config['base_url'], '/') . '/' . ltrim($subURL, '/');

        try {
            $response = $this->httpClient->get($url, [
                RequestOptions::HEADERS => [
                    'X-Api-Key' => $config['api_key'],
                ],
                RequestOptions::TIMEOUT => 30,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('تعذر الاتصال بخدمة المناطق الخارجية: ' . $e->getMessage(), 0, $e);
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!is_array($body) || ($body['status'] ?? null) !== 'success') {
            throw new \RuntimeException($body['message'] ?? 'استجابة غير متوقعة من خدمة المناطق الخارجية');
        }

        return $body['data'] ?? [];
    }

    public function getBranches(): array
    {
        return $this->fetch('/areas');
    }

    public function getBranchRegions(int $id): array
    {
        return $this->fetch("/areas/{$id}/sub-areas");
    }
}
