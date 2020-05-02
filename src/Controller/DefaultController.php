<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("", name="default_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $onboardingCompleted = $request->cookies->get('onboarding-completed');
        if (empty($onboardingCompleted)) {
            return $this->redirectToRoute('cms_frontpage');
        }

        return $this->redirectToRoute('mission_index');
    }
}
