<?php

namespace App\Form\Type;

use App\Entity\MissionSensorWarning;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionSensorWarningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', IntegerType::class, [
                'label' => $this->trans('Minimum value'),
                'translation_domain' => false,
                'required' => false,
                'help' => $this->trans('Generate a warning if the sensor value is below this value.'),
            ])
            ->add('max', IntegerType::class, [
                'label' => $this->trans('Maximum value'),
                'translation_domain' => false,
                'required' => false,
                'help' => $this->trans('Generate a warning if the sensor value is above this value.'),
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->trans('Message'),
                'translation_domain' => false,
                'help' => $this->trans('The message to write in the log if the sensor value is outside the bounds. “%value%” will be replaced with the actual measured value when writing the log message.'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MissionSensorWarning::class,
        ]);
    }
}
