<?php

namespace Macrose\Hound\Transport;

use Macrose\Hound\DTO\Event;
use Macrose\Hound\JsonSerializer;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final readonly class Psr18Transport implements TransportInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private string $endpoint,
        private string $publicKey,
        private string $privateKey,
    ) {}

    public function send(Event $event): void
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Hound-Auth', $this->publicKey);

        $body = (new JsonSerializer())->serialize($event);
        $signature = hash_hmac('sha256', $body, $this->privateKey);

        $request = $request
            ->withHeader('X-Hound-Signature', $signature)
            ->withBody($this->requestFactory->createStream($body));

        $this->httpClient->sendRequest($request);
    }
}
