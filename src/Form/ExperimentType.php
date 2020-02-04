<?php

namespace App\Form;

use App\Entity\Experiment;
use App\Scorpio\SensorManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $sensors = $this->sensorManager->getSensors();

        $builder
            ->add('title')
            ->add('sensors', ChoiceType::class, [
                'choices' => array_combine($sensors, $sensors),
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Experiment::class,
        ]);
    }
}
