<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

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
            ])
            ->add('header_bg_color', TextType::class, [
                'label' => 'Couleur du header du blog',
                'required' => false,
                'help' => 'Couleur appliquee au bandeau principal des pages blog, categorie, tag, auteur et recherche.',
                'attr' => [
                    'type' => 'color',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^#[0-9a-fA-F]{6}$/',
                        'message' => 'La couleur doit etre au format hexadecimal, par exemple #0a0f54.',
                    ]),
                ],
            ])
            ->add('wordpress_api_url', TextType::class, [
                'label' => 'URL du site WordPress',
                'required' => false,
                'help' => 'Exemple : https://example.com ou https://example.com/wp-json/wp/v2',
                'constraints' => [
                    new Length(['max' => 255]),
                    new Url(),
                ],
            ])
            ->add('wordpress_api_user', TextType::class, [
                'label' => 'Identifiant REST WordPress',
                'required' => false,
                'help' => 'Facultatif pour importer les articles publics. Requis pour les contenus non publics.',
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ])
            ->add('wordpress_api_password', PasswordType::class, [
                'label' => 'Mot de passe d\'application WordPress',
                'required' => false,
                'always_empty' => false,
                'help' => 'Utilisez un mot de passe d\'application WordPress, pas le mot de passe principal.',
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ])
            ->add('wordpress_import_post_status', ChoiceType::class, [
                'label' => 'Statut des articles importes',
                'choices' => [
                    'Publie' => 'published',
                    'Brouillon' => 'draft',
                ],
            ])
            ->add('wordpress_enable_authors', CheckboxType::class, [
                'label' => 'Activer les auteurs importes',
                'required' => false,
            ])
            ->add('wordpress_enable_categories', CheckboxType::class, [
                'label' => 'Activer les categories importees',
                'required' => false,
            ])
            ->add('wordpress_enable_tags', CheckboxType::class, [
                'label' => 'Activer les tags importes',
                'required' => false,
            ]);

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (langue #%d)', $idLang);

            $builder
                ->add(sprintf('top_text_%d', $idLang), TextareaType::class, [
                    'label' => 'Texte haut de page blog' . $suffix,
                    'required' => false,
                    'attr' => [
                        'data-ever-richtext' => '1',
                        'rows' => 8,
                    ],
                ])
                ->add(sprintf('bottom_text_%d', $idLang), TextareaType::class, [
                    'label' => 'Texte bas de page blog' . $suffix,
                    'required' => false,
                    'attr' => [
                        'data-ever-richtext' => '1',
                        'rows' => 8,
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'everpsblog_configuration',
        ]);
    }
}
