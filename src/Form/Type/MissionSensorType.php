<?php

namespace App\Form\Type;

use App\Entity\MissionSensor;
use App\Entity\Sensor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionSensorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sensor', EntityType::class, [
                'class' => Sensor::class,
                'placeholder' => 'Pick a sensor',
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'help' => 'Give the sensors a name for this mission',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MissionSensor::class,
        ]);
    }
}
