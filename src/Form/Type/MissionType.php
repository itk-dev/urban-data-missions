<?php

namespace App\Form\Type;

use App\Entity\Mission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'help' => 'Give your mission an easy recognisable title',
            ])
            ->add('description', TextareaType::class, [
                'help' => 'Give a short description of what you want to achieve',
                'attr' => [
                    'rows' => 3,
                ],
            ])
            ->add('location', TextType::class, [
                'help' => 'Write the address or primary location of your mission',
            ])
            ->add('latitude', TextType::class)
            ->add('longitude', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
