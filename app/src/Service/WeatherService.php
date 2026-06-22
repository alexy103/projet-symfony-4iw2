<?php

namespace App\Service;

use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WeatherService
{
    private const LATITUDE = 48.8566;
    private const LONGITUDE = 2.3522;
    private const CITY = 'Paris';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * Météo courante + verdict humoristique sur la « passabilité » des excuses.
     *
     * @return array{city: string, temperature: float, indulgence: string, label: string, message: string, emoji: string, successRate: int}|null
     */
    public function getExcuseWeather(): ?array
    {
        try {
            return $this->cache->get('excuse_weather_'.self::CITY, function (ItemInterface $item): array {
                $item->expiresAfter(1800);

                return $this->fetch();
            });
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{city: string, temperature: float, indulgence: string, label: string, message: string, emoji: string, successRate: int}
     */
    private function fetch(): array
    {
        $response = $this->httpClient->request('GET', 'https://api.open-meteo.com/v1/forecast', [
            'query' => [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'current' => 'temperature_2m',
            ],
            'timeout' => 5,
        ]);

        $data = $response->toArray();

        if (!isset($data['current']['temperature_2m'])) {
            throw new TransportException('Réponse météo inattendue.');
        }

        return $this->buildMood((float) $data['current']['temperature_2m']);
    }

    /**
     * @return array{city: string, temperature: float, indulgence: string, label: string, message: string, emoji: string, successRate: int}
     */
    private function buildMood(float $temperature): array
    {
        if ($temperature >= 30) {
            $mood = ['low', 'Canicule', 'Il fait une chaleur à fondre : tout le monde est grognon, vos excuses vont mal passer.', '🥵', 25];
        } elseif ($temperature <= 3) {
            $mood = ['low', 'Glacial', 'Il gèle dehors : moral en berne, vos excuses risquent de coincer.', '🥶', 25];
        } elseif ($temperature >= 18 && $temperature <= 26) {
            $mood = ['high', 'Idéale', 'Température parfaite : bonne humeur générale, c\'est le moment de tenter une excuse !', '😎', 85];
        } else {
            $mood = ['medium', 'Mitigée', 'Météo correcte : vos excuses ont une chance raisonnable de passer.', '🤔', 55];
        }

        return [
            'city' => self::CITY,
            'temperature' => round($temperature, 1),
            'indulgence' => $mood[0],
            'label' => $mood[1],
            'message' => $mood[2],
            'emoji' => $mood[3],
            'successRate' => $mood[4],
        ];
    }
}
