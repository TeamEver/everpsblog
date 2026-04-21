<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

final class AuthorType extends AbstractType
{
    private const META_TITLE_MAX_LENGTH = 70;
    private const META_DESCRIPTION_MAX_LENGTH = 160;
    private const SLUG_MAX_LENGTH = 128;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_employee', ChoiceType::class, [
                'required' => false,
                'label' => 'Employé lié',
                'placeholder' => 'Aucun employé lié',
                'choices' => $this->getEmployeeChoices(),
            ])
            ->add('nickhandle', TextType::class, [
                'required' => false,
                'label' => 'Pseudo',
                'constraints' => [new Length(['max' => 255])],
            ])
            ->add('twitter', TextType::class, [
                'required' => false,
                'label' => 'Twitter',
                'constraints' => [new Length(['max' => 255])],
            ])
            ->add('facebook', TextType::class, [
                'required' => false,
                'label' => 'Facebook',
                'constraints' => [new Length(['max' => 255])],
            ])
            ->add('linkedin', TextType::class, [
                'required' => false,
                'label' => 'LinkedIn',
                'constraints' => [new Length(['max' => 255])],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Actif',
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
            ->add('author_products', ChoiceType::class, [
                'required' => false,
                'label' => 'Produits liés',
                'choices' => $this->getProductChoices(),
                'multiple' => true,
                'expanded' => false,
                'attr' => ['data-ever-tagify' => '1'],
            ])
            ->add('author_image_file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Image auteur',
                'help' => $options['author_image_help'],
                'help_html' => true,
            ])
            ->add('delete_author_image', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'disabled' => !$options['has_author_image'],
                'label' => 'Supprimer l\'image auteur actuelle',
                'help' => $options['has_author_image']
                    ? 'Cochez puis enregistrez pour supprimer l\'image actuelle.'
                    : 'Aucune image auteur n\'est encore associee a cet auteur.',
            ])
        ;

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (langue #%d)', $idLang);

            $builder
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
                ->add(sprintf('excerpt_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Resume' . $suffix,
                    'constraints' => [new Length(['max' => 255])],
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
    private function getEmployeeChoices(): array
    {
        $rows = \Db::getInstance()->executeS(
            'SELECT e.id_employee, CONCAT(COALESCE(e.firstname, \'\'), \' \', COALESCE(e.lastname, \'\')) AS fullname
            FROM `' . _DB_PREFIX_ . 'employee` e
            ORDER BY e.id_employee DESC'
        ) ?: [];

        $choices = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id_employee'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $label = trim((string) ($row['fullname'] ?? ''));
            $choices[$label ?: sprintf('Employé #%d', $id)] = $id;
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'author_image_help' => '',
            'has_author_image' => false,
        ]);
        $resolver->setAllowedTypes('author_image_help', 'string');
        $resolver->setAllowedTypes('has_author_image', 'bool');
    }
}
