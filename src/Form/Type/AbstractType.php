<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractType extends BaseAbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
