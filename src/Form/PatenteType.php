<?php

namespace App\Form;

use App\Entity\Anagrafica;
use App\Entity\Patente;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroPatente', TextType::class, array('required' => false))
            ->add('categoriaPatente', ChoiceType::class, array(
                'choices' => array(
                    'AM' => 'AM',
                    'A1' => 'A1',
                    'A2' => 'A2',
                    'A' => 'A',
                    'B' => 'B',
                    'B1' => 'B1',
                    'C' => 'C',
                    'C1' => 'C1',
                    'D' => 'D',
                    'D1' => 'D1',
                    'E' => 'E',
                ),
                'required' => true,
                'multiple' => true,
                'expanded' => true)
            )
            ->add('dataScadenzaPatente', DateType::class, array(
                'placeholder' => 'Seleziona una data',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => true
            ))
            ->add('note', TextareaType::class, array('required' => false))
            ->add('intestatario', EntityType::class, [
                'class' => Anagrafica::class,
                'choice_label' => 'cognome',
            ])
            //->add('salva', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Patente::class,
        ]);
    }
}
