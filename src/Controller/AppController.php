<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/app")
 */
class AppController extends AbstractController
{
    /**
     * @Route("", name="app", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    /**
     * @Route("/guide", name="app_guide", methods={"GET"})
     */
    public function guide(Request $request): Response
    {
        $pages = [];

        return $this->render('app/guide.html.twig', [
            'pages' => $pages,
        ]);
    }
}
