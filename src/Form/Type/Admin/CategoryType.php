<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

final class CategoryType extends AbstractType
{
    private const META_TITLE_MAX_LENGTH = 70;
    private const META_DESCRIPTION_MAX_LENGTH = 160;
    private const SLUG_MAX_LENGTH = 128;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_parent_category', ChoiceType::class, [
                'required' => false,
                'label' => 'Catégorie parente',
                'placeholder' => 'Aucune (racine)',
                'choices' => $this->getParentCategoryChoices(),
            ])
            ->add('is_root_category', CheckboxType::class, [
                'required' => false,
                'label' => 'Catégorie racine',
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Active',
            ])
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
            ])
            ->add('count', IntegerType::class, [
                'required' => false,
                'label' => 'Compteur',
                'disabled' => true,
            ])
            ->add('allowed_groups', ChoiceType::class, [
                'required' => false,
                'label' => 'Groupes autorisés',
                'choices' => $this->getGroupChoices(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('category_products', ChoiceType::class, [
                'required' => false,
                'label' => 'Produits liés',
                'choices' => $this->getProductChoices(),
                'multiple' => true,
                'expanded' => false,
                'attr' => ['data-ever-tagify' => '1'],
            ])
        ;

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (langue #%d)', $idLang);

            $builder
                ->add(sprintf('title_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'Titre' . $suffix,
                    'constraints' => [new Length(['max' => 255])],
                ])
                ->add(sprintf('meta_title_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'Meta title' . $suffix,
                    'constraints' => [new Length(['max' => self::META_TITLE_MAX_LENGTH])],
                ])
                ->add(sprintf('meta_description_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Meta description' . $suffix,
                    'constraints' => [new Length(['max' => self::META_DESCRIPTION_MAX_LENGTH])],
                ])
                ->add(sprintf('link_rewrite_%d', $idLang), TextType::class, [
                    'required' => false,
                    'label' => 'Slug' . $suffix,
                    'constraints' => [
                        new Length(['max' => self::SLUG_MAX_LENGTH]),
                        new Regex([
                            'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                            'message' => 'Le slug doit contenir uniquement des lettres, chiffres et tirets.',
                        ]),
                    ],
                ])
                ->add(sprintf('content_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Contenu' . $suffix,
                ])
                ->add(sprintf('bottom_content_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Contenu bas de page' . $suffix,
                ])
            ;
        }
    }

    /**
     * @return array<string, int>
     */
    private function getParentCategoryChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT c.id_ever_category, cl.title
            FROM `' . _DB_PREFIX_ . 'ever_blog_category` c
            LEFT JOIN `' . _DB_PREFIX_ . 'ever_blog_category_lang` cl ON (cl.id_ever_category = c.id_ever_category AND cl.id_lang = ' . (int) \Context::getContext()->language->id . ')
            ORDER BY c.id_ever_category ASC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id_ever_category'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $label = trim((string) ($row['title'] ?? ''));
            $choices[$label ?: sprintf('Catégorie #%d', $id)] = $id;
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
            $id = (int) ($row['id_product'] ?? 0);
            if ($id <= 0) {
                continue;
            }

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
}
