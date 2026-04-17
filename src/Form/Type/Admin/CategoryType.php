<?php

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
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
}
