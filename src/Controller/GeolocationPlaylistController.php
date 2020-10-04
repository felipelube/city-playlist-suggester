<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GeolocationPlaylistController extends AbstractController
{
    /**
     * @Route("/geolocation/playlist", name="geolocation_playlist")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GeolocationPlaylistController.php',
        ]);
    }
}
