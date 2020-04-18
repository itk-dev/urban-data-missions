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
        /** @var Sensor $sensor */
        $sensor = $builder->getData()->getSensor();
        $builder
            ->add('sensor_id', TextType::class, [
                'mapped' => false,
                'disabled' => true,
                'data' => (string) $sensor,
            ])
            ->add('sensor', EntityType::class, [
                'class' => Sensor::class,
                'choices' => [
                    'xx' => $sensor, // => 'xxx',, //->getId() => 'xxx',
                ],
//                'placeholder' => 'Pick a sensor',
                'data' => $sensor,
                'attr' => [
                    'readonly' => 'readony',
                ],
//                'disabled' => true,
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'help' => 'Give the sensor a name for this mission',
            ])
            ->add('enabled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MissionSensor::class,
        ]);
    }
}
