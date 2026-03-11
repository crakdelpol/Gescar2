<?php

namespace App\Form;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Entity\Vettura;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('anagrafica', EntityType::class, [
                'class'        => Anagrafica::class,
                'choice_label' => fn(Anagrafica $a) => $a->getCognome() . ' ' . $a->getNome(),
                'label'        => 'Cliente',
                'placeholder'  => '— Seleziona cliente —',
                'attr'         => ['class' => 'form-select'],
            ])
            ->add('vettura', EntityType::class, [
                'class'        => Vettura::class,
                'choice_label' => fn(Vettura $v) => $v->getTarga() . ' – ' . $v->getMarca() . ' ' . $v->getModello(),
                'label'        => 'Veicolo',
                'placeholder'  => '— Seleziona veicolo (opzionale) —',
                'required'     => false,
                'attr'         => ['class' => 'form-select'],
            ])
            ->add('tipoScadenza', ChoiceType::class, [
                'label'   => 'Tipo scadenza',
                'choices' => Notifica::getTipiScadenza(),
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('canale', ChoiceType::class, [
                'label'   => 'Canale',
                'choices' => Notifica::getCanali(),
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('esito', ChoiceType::class, [
                'label'    => 'Esito',
                'choices'  => Notifica::getEsiti(),
                'required' => false,
                'placeholder' => '— In attesa —',
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('dataInvio', DateTimeType::class, [
                'label'        => 'Data/ora',
                'widget'       => 'single_text',
                'input'        => 'datetime',
                'html5'        => true,
                'required'     => true,
                'attr'         => ['class' => 'form-control'],
            ])
            ->add('note', TextareaType::class, [
                'label'    => 'Note (esito chiamata, appuntamento, ecc.)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notifica::class,
        ]);
    }
}
