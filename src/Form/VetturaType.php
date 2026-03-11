<?php

namespace App\Form;

use App\Entity\Anagrafica;
use App\Entity\Vettura;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VetturaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('targa', TextType::class, array('required' => false))
            ->add('numeroTelaio', TextType::class, array('required' => false))
            ->add('tipo', ChoiceType::class, array(
                'required' => true,
                'choices' => array(
                    'Tipo Vettura' => null,
                    'Autovettura' => 'autovettura',
                    'Ciclomotore' => 'ciclomotore',
                    'Motoveicolo' => 'motoveicolo',
                    'Autocarro' => 'autocarro',
                    'Rimorchio' => 'rimorchio',
                )
            ))
            ->add('carburante', ChoiceType::class, array(
                'required' => true,
                'choices' => array(
                    'Carburante' => null,
                    'Benzina' => 'benzina',
                    'Gasolio' => 'gasolio',
                    'GPL' => 'gpl',
                    'Metano' => 'metano',
                    'Elettrico' => 'elettrico',
                )
            ))
            ->add('marca', TextType::class, array('required' => false))
            ->add('modello', TextType::class, array('required' => false))
            ->add('dataUltimaRevisione', DateType::class, array(
                'placeholder' => 'Seleziona una data',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false
            ))
            ->add('dataScadenzaRevisione', DateType::class, array(
                'placeholder' => 'Seleziona una data',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false
            ))
            ->add('dataScadenzaImpianto', DateType::class, array(
                'placeholder' => 'Seleziona una data',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false
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
            'data_class' => Vettura::class,
        ]);
    }
}
