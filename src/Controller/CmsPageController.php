<?php

namespace App\Controller;

use App\Cms\PageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cms", name="cms_")
 */
class CmsPageController extends AbstractController
{
    /**
     * @Route("", name="frontpage")
     */
    public function frontpage(PageManager $pageManager)
    {
        $frontPage = $pageManager->getFrontPage();

        if (null === $frontPage) {
            throw new NotFoundHttpException();
        }

        return $this->show($frontPage, $pageManager);
    }

    /**
     * @Route("/{id}", name="show", requirements={"id": ".+"})
     */
    public function show($id, PageManager $pageManager)
    {
        $page = $pageManager->findPage($id);

        if (null === $page) {
            throw new NotFoundHttpException();
        }

        $path = [];
        $previousPage = null;
        $nextPage = null;

        if (null !== $page->getParent()) {
            $parent = $page->getParent();
            do {
                $path[] = $parent;
                $parent = $parent->getParent();
            } while (null !== $parent);
            $path = array_reverse($path);
        }

        [$precedingSiblings, $followingSiblings] = $pageManager->getSiblings($page);

        $view = $pageManager->getPageView($page);

        return $this->render($view, [
            'page' => $page,
            'path' => $path,
            'preceding_siblings' => $precedingSiblings,
            'following_siblings' => $followingSiblings,
        ]);
    }
}
