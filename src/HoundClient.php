<?php

declare(strict_types=1);

namespace Macrose\Hound;

use Macrose\Hound\DTO\Event;
use Macrose\Hound\Filter\EventFilterInterface;
use Macrose\Hound\Filter\NullFilter;
use Macrose\Hound\Transport\TransportInterface;
use Macrose\Hound\Transport\CurlTransport;
use Macrose\Hound\Transport\Psr18Transport;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class HoundClient
{
    private TransportInterface $transport;
    private EventFilterInterface $filterChain;

    public function __construct(
        private readonly string  $endpoint,
        private readonly string  $publicKey,
        private readonly string  $privateKey,
        ?TransportInterface      $transport = null,
        private ?string          $environment = null,
        private readonly ?string $release = null,
        ?ClientInterface         $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) {
        $this->environment = $environment ?? $_ENV['APP_ENV'] ?? 'production';
        $this->transport = $transport ?? $this->createDefaultTransport($httpClient, $requestFactory);
        $this->filterChain = new NullFilter();
    }

    public function captureException(\Throwable $e, ?string $message = null, int $count = 1): void
    {
        $event = (new EventBuilder)->buildFromThrowable(
            $e,
            $this->environment,
            $this->release,
            $count
        );

        if ($message !== null) {
            $event = $event->withMessage($message);
        }

        $this->transport->send($event);
    }

    public function captureMessage(string $message, string $level = 'error'): void
    {
        $event = (new EventBuilder)->buildFromMessage(
            $message,
            $level,
            $this->environment,
            $this->release
        );

        $this->transport->send($event);
    }

    public function addFilter(EventFilterInterface $filter): void
    {
        if ($this->filterChain instanceof NullFilter) {
            $this->filterChain = $filter;
            return;
        }

        $current = $this->filterChain;
        while ($current->getNext() !== null) {
            $current = $current->getNext();
        }
        $current->setNext($filter);
    }

    private function createDefaultTransport(
        ?ClientInterface $httpClient,
        ?RequestFactoryInterface $requestFactory
    ): TransportInterface {
        if ($httpClient !== null && $requestFactory !== null) {
            return new Psr18Transport(
                $httpClient,
                $requestFactory,
                $this->endpoint,
                $this->publicKey,
                $this->privateKey
            );
        }

        if (extension_loaded('curl')) {
            return new CurlTransport(
                $this->endpoint,
                $this->publicKey,
                $this->privateKey
            );
        }

        throw new \RuntimeException('No available transport. Install PSR-18 client or cURL extension.');
    }

    private function applyFilters(Event $event): ?Event
    {
        return $this->filterChain->process($event);
    }
}
