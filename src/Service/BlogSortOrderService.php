<?php

namespace PrestaShop\Module\Everpsblog\Service;

class BlogSortOrderService
{
    private function getModule()
    {
        return \Module::getInstanceByName('everpsblog');
    }

    public function getSortOrders()
    {
        $sortOrders = [
            ['label' => $this->getModule()->l('Most viewed'), 'order_by' => 'count', 'order_way' => 'desc'],
            ['label' => $this->getModule()->l('Most recent'), 'order_by' => 'date_add', 'order_way' => 'desc'],
            ['label' => $this->getModule()->l('The oldest'), 'order_by' => 'date_add', 'order_way' => 'asc'],
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
