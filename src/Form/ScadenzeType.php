<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScadenzaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dataScadenza', DateType::class, array(
                'label' => ' Da ',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => true

            ))
            ->add('aDataScadenza', DateType::class, array(
                'label' => ' A ',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => true
            ))
            ->add('cerca', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_scadenza_type';
    }
}
