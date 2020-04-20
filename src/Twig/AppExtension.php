<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /** @var RequestStack */
    private $requestStack;

    /** @var RouterInterface */
    private $router;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_path', [$this, 'currentPath'], ['is_safe' => ['all']]),
            new TwigFunction('path_with_referer', [$this, 'getPathWithReferer']),
            new TwigFunction('path_from_referer', [$this, 'getPathFromReferer']),
        ];
    }

    public function currentPath(array $parameters = []): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->router->generate(
            $request->attributes->get('_route'),
            array_merge($request->attributes->get('_route_params'), $request->query->all(), $parameters)
        );
    }

    public function getPathWithReferer(string $route, array $parameters = []): string
    {
        if (!isset($parameters['referer'])) {
            $parameters['referer'] = $this->currentPath();
        }

        return $this->router->generate($route, $parameters);
    }

    public function getPathFromReferer(string $route, array $parameters = []): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $defaultPath = $this->router->generate($route, $parameters);

        return $request->query->get('referer', $defaultPath);
    }
}
