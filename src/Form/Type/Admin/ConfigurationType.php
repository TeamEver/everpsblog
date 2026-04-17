<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('route', TextType::class, [
                'label' => 'Route du blog',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('allow_comments', CheckboxType::class, [
                'label' => 'Autoriser les commentaires',
                'required' => false,
            ])
            ->add('check_comments', CheckboxType::class, [
                'label' => 'Modération des commentaires',
                'required' => false,
            ])
            ->add('posts_per_page', IntegerType::class, [
                'label' => 'Articles par page',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('home_posts', IntegerType::class, [
                'label' => 'Articles sur la page d\'accueil',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('product_posts', IntegerType::class, [
                'label' => 'Articles sur la fiche produit',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('excerpt_length', IntegerType::class, [
                'label' => 'Longueur de l\'extrait',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('title_length', IntegerType::class, [
                'label' => 'Longueur du titre',
                'constraints' => [new GreaterThan(['value' => 0])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'everpsblog_configuration',
        ]);
    }
}
