<?php

namespace PrestaShop\Module\Everpsblog\Grid\Data;

trait GridRecordFilterTrait
{
    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<string, mixed> $filters
     * @param array<int, string> $searchFields
     *
     * @return array<int, array<string, mixed>>
     */
    private function filterRecords(array $records, array $filters, array $searchFields): array
    {
        $globalSearch = trim((string) ($filters['q'] ?? ''));
        unset($filters['q']);

        $filtered = array_filter($records, function (array $record) use ($filters, $searchFields, $globalSearch) {
            foreach ($filters as $field => $expected) {
                $expected = trim((string) $expected);
                if ('' === $expected || '_' === substr((string) $field, 0, 1)) {
                    continue;
                }

                if (!$this->recordValueContains($record[$field] ?? '', $expected)) {
                    return false;
                }
            }

            if ('' === $globalSearch) {
                return true;
            }

            foreach ($searchFields as $field) {
                if ($this->recordValueContains($record[$field] ?? '', $globalSearch)) {
                    return true;
                }
            }

            return false;
        });

        return array_values($filtered);
    }

    private function recordValueContains($value, string $needle): bool
    {
        if (is_array($value) || is_object($value)) {
            return false;
        }

        return false !== stripos((string) $value, $needle);
    }
}
