<?php

namespace App\Services;

use App\Exception\InvalidInputException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TODO: desmembrar o cliente desta classe.
 */

/**
 * Serviço que encapsula a busca de uma playlist no serviço Spotify.
 *
 * Responsabilidades:
 * - Busca de playlist de acordo com gênero musical
 * - Controle do token de acesso necessário para autenticar-se na API: geração e regeneração
 * - Utilização de cache para evitar o uso excessivo da API.
 */
class SpotifyPlaylistGetter
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
    const SPOTIFY_ACCESS_TOKEN_ENDPOINT = 'https://accounts.spotify.com/api/token';
    // Endpoint para pegar recomendações de músicas (playlist) de acordo com um gênero musical
    const SPOTIFY_RECOMMENDATIONS_ENDPOINT = 'https://api.spotify.com/v1/recommendations';
    // Tipo de autorização para geração de token de acesso no fluxo Client Credentials
    const SPOTIFY_GRANT_TYPE = 'client_credentials';

    /**
     * Constrói uma instância do serviço SpotifyPlaylistGetter.
     *
     * @param string              $clientId     o ID de cliente do Spotify
     * @param string              $clientSecret o segredo do cliente do Spotify
     * @param HttpClientInterface $httpClient   uma implementação de cliente HTTP
     * @param AdapterInterface    $cache        uma implementação de um adaptador de contrato de cache do Symfony
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
     * @return void
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
            ], //TODO: fazer método para criar essa requisição autorizada
        ]);

        return $response;
    }

    /**
     * Utiliza um token de acesso guardado no cache ou gera/regenera um.
     *
     * @return void
     */
    protected function getAccessToken()
    {
        return $this->cache->get('config_spotify_access_token', function (ItemInterface $item) {
            $accessTokenInfo = $this->refreshAccessToken()->toArray();
            $item->expiresAfter($accessTokenInfo['expires_in']);

            return $accessTokenInfo['access_token'];
        });
    }

    /**
     * Gera uma playlist no formato Artista(s) - Título com as músicas recomendadas para
     * um determinado gênero musical.
     *
     * @param string $genre O gênero musical
     *
     * @return Array[string]
     */
    public function getPlaylistForGenre(string $genre)
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

            // Transforma a resposta da API numa lista no formato Artista(s) - Título
            return array_map(function ($track) {
                $title = $track['name'];
                // Separa os artistas por vírgula
                $artists = implode(', ', array_map(function ($artist) {
                    return $artist['name'];
                }, $track['artists']));

                return "{$artists} - $title";
            }, $response['tracks']);
        });
    }
}
