<?php

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WeatherServiceTest extends TestCase
{
    public function testReturnsExpectedPayloadForHotWeather(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'current' => [
                    'temperature_2m' => 32.4,
                ],
            ]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                'GET',
                'https://api.open-meteo.com/v1/forecast',
                self::arrayHasKey('query')
            )
            ->willReturn($response);

        $item = $this->createMock(ItemInterface::class);
        $item->expects(self::once())
            ->method('expiresAfter')
            ->with(1800)
            ->willReturnSelf();

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(self::once())
            ->method('get')
            ->willReturnCallback(static function (string $key, callable $callback) use ($item): array {
                self::assertSame('excuse_weather_Paris', $key);

                return $callback($item);
            });

        $service = new WeatherService($httpClient, $cache);
        $data = $service->getExcuseWeather();

        self::assertIsArray($data);
        self::assertSame('Paris', $data['city']);
        self::assertSame('Canicule', $data['label']);
        self::assertSame('low', $data['indulgence']);
        self::assertSame(25, $data['successRate']);
    }

    public function testReturnsNullOnFailure(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->willThrowException(new \RuntimeException('Service indisponible'));

        $item = $this->createMock(ItemInterface::class);
        $item->expects(self::once())
            ->method('expiresAfter')
            ->with(1800)
            ->willReturnSelf();

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(self::once())
            ->method('get')
            ->willReturnCallback(static function (string $key, callable $callback) use ($item): ?array {
                self::assertSame('excuse_weather_Paris', $key);

                return $callback($item);
            });

        $service = new WeatherService($httpClient, $cache);

        self::assertNull($service->getExcuseWeather());
    }
}

