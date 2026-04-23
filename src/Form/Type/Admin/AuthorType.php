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
                'label' => 'Linked employee',
                'placeholder' => 'No linked employee',
                'choices' => $this->getEmployeeChoices(),
            ])
            ->add('nickhandle', TextType::class, [
                'required' => false,
                'label' => 'Nickname',
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
            ->add('count', IntegerType::class, [
                'required' => false,
                'label' => 'Counter',
                'disabled' => true,
            ])
            ->add('allowed_groups', ChoiceType::class, [
                'required' => false,
                'label' => 'Allowed groups',
                'choices' => $this->getGroupChoices(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('author_products', ProductAutocompleteType::class, [
                'required' => false,
                'label' => 'Linked products',
                'help' => 'Search by product name, reference or ID to link products without loading the whole catalogue.',
            ])
            ->add('author_image_file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Author image',
                'help' => $options['author_image_help'],
                'help_html' => true,
            ])
            ->add('delete_author_image', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'disabled' => !$options['has_author_image'],
                'label' => 'Delete current author image',
                'help' => $options['has_author_image']
                    ? 'Check this box and save to delete the current image.'
                    : 'No author image is associated with this author yet.',
            ])
            ->add('banner_image_file', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Banner image',
                'help' => $options['banner_image_help'],
                'help_html' => true,
            ])
            ->add('delete_banner_image', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'disabled' => !$options['has_banner_image'],
                'label' => 'Delete current banner image',
                'help' => $options['has_banner_image']
                    ? 'Check this box and save to delete the current banner image.'
                    : 'No banner image is associated with this author yet.',
            ])
        ;

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (language #%d)', $idLang);

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
                            'message' => 'The slug must contain only letters, numbers and hyphens.',
                        ]),
                    ],
                ])
                ->add(sprintf('excerpt_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Excerpt' . $suffix,
                    'constraints' => [new Length(['max' => 255])],
                ])
                ->add(sprintf('content_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Content' . $suffix,
                ])
                ->add(sprintf('bottom_content_%d', $idLang), TextareaType::class, [
                    'required' => false,
                    'label' => 'Bottom content' . $suffix,
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
            $choices[$label ?: sprintf('Employee #%d', $id)] = $id;
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
            $choices[$label ?: sprintf('Group #%d', $id)] = $id;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'author_image_help' => '',
            'has_author_image' => false,
            'banner_image_help' => '',
            'has_banner_image' => false,
            'translation_domain' => 'Modules.Everpsblog.Admin',
        ]);
        $resolver->setAllowedTypes('author_image_help', 'string');
        $resolver->setAllowedTypes('has_author_image', 'bool');
        $resolver->setAllowedTypes('banner_image_help', 'string');
        $resolver->setAllowedTypes('has_banner_image', 'bool');
    }
}
