<?php

namespace App\Form;

use App\Entity\Anagrafica;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AnagraficaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cognome', TextType::class , array('required' => true))
            ->add('nome', TextType::class , array('required' => false))
            ->add('tipo', ChoiceType::class, array(
                'choices'  => array(
                    'Privato' => 'privato',
                    'Azienda' => 'azienda',
                ),))
            ->add('luogo_nascita', TextType::class, array('required' => false))
            ->add('codice_fiscale', TextType::class, array(
                'required' => false,
                'constraints' => array( new Length( array( 'min' => 16, 'max' => 16, 'exactMessage' => 'Il codice fiscale deve avere 16 caratteri'
                )))))
            ->add('partita_iva', TextType::class, array(
                'required' => false,
                'constraints' => array( new Length( array( 'min' => 11, 'max' => 11, 'exactMessage' => 'La partita IVA deve avere 11 caratteri'
                )))))
            ->add('residenza', TextType::class, array('required' => false))
            ->add('sede_legale', TextType::class, array('required' => false))
            ->add('telefono', TextType::class, array('required' => false))
            ->add('email', EmailType::class, array('required' => false))
            ->add('codiceDestinatario', TextType::class, array('required' => false))
            ->add('note', TextareaType::class, array('required' => false))
            //->add('salva', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Anagrafica::class
        ]);
    }
}
