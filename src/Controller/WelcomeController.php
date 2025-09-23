<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController

{
    #[Route('/Welcomecontroller', name: 'welcomecontroller/index.html.twig')]
    public function index(): Response
    {
        return $this->render('welcomecontroller/index.html.twig', [
            'controller_name' => 'WelcomeController',
        ]);
    }
}
