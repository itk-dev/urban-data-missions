<?php

namespace App\Form\Type;

use App\Entity\MissionSensor;
use App\Entity\Sensor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('sensor', EntityType::class, [
                'block_name' => 'sensor_details',
                'class' => Sensor::class,
                'choices' => [
                    $sensor->getId() => $sensor,
                ],
                'attr' => [
                    'readonly' => 'readonly',
                ],
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'help' => 'Give the sensor a name for this mission',
            ])
            ->add('enabled')
            ->add('sensorWarnings', CollectionType::class, [
                'help' => 'Sensor warning can help you detect invalid data.',
                'entry_type' => MissionSensorWarningType::class,
                'entry_options' => [
                    'label' => false,
                    'block_prefix' => 'mission_sensor_sensor_warning_item',
                ],
                'block_prefix' => 'mission_sensor_warning',
                'block_name' => 'sensor_warnings',
                'allow_add' => true,
                'allow_delete' => true,
                // Apparently this is needed to set mission_sensor_id correctly, but why?
                'by_reference' => false,
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
