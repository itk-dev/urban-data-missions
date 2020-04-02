<?php

namespace App\Controller;

use App\Cms\PageManager;
use App\Entity\CmsPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cms", name="cms_")
 */
class CmsPageController extends AbstractController
{
    /** @var PageManager */
    private $pageManager;

    public function __construct(PageManager $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * @Route("", name="frontpage")
     */
    public function frontpage()
    {
        $frontPage = $this->pageManager->getFrontPage();

        if (null === $frontPage) {
            throw new NotFoundHttpException();
        }

        return $this->showPage($frontPage);
    }

    /**
     * @Route("/{id}", name="show", requirements={"id": ".+"})
     */
    public function show(string $id)
    {
        if ('/' === $id[-1]) {
            return $this->redirectToRoute('cms_show', ['id' => substr($id, 0, -1)]);
        }
        $page = $this->pageManager->findPage($id);

        if (null === $page) {
            throw new NotFoundHttpException();
        }

        return $this->showPage($page);
    }

    private function showPage(CmsPage $page)
    {
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

        [$precedingSiblings, $followingSiblings] = $this->pageManager->getSiblings($page);

        $view = $this->pageManager->getPageView($page);

        return $this->render($view, [
            'page' => $page,
            'path' => $path,
            'preceding_siblings' => $precedingSiblings,
            'following_siblings' => $followingSiblings,
        ]);
    }
}
