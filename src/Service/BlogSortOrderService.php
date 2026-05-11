<?php

declare(strict_types=1);


namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}


class BlogSortOrderService
{
    private function transShop(string $message): string
    {
        return \Context::getContext()->getTranslator()->trans($message, [], 'Modules.Everpsblog.Shop');
    }

    public function getSortOrders()
    {
        $sortOrders = [
            ['label' => $this->transShop('Most viewed'), 'order_by' => 'count', 'order_way' => 'desc'],
            ['label' => $this->transShop('Most recent'), 'order_by' => 'date_add', 'order_way' => 'desc'],
            ['label' => $this->transShop('The oldest'), 'order_by' => 'date_add', 'order_way' => 'asc'],
        ];

        foreach ($sortOrders as &$sortOrder) {
            $orderBy = $sortOrder['order_by'];
            $orderWay = $sortOrder['order_way'];
            $sortOrder['current'] = $orderBy === \Tools::getValue('orderby') && $orderWay === \Tools::getValue('orderway');
            $sortOrder['url'] = $this->buildSortLink($orderBy, $orderWay);
        }

        return $sortOrders;
    }

    public function buildSortLink($orderBy, $orderWay)
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        $queryParams['orderby'] = $orderBy;
        $queryParams['orderway'] = $orderWay;
        $newQuery = http_build_query($queryParams);

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host']
            . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '')
            . $parsedUrl['path'] . '?' . $newQuery
            . (isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '');
    }
}
