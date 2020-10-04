<?php

namespace App\Services;

use App\Exception\InvalidInputException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyPlaylistGetter
{
    private $clientId;
    private $clientSecret;
    private $httpClient;
    private $cache;

    const SPOTIFY_ACCESS_TOKEN_ENDPOINT = 'https://accounts.spotify.com/api/token';
    const SPOTIFY_RECOMMENDATIONS_ENDPOINT = 'https://api.spotify.com/v1/recommendations';
    const SPOTIFY_GRANT_TYPE = 'client_credentials';

    public function __construct(string $clientId, string $clientSecret, HttpClientInterface $httpClient, AdapterInterface $cache)
    {
        if (!is_string($clientId) || empty($clientId)) {
            throw new InvalidInputException('SpotifyPlaylistGetter: você precisa definir um clientId para usar este serviço.');
        }

        if (!is_string($clientSecret) || empty($clientSecret)) {
            throw new InvalidInputException('SpotifyPlaylistGetter: você precisa definir um clientSecret para usar este serviço.');
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    protected function refreshAccessToken()
    {
        $authorizationValue = \base64_encode("{$this->clientId}:{$this->clientSecret}");
        $response = $this->httpClient->request('POST', self::SPOTIFY_ACCESS_TOKEN_ENDPOINT, [
                'body' => [
                'grant_type' => self::SPOTIFY_GRANT_TYPE,
            ],
            'headers' => [
                'Authorization' => "Basic {$authorizationValue}",
            ], //TODO: fazer método para criar essa requisição autorizada
        ]);

        return $response;
    }

    protected function getAccessToken()
    {
        return $this->cache->get('config_spotify_access_token', function (ItemInterface $item) {
            $accessTokenInfo = $this->refreshAccessToken()->toArray();
            $item->expiresAfter($accessTokenInfo['expires_in']);

            return $accessTokenInfo['access_token'];
        });
    }

    public function getPlaylistForGenre($genre)
    {
        // TODO: usar uma resposta cacheada apenas como último recurso ou reduzir o TTL do cache
        return $this->cache->get("genre-$genre", function (ItemInterface $item) use ($genre) {
            $response = $this->httpClient->request('GET', self::SPOTIFY_RECOMMENDATIONS_ENDPOINT, [
                'headers' => [
                'Authorization' => "Bearer {$this->getAccessToken()}",
                ],
                'query' => [
                'seed_genres' => $genre,
                'target_popularity' => 70,
                ],
            ])->toArray();
            $item->expiresAfter(3600); //TODO: parametrizar

            return array_map(function ($track) {
                $title = $track['name'];
                $artists = implode(', ', array_map(function ($artist) {
                    return $artist['name'];
                }, $track['artists']));

                return "{$artists} - $title";
            }, $response['tracks']);
        });
    }
}
