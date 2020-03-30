<?php

namespace App\Controller;

use App\Entity\MissionTheme;
use App\Repository\MissionThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/theme", name="theme_")
 */
class MissionThemeController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(MissionThemeRepository $missionThemeRepository): Response
    {
        return $this->render('mission_theme/index.html.twig', [
            'themes' => $missionThemeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="mission_theme_show", methods={"GET"})
     */
    public function show(MissionTheme $missionTheme): Response
    {
        return $this->render('mission_theme/show.html.twig', [
            'theme' => $missionTheme,
        ]);
    }
}
