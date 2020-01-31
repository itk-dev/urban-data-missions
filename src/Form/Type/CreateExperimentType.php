<?php


namespace App\Form\Type;

use App\Broker\SensorManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateExperimentType extends AbstractType
{
    /** @var SensorManager  */
    private $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sensors = $this->sensorManager->getSensors();
        $builder
            ->add('title', TextType::class)
            ->add('sensors', ChoiceType::class, [
                'choices' => array_combine($sensors, $sensors),
                'expanded' => true,
                'multiple' => true,
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create experiment',
            ])
        ;
    }
}
