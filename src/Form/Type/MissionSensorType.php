<?php

namespace App\Form\Type;

use App\Entity\MissionSensor;
use App\Entity\Sensor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                'label' => $this->trans('Sensor'),
                'translation_domain' => false,
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
                'label' => $this->trans('Name'),
                'translation_domain' => false,
                'required' => false,
                'help' => $this->trans('Give the sensor a name for this mission'),
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => $this->trans('Enabled'),
                'translation_domain' => false,
                'required' => false,
            ])
            ->add('sensorWarnings', CollectionType::class, [
                'label' => $this->trans('Sensor warnings'),
                'translation_domain' => false,
                'help' => $this->trans('Sensor warning can help you detect invalid data.'),
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
