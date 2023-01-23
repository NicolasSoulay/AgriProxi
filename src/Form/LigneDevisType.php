<?php

namespace App\Form;

use App\Entity\LigneDevis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneDevisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('price');
        // ->add('name', TextType::class, [
        //     'label' => 'Nom'
        // ])
        // ->add('description', TextareaType::class, [
        //     'label' => 'Description',
        //     'required' => false
        // ])
        // ->add('Enregistrer', SubmitType::class)
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneDevis::class,
        ]);
    }
}
