<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class ProductAutocompleteProvider
{
    /**
     * @param int[] $ids
     *
     * @return array<int, array<string, int|string>>
     */
    public function getSelectedProducts(array $ids, int $shopId, int $langId): array
    {
        $ids = $this->normalizeIds($ids);
        if ([] === $ids) {
            return [];
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT p.id_product, p.reference, pl.name
            FROM `' . _DB_PREFIX_ . 'product` p
            INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int) $shopId . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $langId . ' AND pl.id_shop = ' . (int) $shopId . ')
            WHERE p.id_product IN (' . implode(',', $ids) . ')'
        ) ?: [];

        $productsById = [];
        foreach ($rows as $row) {
            $product = $this->buildProductPayload($row);
            if (null === $product) {
                continue;
            }

            $productsById[(int) $product['id']] = $product;
        }

        $selectedProducts = [];
        foreach ($ids as $id) {
            if (isset($productsById[$id])) {
                $selectedProducts[] = $productsById[$id];
            }
        }

        return $selectedProducts;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function searchProducts(string $query, int $shopId, int $langId, int $limit = 20): array
    {
        $query = trim($query);
        if ('' === $query) {
            return [];
        }

        $limit = max(1, min(50, (int) $limit));
        $escapedQuery = pSQL($query);
        $escapedLike = $this->escapeLike($query);
        $escapedPrefixLike = $this->escapeLike($query) . '%';
        $conditions = [
            'pl.name LIKE "%' . $escapedLike . '%"',
            'p.reference LIKE "%' . $escapedLike . '%"',
        ];
        $orderBy = [
            'CASE WHEN p.reference = "' . $escapedQuery . '" THEN 0 ELSE 1 END',
            'CASE WHEN pl.name LIKE "' . $escapedPrefixLike . '" THEN 0 ELSE 1 END',
            'pl.name ASC',
            'p.id_product DESC',
        ];

        if ($this->isExactInteger($query)) {
            $productId = (int) $query;
            if ($productId > 0) {
                $conditions[] = 'p.id_product = ' . $productId;
                array_unshift($orderBy, 'CASE WHEN p.id_product = ' . $productId . ' THEN 0 ELSE 1 END');
            }
        }

        $rows = \Db::getInstance()->executeS(
            'SELECT DISTINCT p.id_product, p.reference, pl.name
            FROM `' . _DB_PREFIX_ . 'product` p
            INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int) $shopId . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $langId . ' AND pl.id_shop = ' . (int) $shopId . ')
            WHERE (' . implode(' OR ', $conditions) . ')
            ORDER BY ' . implode(', ', $orderBy) . '
            LIMIT ' . $limit
        ) ?: [];

        $products = [];
        foreach ($rows as $row) {
            $product = $this->buildProductPayload($row);
            if (null === $product) {
                continue;
            }

            $products[] = $product;
        }

        return $products;
    }

    private function escapeLike(string $value): string
    {
        return pSQL(strtr($value, [
            '\\' => '\\\\',
            '%' => '\%',
            '_' => '\_',
        ]));
    }

    private function isExactInteger(string $value): bool
    {
        return '' !== $value && ctype_digit($value);
    }

    /**
     * @param int[] $ids
     *
     * @return int[]
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];
        foreach ($ids as $id) {
            $productId = (int) $id;
            if ($productId <= 0) {
                continue;
            }

            $normalized[] = $productId;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, int|string>|null
     */
    private function buildProductPayload(array $row): ?array
    {
        $productId = (int) ($row['id_product'] ?? 0);
        if ($productId <= 0) {
            return null;
        }

        $name = trim((string) ($row['name'] ?? ''));
        $reference = trim((string) ($row['reference'] ?? ''));
        if ('' === $name) {
            $name = sprintf('Product #%d', $productId);
        }

        $details = [];
        if ('' !== $reference) {
            $details[] = $reference;
        }
        $details[] = '#' . $productId;

        return [
            'id' => $productId,
            'name' => $name,
            'reference' => $reference,
            'label' => sprintf('%s (%s)', $name, implode(' - ', $details)),
        ];
    }
}
