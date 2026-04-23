<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Form\Type\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Everpsblog\Service\ProductAutocompleteProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class ProductAutocompleteType extends AbstractType
{
    /** @var ProductAutocompleteProvider */
    private $productAutocompleteProvider;

    /** @var RouterInterface */
    private $router;

    public function __construct(ProductAutocompleteProvider $productAutocompleteProvider, RouterInterface $router)
    {
        $this->productAutocompleteProvider = $productAutocompleteProvider;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                $ids = $this->normalizeIds($value);

                return implode(',', $ids);
            },
            function ($value) {
                return $this->normalizeIds($value);
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $context = \Context::getContext();
        $shopId = isset($context->shop) ? (int) $context->shop->id : 0;
        $langId = isset($context->language) ? (int) $context->language->id : 0;
        $selectedIds = $this->normalizeIds($form->getData());

        $view->vars['product_search_url'] = $options['product_search_url'] ?: $this->router->generate('everpsblog_admin_product_autocomplete');
        $view->vars['selected_products'] = $shopId > 0 && $langId > 0
            ? $this->productAutocompleteProvider->getSelectedProducts($selectedIds, $shopId, $langId)
            : [];
        $view->vars['autocomplete_placeholder'] = $options['autocomplete_placeholder'];
        $view->vars['autocomplete_loading_text'] = $options['autocomplete_loading_text'];
        $view->vars['autocomplete_empty_text'] = $options['autocomplete_empty_text'];
        $view->vars['autocomplete_min_length_text'] = $options['autocomplete_min_length_text'];
        $view->vars['autocomplete_min_length'] = $options['autocomplete_min_length'];
        $view->vars['autocomplete_remove_text'] = $options['autocomplete_remove_text'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'product_search_url' => null,
            'autocomplete_placeholder' => 'Search by product name, reference or ID',
            'autocomplete_loading_text' => 'Searching...',
            'autocomplete_empty_text' => 'No product found.',
            'autocomplete_min_length_text' => 'Type at least %count% characters.',
            'autocomplete_min_length' => 2,
            'autocomplete_remove_text' => 'Remove',
            'invalid_message' => 'Invalid product selection.',
        ]);
        $resolver->setAllowedTypes('product_search_url', ['null', 'string']);
        $resolver->setAllowedTypes('autocomplete_placeholder', 'string');
        $resolver->setAllowedTypes('autocomplete_loading_text', 'string');
        $resolver->setAllowedTypes('autocomplete_empty_text', 'string');
        $resolver->setAllowedTypes('autocomplete_min_length_text', 'string');
        $resolver->setAllowedTypes('autocomplete_min_length', 'int');
        $resolver->setAllowedTypes('autocomplete_remove_text', 'string');
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ever_product_autocomplete';
    }

    /**
     * @param mixed $value
     *
     * @return int[]
     */
    private function normalizeIds($value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (is_array($value)) {
            return $this->normalizeIdList($value);
        }

        $decoded = json_decode((string) $value, true);
        if (is_array($decoded)) {
            return $this->normalizeIdList($decoded);
        }

        return $this->normalizeIdList(explode(',', (string) $value));
    }

    /**
     * @param array<int|string, mixed> $values
     *
     * @return int[]
     */
    private function normalizeIdList(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            $id = (int) $value;
            if ($id <= 0) {
                continue;
            }

            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }
}
