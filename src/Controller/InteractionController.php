<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InteractionController extends AbstractController
{
    #[Route('/interaction', name: 'app_interaction')]
    public function index(): Response
    {
        return $this->render('interaction/index.html.twig', [
            'controller_name' => 'InteractionController',
        ]);
    }
}
