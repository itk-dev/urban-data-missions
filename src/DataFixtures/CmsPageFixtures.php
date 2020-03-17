<?php

namespace App\DataFixtures;

use App\Entity\CmsPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CmsPageFixtures extends Fixture implements FixtureGroupInterface
{
    /** @var Filesystem */
    private $filesystem;

    /** @var array */
    private $options;

    public function __construct(Filesystem $filesystem, ParameterBagInterface $parameters)
    {
        $this->filesystem = $filesystem;
        $this->options = [
            'images_path' => rtrim($parameters->get('app.path.cms_images'), '/'),
            'project_dir' => $parameters->get('kernel.project_dir'),
        ];
    }

    public function load(ObjectManager $manager)
    {
        $pages = [
            [
                'title' => 'Urban data mission',
                'slug' => 'onboarding',
                'type' => 'onboarding',
                'published' => true,
                'content' => '',
            ],

            [
                'title' => 'Welcome',
                'parent' => 'onboarding',
                'position' => 0,
                'slug' => 'onboarding/welcome',
                'type' => 'onboarding',
                'published' => true,
                'content' => '',
                'image' => 'hest.png',
            ],

            [
                'title' => 'Explore',
                'parent' => 'onboarding',
                'position' => 1,
                'slug' => 'onboarding/explore',
                'type' => 'onboarding',
                'published' => true,
                'content' => '',
            ],

            [
                'title' => 'Investigate',
                'parent' => 'onboarding',
                'position' => 2,
                'slug' => 'onboarding/investigate',
                'type' => 'onboarding',
                'published' => true,
                'content' => '',
            ],
        ];

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($pages as $data) {
            $page = new CmsPage();
            foreach ($data as $key => $value) {
                if ('image' === $key) {
                    $this->filesystem->copy(
                        __DIR__.'/images/'.$value,
                        $this->options['project_dir'].'/public/'.$this->options['images_path'].'/'.$value,
                    );
                } elseif ('parent' === $key) {
                    $value = $this->getReference('page:'.$value);
                }

                $accessor->setValue($page, $key, $value);
                if ('slug' === $key) {
                    $this->addReference('page:'.$value, $page);
                }
            }
            $manager->persist($page);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['cms'];
    }
}
