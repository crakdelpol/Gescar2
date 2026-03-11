<?php

namespace App\Form;

use App\Entity\Bollo;
use App\Entity\Vettura;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BolloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dataScadenzaBollo', DateType::class, array(
                'placeholder' => 'Seleziona una data',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => true
            ))
            ->add('note', TextareaType::class, array('required' => false))
            ->add('vettura', EntityType::class, [
                'class' => Vettura::class,
                'choice_label' => 'id',
            ])
            //->add('salva', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bollo::class,
        ]);
    }
}
