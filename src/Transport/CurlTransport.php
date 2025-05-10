<?php

namespace Macrose\Hound\Transport;

use Macrose\Hound\DTO\Event;
use Macrose\Hound\JsonSerializer;

final readonly class CurlTransport implements TransportInterface
{
    public function __construct(
        private string $endpoint,
        private string $publicKey,
        private string $privateKey,
        private int    $timeout = 10,
    ) {}

    public function send(Event $event): void
    {
        $ch = curl_init($this->endpoint);
        $body = (new JsonSerializer())->serialize($event);
        $signature = hash_hmac('sha256', $body, $this->privateKey);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Hound-Auth: ' . $this->publicKey,
                'X-Hound-Signature: ' . $signature,
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('CURL error: ' . $error);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = $this->parseHeaders(substr($response, 0, $headerSize));
        $body = substr($response, $headerSize);
    }

    private function parseHeaders(string $headers): array
    {
        $result = [];
        foreach (explode("\r\n", $headers) as $header) {
            if (str_contains($header, ':')) {
                [$name, $value] = explode(':', $header, 2);
                $result[trim($name)] = trim($value);
            }
        }
        return $result;
    }
}