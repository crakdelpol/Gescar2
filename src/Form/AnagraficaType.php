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
use Symfony\Component\Validator\Constraints\NotBlank;

class AnagraficaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cognome', TextType::class , array(
                'required' => true,
                'constraints' => [new NotBlank(['message' => 'Il cognome è obbligatorio'])],
            ))
            ->add('nome', TextType::class , array('required' => false))
            ->add('tipo', ChoiceType::class, array(
                'choices'  => array(
                    'Privato' => 'privato',
                    'Azienda' => 'azienda',
                ),))
            ->add('luogoNascita', TextType::class, array('required' => false))
            ->add('codiceFiscale', TextType::class, array(
                'required' => false,
                'constraints' => array( new Length( array( 'min' => 16, 'max' => 16, 'exactMessage' => 'Il codice fiscale deve avere 16 caratteri'
                )))))
            ->add('partitaIva', TextType::class, array(
                'required' => false,
                'constraints' => array( new Length( array( 'min' => 11, 'max' => 11, 'exactMessage' => 'La partita IVA deve avere 11 caratteri'
                )))))
            ->add('residenza', TextType::class, array('required' => false))
            ->add('sedeLegale', TextType::class, array('required' => false))
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
