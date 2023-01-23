<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\SousCategorie;
use App\Repository\SousCategorieRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProduitCreationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit'
            ])
            ->add('inStock', CheckboxType::class, [
                'label' => 'En Stock',
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('imageURL', FileType::class, [
                'label' => 'Choisir une image',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Image(['maxSize' => '1024k'])
                ]
            ])
            ->add('subCategorie', EntityType::class, [
                'class' => SousCategorie::class,
                'choice_label' => 'name',
                'query_builder' => function (SousCategorieRepository $subCatRepo) {
                    return $subCatRepo->createQueryBuilder('sc')
                        ->orderBy('sc.name', 'ASC');
                },
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
