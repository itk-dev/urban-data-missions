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
                'content' => '<p>Byens klimatilstand</p>',
                'image' => 'onboarding_1_presentation@2x.png',
            ],

            [
                'title' => 'Velkommen!',
                'parent' => 'onboarding',
                'position' => 0,
                'slug' => 'onboarding/step-1',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p>Aarhus kommune har identificeret en række sensorforsøg, som kan gøre dig klogere på byens klimatilstand og gøre dig i stand til at tage aktiv stilling til din klimapåvirkning.
                Er du klar til at blive en af byens datadetektiver?</p>',
                'image' => 'onboarding_2_chart-graphic@2x.png',
            ],

            [
                'title' => '',
                'parent' => 'onboarding',
                'position' => 1,
                'slug' => 'onboarding/step-2',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p>Du finder alle de forskellige forsøg i Aarhus på kortet. De er markeret med en lilla prik <span style="display: inline-block; width: 10px; height: 10px; background-color: #685ff3; border-radius: 50%;"></span></p>',
                'image' => 'onboarding_3_map@2x.png',
            ],

            [
                'title' => '',
                'parent' => 'onboarding',
                'position' => 2,
                'slug' => 'onboarding/step-3',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p></p>Når du vælger et forsøg, kan du se flere detaljer om udfordringen, og du kan  fortsætte til forsøget.</p>',
                'image' => 'onboarding_4_map-popover@2x.png',
            ],

            [
                'title' => '',
                'parent' => 'onboarding',
                'position' => 3,
                'slug' => 'onboarding/step-4',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p>Inde på forsøget kan du se forsøgets data. I eksemplet her kan du se luftkvalitets forsøget på en graf.</p>',
                'image' => 'onboarding_5_amchart-1@2x.png',
            ],

            [
                'title' => '',
                'parent' => 'onboarding',
                'position' => 4,
                'slug' => 'onboarding/step-5',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p>I det valgte forsøg kan du aktivere data fra forskellige sensorer for at sammenligne dem. Få vist en større graf ved at vende skærmen vandret.</p>',
                'image' => 'onboarding_6_amchart-2@2x.png',
            ],

            [
                'title' => '',
                'parent' => 'onboarding',
                'position' => 5,
                'slug' => 'onboarding/step-6',
                'type' => 'onboarding',
                'published' => true,
                'content' => '<p>Du kan nu gå i gang med byens udfordringer!</p>',
                'image' => 'onboarding_7_chart-graphic-2@2x.png',
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
