<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildContentTab($builder);
        $this->buildSeoTab($builder);
        $this->buildPublicationTab($builder);
        $this->buildTaxonomyTab($builder);
    }

    private function buildContentTab(FormBuilderInterface $builder): void
    {
        $contentTab = $builder->create('content_tab', FormType::class, [
            'inherit_data' => true,
            'label' => 'Contenu',
            'attr' => ['data-tab' => 'content'],
        ]);

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (langue #%d)', $idLang);

            $contentTab
                ->add(sprintf('title_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'Titre' . $suffix,
                ])
                ->add(sprintf('content_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Contenu' . $suffix,
                ])
                ->add(sprintf('excerpt_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Résumé' . $suffix,
                ])
            ;
        }

        $builder->add($contentTab);
    }

    private function buildSeoTab(FormBuilderInterface $builder): void
    {
        $seoTab = $builder->create('seo_tab', FormType::class, [
            'inherit_data' => true,
            'label' => 'SEO',
            'attr' => ['data-tab' => 'seo'],
        ]);

        $seoTab
            ->add('indexable', CheckboxType::class, [
                'required' => false,
                'label' => 'Indexable',
            ])
            ->add('follow', CheckboxType::class, [
                'required' => false,
                'label' => 'Follow',
            ])
            ->add('sitemap', CheckboxType::class, [
                'required' => false,
                'label' => 'Inclure dans le sitemap',
            ]);

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (langue #%d)', $idLang);

            $seoTab
                ->add(sprintf('meta_title_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'Meta title' . $suffix,
                ])
                ->add(sprintf('meta_description_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Meta description' . $suffix,
                ])
                ->add(sprintf('link_rewrite_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'URL simplifiée' . $suffix,
                ])
            ;
        }

        $builder->add($seoTab);
    }

    private function buildPublicationTab(FormBuilderInterface $builder): void
    {
        $publicationTab = $builder->create('publication_tab', FormType::class, [
            'inherit_data' => true,
            'label' => 'Publication',
            'attr' => ['data-tab' => 'publication'],
        ]);

        $publicationTab
            ->add('post_status', ChoiceType::class, [
                'required' => false,
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => 'draft',
                    'Publié' => 'published',
                ],
            ])
            ->add('date_add', DateTimeType::class, [
                'required' => false,
                'label' => 'Date de publication',
                'widget' => 'single_text',
                'input' => 'string',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('id_author', IntegerType::class, [
                'required' => false,
                'label' => 'ID auteur',
            ])
            ->add('starred', CheckboxType::class, [
                'required' => false,
                'label' => 'Mettre en avant',
            ])
            ->add('psswd', PasswordType::class, [
                'required' => false,
                'label' => 'Mot de passe',
                'empty_data' => '',
            ])
        ;

        $builder->add($publicationTab);
    }

    private function buildTaxonomyTab(FormBuilderInterface $builder): void
    {
        $taxonomyTab = $builder->create('taxonomy_tab', FormType::class, [
            'inherit_data' => true,
            'label' => 'Taxonomie',
            'attr' => ['data-tab' => 'taxonomy'],
        ]);

        $taxonomyTab
            ->add('id_default_category', IntegerType::class, [
                'required' => false,
                'label' => 'Catégorie par défaut (ID)',
            ])
            ->add('post_categories', CollectionType::class, [
                'required' => false,
                'label' => 'Catégories',
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('post_tags', CollectionType::class, [
                'required' => false,
                'label' => 'Tags',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('post_products', CollectionType::class, [
                'required' => false,
                'label' => 'Produits liés',
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('allowed_groups', CollectionType::class, [
                'required' => false,
                'label' => 'Groupes autorisés',
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
        ;

        $builder->add($taxonomyTab);
    }
}
