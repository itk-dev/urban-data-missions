<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @Route("/mock-up")
 */
class MockUpController extends AbstractController
{
    /**
     * @Route("/{path}", requirements={"path":".*"}, name="mockup")
     */
    public function mockup(string $path, Environment $twig): Response
    {
        $loader = $twig->getLoader();

        $parameters = [];
        if (empty($path)) {
            $path = 'index';

            if ($loader instanceof FilesystemLoader) {
                $pattern = '/\.html\.twig$/';
                $paths = array_map(static function (string $path) {
                    return $path.'/mock-up';
                }, $loader->getPaths());
                $paths = array_filter($paths, static function (string $path) {
                    return is_dir($path);
                });
                $finder = (new Finder())
                    ->in($paths)
                    ->filter(static function (SplFileInfo $file) use ($pattern) {
                        return false === strpos($file->getFilename(), 'index.html.twig')
                            && 1 === preg_match($pattern, $file->getFilename());
                    });
                /** @var SplFileInfo $file */
                foreach ($finder as $file) {
                    $parameters['paths'][] = preg_replace($pattern, '', $file->getRelativePathname());
                }
            }
        }

        $view = 'mock-up/'.$path.'.html.twig';
        if (!$loader->exists($view)) {
            throw new NotFoundHttpException();
        }

        return $this->render($view, $parameters);
    }
}
