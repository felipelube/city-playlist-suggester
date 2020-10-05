<?php

namespace App\Services;

use App\Exception\InvalidInputException;
use STS\Backoff\Backoff;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SpotifyClient
{
    /**
     * O ID de cliente do Spotify.
     * Essa informação pode ser obtida em https://developer.spotify.com/dashboard/applications.
     *
     * @var string
     */
    private $clientId;
    /**
     * O segredo do cliente do Spotify
     * Essa informação pode ser obtida em https://developer.spotify.com/dashboard/applications/<id_da_aplicacao>.
     *
     * @var string
     */
    private $clientSecret;

    /**
     * Uma implementação de cliente HTTP.
     *
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * Uma implementação de um adaptador de contrato de cache do Symfony.
     *
     * @var AdapterInterface
     */
    private $cache;

    // Endpoint para geração/regeneração de tokens de acesso
    public const SPOTIFY_ACCESS_TOKEN_ENDPOINT = 'https://accounts.spotify.com/api/token';
    // Tipo de autorização para geração de token de acesso no fluxo Client Credentials
    public const SPOTIFY_GRANT_TYPE = 'client_credentials';
    // o nome da chave no cache a qual guardará o token de acesso
    public const CACHE_ACCESS_TOKEN_KEY = 'spotify_access_token';
    // número máximo de tentativas de conexão com a API
    public const MAX_RETRIES = 5;

    /**
     * Constrói uma instância do serviço SpotifyPlaylistGetter.
     *
     * @param string              $clientId     o ID de cliente do Spotify
     * @param string              $clientSecret o segredo do cliente do Spotify
     * @param HttpClientInterface $httpClient   uma implementação de cliente HTTP
     * @param AdapterInterface    $cache        uma implementação de um adaptador de contrato de cache do Symfony
     *
     * @return SpotifyClient
     *
     * @throws InvalidInputException
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        HttpClientInterface $httpClient,
        AdapterInterface $cache
    ) {
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

    /**
     * Gera um novo token de acesso junto à API do Spotify.
     *
     * @return ResponseInterface
     */
    protected function refreshAccessToken()
    {
        $authorizationValue = \base64_encode("{$this->clientId}:{$this->clientSecret}");
        $response = $this->httpClient->request('POST', self::SPOTIFY_ACCESS_TOKEN_ENDPOINT, [
                'body' => [
                'grant_type' => self::SPOTIFY_GRANT_TYPE,
            ],
            'headers' => [
                'Authorization' => "Basic {$authorizationValue}",
            ],
        ]);

        return $response;
    }

    /**
     * Utiliza um token de acesso guardado no cache ou gera/regenera um.
     *
     * @return string
     */
    protected function getAccessToken()
    {
        return $this->cache->get(self::CACHE_ACCESS_TOKEN_KEY, function (ItemInterface $item) {
            $accessTokenInfo = $this->refreshAccessToken()->toArray();
            $item->expiresAfter($accessTokenInfo['expires_in']);

            return $accessTokenInfo['access_token'];
        });
    }

    /**
     * Faz uma requisição autenticada para a API do Spotify.
     * Lida com a geração/regeneração do token de acesso de forma transparente.
     *
     * @param string $method  O método da requisição
     * @param string $url     A URL
     * @param array  $options Os dados e metadados da requisição
     *
     * @return ResponseInterface
     */
    public function authenticatedRequest(string $method, string $url, array $options)
    {
        //TODO: implementar esta estratégia de backoff numa classe à parte
        return Backoff(function () use ($method, $url, $options) {
            return $this->httpClient->request($method, $url, array_merge($options, [
                'headers' => [
                    'Authorization' => "Bearer {$this->getAccessToken()}",
                ],
            ]));
        }, self::MAX_RETRIES, 'exponential', 30000, true);
    }
}
