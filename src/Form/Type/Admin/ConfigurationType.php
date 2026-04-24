<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
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
                'label' => 'Blog route',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 64]),
                ],
            ])
            ->add('allow_comments', CheckboxType::class, [
                'label' => 'Allow comments',
                'required' => false,
            ])
            ->add('check_comments', CheckboxType::class, [
                'label' => 'Moderate comments',
                'required' => false,
            ])
            ->add('rss_enabled', CheckboxType::class, [
                'label' => 'Enable RSS feeds',
                'required' => false,
                'help' => 'Adds RSS feed links on blog, category, tag and author pages.',
            ])
            ->add('posts_per_page', IntegerType::class, [
                'label' => 'Posts per page',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('home_posts', IntegerType::class, [
                'label' => 'Posts on homepage',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('product_posts', IntegerType::class, [
                'label' => 'Posts on product page',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('excerpt_length', IntegerType::class, [
                'label' => 'Excerpt length',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('title_length', IntegerType::class, [
                'label' => 'Title length',
                'constraints' => [new GreaterThan(['value' => 0])],
            ])
            ->add('empty_trash_days', IntegerType::class, [
                'label' => 'Empty trash after (days)',
                'help' => 'Posts kept in trash longer than this delay are deleted automatically.',
                'constraints' => [new GreaterThanOrEqual(['value' => 0])],
            ])
            ->add('default_author_id', ChoiceType::class, [
                'label' => 'Default author for orphan posts',
                'required' => false,
                'placeholder' => 'No default author',
                'choices' => (array) ($options['author_choices'] ?? []),
            ])
            ->add('header_bg_color', TextType::class, [
                'label' => 'Blog header color',
                'required' => false,
                'help' => 'Color applied to the main banner on blog, post, category, tag, author and search pages.',
                'attr' => [
                    'type' => 'color',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^#[0-9a-fA-F]{6}$/',
                        'message' => 'The color must use hexadecimal format, for example #0a0f54.',
                    ]),
                ],
            ])
            ->add('header_title_color', TextType::class, [
                'label' => 'Blog header title color',
                'required' => false,
                'help' => 'Color applied to the titles displayed inside blog, post, category, tag, author and search headers.',
                'attr' => [
                    'type' => 'color',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^#[0-9a-fA-F]{6}$/',
                        'message' => 'The color must use hexadecimal format, for example #ffffff.',
                    ]),
                ],
            ])
            ->add('wordpress_api_url', TextType::class, [
                'label' => 'WordPress site URL',
                'required' => false,
                'help' => 'Example: https://example.com or https://example.com/wp-json/wp/v2',
                'constraints' => [
                    new Length(['max' => 255]),
                    new Url(),
                ],
            ])
            ->add('wordpress_api_user', TextType::class, [
                'label' => 'WordPress REST username',
                'required' => false,
                'help' => 'Optional for public post imports. Required for non-public content.',
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ])
            ->add('wordpress_api_password', PasswordType::class, [
                'label' => 'WordPress application password',
                'required' => false,
                'always_empty' => false,
                'help' => 'Use a WordPress application password, not the main account password.',
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ])
            ->add('wordpress_import_post_status', ChoiceType::class, [
                'label' => 'Imported post status',
                'choices' => [
                    'Published' => 'published',
                    'Draft' => 'draft',
                ],
            ])
            ->add('wordpress_enable_authors', CheckboxType::class, [
                'label' => 'Enable imported authors',
                'required' => false,
            ])
            ->add('wordpress_enable_categories', CheckboxType::class, [
                'label' => 'Enable imported categories',
                'required' => false,
            ])
            ->add('wordpress_enable_tags', CheckboxType::class, [
                'label' => 'Enable imported tags',
                'required' => false,
            ]);

        foreach (\Language::getLanguages(false) as $lang) {
            $idLang = (int) $lang['id_lang'];
            $isoCode = strtoupper((string) ($lang['iso_code'] ?? ''));
            $suffix = $isoCode ? sprintf(' (%s)', $isoCode) : sprintf(' (language #%d)', $idLang);

            $builder
                ->add(sprintf('top_text_%d', $idLang), TextareaType::class, [
                    'label' => 'Blog top text' . $suffix,
                    'required' => false,
                    'attr' => [
                        'data-ever-richtext' => '1',
                        'rows' => 8,
                    ],
                ])
                ->add(sprintf('bottom_text_%d', $idLang), TextareaType::class, [
                    'label' => 'Blog bottom text' . $suffix,
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
            'translation_domain' => 'Modules.Everpsblog.Admin',
            'author_choices' => [],
        ]);
    }
}
