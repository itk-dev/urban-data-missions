<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mock-up")
 */
class MockUpController extends AbstractController
{
    /**
     * @Route("/{path}", requirements={"path":".*"})
     */
    public function mockup(string $path): Response
    {
        if (empty($path)) {
            $path = 'index';
        }

        return $this->render('mock-up/'.$path.'.html.twig');
    }
}
