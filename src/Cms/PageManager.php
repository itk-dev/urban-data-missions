<?php

namespace App\Cms;

use App\Entity\CmsPage;
use App\Repository\CmsPageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Twig\Environment;

class PageManager
{
    /** @var CmsPageRepository */
    private $pageRepository;

    /** @var Environment */
    private $environment;

    public function __construct(CmsPageRepository $pageRepository, Environment $environment)
    {
        $this->pageRepository = $pageRepository;
        $this->environment = $environment;
    }

    /**
     * Find a page by slug or id.
     *
     * @param string|int $slug
     */
    public function findPage($slug): ?CmsPage
    {
        return $this->pageRepository->findOneBy(['slug' => $slug])
            ?? $this->pageRepository->find($slug);
    }

    /**
     * @return array[]
     */
    public function getSiblings(CmsPage $page, ?bool $published = true): array
    {
        $children = $this->getChildren($page->getParent(), $published);
        $index = $children->indexOf($page);

        return [$children->slice(0, $index), $children->slice($index + 1)];
    }

    public function getFrontPage(): ?CmsPage
    {
        $pages = $this->getChildren();

        return $pages->count() > 0 ? $pages->first() : null;
    }

    /**
     * Get a twig view for rendering a page.
     *
     * @param Page $page
     */
    public function getPageView(CmsPage $page): string
    {
        $loader = $this->environment->getLoader();
        foreach ([
                     'cms/page/show-'.$page->getType().'.html.twig',
                     'cms/page/show.html.twig',
                 ] as $view) {
            if ($loader->exists($view)) {
                return $view;
            }
        }

        throw new RuntimeException('Cannot find page template');
    }

    /**
     * @return Collection|CmsPage[]
     */
    private function getChildren(CmsPage $page = null, ?bool $published = true): Collection
    {
        $criteria = ['parent' => $page];
        if (null !== $published) {
            $criteria = ['published' => $published];
        }

        return new ArrayCollection($this->pageRepository->findBy($criteria, ['position' => 'ASC']));
    }
}
