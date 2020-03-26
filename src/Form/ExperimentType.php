<?php

namespace App\Form;

use App\Entity\Experiment;
use App\Entity\Sensor;
use App\Scorpio\SensorManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentType extends AbstractType
{
    /** @var SensorManager */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('sensors', EntityType::class, [
                'class' => Sensor::class,
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->add('latitude')
            ->add('longitude')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Experiment::class,
        ]);
    }
}
