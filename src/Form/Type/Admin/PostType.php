<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildContentTab($builder);
        $this->buildSeoTab($builder);
        $this->buildPublicationTab($builder, $options);
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

    private function buildPublicationTab(FormBuilderInterface $builder, array $options): void
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
                    'Corbeille' => 'trash',
                    'Publié' => 'published',
                ],
            ])
            ->add('date_add', TextType::class, [
                'required' => false,
                'label' => 'Date de publication',
                'empty_data' => '',
                'help' => 'Si la date est future et que l\'article est publie, il sera programme automatiquement.',
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'YYYY-MM-DD HH:mm',
                    'data-ever-datetime' => '1',
                ],
            ])
            ->add('id_author', ChoiceType::class, [
                'required' => false,
                'label' => 'Auteur',
                'placeholder' => 'Sélectionnez un auteur',
                'choices' => $this->getAuthorChoices(),
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
            ->add('featured_image_file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Image à la une',
                'help' => $options['featured_image_help'],
                'help_html' => true,
            ])
            ->add('delete_featured_image', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'disabled' => !$options['has_featured_image'],
                'label' => 'Supprimer l\'image a la une actuelle',
                'help' => $options['has_featured_image']
                    ? 'Cochez puis enregistrez pour supprimer l\'image actuelle.'
                    : 'Aucune image a la une n\'est encore associee a cet article.',
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
            ->add('id_default_category', ChoiceType::class, [
                'required' => false,
                'label' => 'Catégorie par défaut',
                'placeholder' => 'Sélectionnez une catégorie',
                'choices' => $this->getCategoryChoices(),
            ])
            ->add('post_categories', ChoiceType::class, [
                'required' => false,
                'label' => 'Catégories',
                'choices' => $this->getCategoryChoices(),
                'multiple' => true,
                'expanded' => false,
                'attr' => ['data-ever-tagify' => '1'],
            ])
            ->add('post_tags', ChoiceType::class, [
                'required' => false,
                'label' => 'Tags',
                'choices' => $this->getTagChoices(),
                'multiple' => true,
                'expanded' => false,
                'attr' => ['data-ever-tagify' => '1'],
            ])
            ->add('post_products', ChoiceType::class, [
                'required' => false,
                'label' => 'Produits liés',
                'choices' => $this->getProductChoices(),
                'multiple' => true,
                'expanded' => false,
                'attr' => ['data-ever-tagify' => '1'],
            ])
            ->add('allowed_groups', ChoiceType::class, [
                'required' => false,
                'label' => 'Groupes autorisés',
                'choices' => $this->getGroupChoices(),
                'multiple' => true,
                'expanded' => true,
            ])
        ;

        $builder->add($taxonomyTab);
    }

    /**
     * @return array<string, int>
     */
    private function getAuthorChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT a.id_ever_author, a.nickhandle, al.meta_title, CONCAT(COALESCE(e.firstname, \'\'), \' \', COALESCE(e.lastname, \'\')) AS employee_name
            FROM `' . _DB_PREFIX_ . 'ever_blog_author` a
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_author_lang` al ON (al.id_ever_author = a.id_ever_author AND al.id_lang = ' . (int) \Context::getContext()->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON (e.id_employee = a.id_employee)
            WHERE a.active = 1
            ORDER BY a.nickhandle ASC, a.id_ever_author ASC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_ever_author'];
            $label = trim((string) ($row['nickhandle'] ?? ''));
            if ('' === $label) {
                $label = trim((string) ($row['employee_name'] ?? ''));
            }
            if ('' === $label) {
                $label = trim((string) ($row['meta_title'] ?? ''));
            }

            $choices[sprintf('%s (#%d)', $label ?: 'Auteur', $id)] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    private function getCategoryChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT c.id_ever_category, cl.title
            FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` cl ON (cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) \Context::getContext()->language->id . ')
            WHERE c.active = 1
            ORDER BY c.id_ever_category ASC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_ever_category'];
            $label = trim((string) ($row['title'] ?? ''));
            $choices[$label ?: sprintf('Catégorie #%d', $id)] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    private function getTagChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT t.id_ever_tag, tl.title
            FROM `' . _DB_PREFIX_ . 'ever_blog_tag` t
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_tag_lang` tl ON (tl.id_ever_tag = t.id_ever_tag AND tl.id_lang = ' . (int) \Context::getContext()->language->id . ')
            WHERE t.active = 1
            ORDER BY t.id_ever_tag ASC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_ever_tag'];
            $label = trim((string) ($row['title'] ?? ''));
            $choices[$label ?: sprintf('Tag #%d', $id)] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    private function getProductChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT p.id_product, pl.name
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) \Context::getContext()->language->id . ' AND pl.id_shop = ' . (int) \Context::getContext()->shop->id . ')
            ORDER BY p.id_product DESC
            LIMIT 500'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) $row['id_product'];
            $label = trim((string) ($row['name'] ?? ''));
            $choices[$label ?: sprintf('Produit #%d', $id)] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, int>
     */
    private function getGroupChoices(): array
    {
        $groups = \Group::getGroups((int) \Context::getContext()->language->id) ?: [];
        $choices = [];

        foreach ($groups as $group) {
            $id = (int) ($group['id_group'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $label = trim((string) ($group['name'] ?? ''));
            $choices[$label ?: sprintf('Groupe #%d', $id)] = $id;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'featured_image_help' => '',
            'has_featured_image' => false,
        ]);
        $resolver->setAllowedTypes('featured_image_help', 'string');
        $resolver->setAllowedTypes('has_featured_image', 'bool');
    }
}
