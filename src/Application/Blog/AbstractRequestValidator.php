<?php

namespace PrestaShop\Module\Everpsblog\Application\Blog;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Everpsblog\Adapter\LegacyLanguageAdapter;
use Throwable;
use Tools;

abstract class AbstractRequestValidator
{
    private const META_TITLE_MAX_LENGTH = 70;
    private const META_DESCRIPTION_MAX_LENGTH = 160;
    private const SLUG_MAX_LENGTH = 128;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var LegacyLanguageAdapter */
    protected $languageAdapter;

    /** @var array<string, string[]> */
    private $fieldErrors = [];

    /** @var string[] */
    private $globalErrors = [];

    public function __construct(EntityManagerInterface $entityManager, LegacyLanguageAdapter $languageAdapter)
    {
        $this->entityManager = $entityManager;
        $this->languageAdapter = $languageAdapter;
    }

    protected function resetErrors(): void
    {
        $this->fieldErrors = [];
        $this->globalErrors = [];
    }

    protected function addFieldError(string $field, string $message): void
    {
        if (!isset($this->fieldErrors[$field])) {
            $this->fieldErrors[$field] = [];
        }

        $this->fieldErrors[$field][] = $message;
    }

    protected function addGlobalError(string $message): void
    {
        $this->globalErrors[] = $message;
    }

    protected function throwIfInvalid(): void
    {
        if (!empty($this->fieldErrors) || !empty($this->globalErrors)) {
            throw new RequestValidationException($this->fieldErrors, $this->globalErrors);
        }
    }

    /**
     * @return int[]
     */
    protected function getLanguageIds(): array
    {
        return array_map(static function (array $language): int {
            return (int) ($language['id_lang'] ?? 0);
        }, $this->languageAdapter->getLanguages(false));
    }

    protected function getDefaultLanguageId(): int
    {
        return $this->languageAdapter->getDefaultLanguageId();
    }

    protected function ensureDefaultTitle(array $requestData, string $fieldPrefix = 'title_'): void
    {
        $defaultLangField = $fieldPrefix . $this->getDefaultLanguageId();
        $title = trim((string) ($requestData[$defaultLangField] ?? ''));
        if ('' === $title) {
            $this->addFieldError($defaultLangField, 'Ce champ est obligatoire (langue par défaut).');
        }
    }

    protected function normalizeSeoFields(array $requestData, string $titleFallbackPrefix = 'title_'): array
    {
        foreach ($this->getLanguageIds() as $langId) {
            $metaTitleField = 'meta_title_' . $langId;
            $metaDescriptionField = 'meta_description_' . $langId;
            $slugField = 'link_rewrite_' . $langId;

            $metaTitle = trim((string) ($requestData[$metaTitleField] ?? ''));
            $metaDescription = trim((string) ($requestData[$metaDescriptionField] ?? ''));
            $slug = trim((string) ($requestData[$slugField] ?? ''));
            $title = trim((string) ($requestData[$titleFallbackPrefix . $langId] ?? ''));

            if (Tools::strlen($metaTitle) > self::META_TITLE_MAX_LENGTH) {
                $this->addFieldError($metaTitleField, sprintf('Maximum %d caractères.', self::META_TITLE_MAX_LENGTH));
            }

            if (Tools::strlen($metaDescription) > self::META_DESCRIPTION_MAX_LENGTH) {
                $this->addFieldError($metaDescriptionField, sprintf('Maximum %d caractères.', self::META_DESCRIPTION_MAX_LENGTH));
            }

            $normalizedSlug = Tools::str2url($slug ?: ($title ?: $metaTitle));
            if ('' !== $normalizedSlug && Tools::strlen($normalizedSlug) > self::SLUG_MAX_LENGTH) {
                $this->addFieldError($slugField, sprintf('Slug trop long (maximum %d caractères).', self::SLUG_MAX_LENGTH));
                continue;
            }

            $requestData[$slugField] = $normalizedSlug;
        }

        return $requestData;
    }

    protected function normalizePostStatusAndDate(array $requestData): array
    {
        $statusField = 'post_status';
        $dateField = 'date_add';

        $status = trim((string) ($requestData[$statusField] ?? 'draft'));
        if ('' === $status) {
            $status = 'draft';
        }
        if (!in_array($status, ['draft', 'published', 'trash', 'planned', 'protected'], true)) {
            $status = 'draft';
        }

        $dateValue = (string) ($requestData[$dateField] ?? '');
        if ('' === trim($dateValue)) {
            $requestData[$statusField] = $status;

            return $requestData;
        }

        $publicationDate = $this->parseDate($dateValue);
        if (null === $publicationDate) {
            $this->addFieldError($dateField, 'Format de date invalide.');

            return $requestData;
        }

        $now = new DateTimeImmutable('now');
        $requestData[$dateField] = $publicationDate->format('Y-m-d H:i:s');
        if ($publicationDate > $now && 'published' === $status) {
            $requestData[$statusField] = 'planned';

            return $requestData;
        }
        if ($publicationDate <= $now && 'planned' === $status) {
            $requestData[$statusField] = 'published';

            return $requestData;
        }

        if ($publicationDate > $now && 'published' === $status) {
            $requestData[$statusField] = 'planned';
            $this->addGlobalError('Le statut a été ajusté à "planned" car la date de publication est dans le futur.');
        } elseif ($publicationDate <= $now && 'planned' === $status) {
            $requestData[$statusField] = 'published';
            $this->addGlobalError('Le statut a été ajusté à "published" car la date de publication est passée.');
        } else {
            $requestData[$statusField] = $status;
        }

        return $requestData;
    }

    protected function existsInModuleTable(string $table, string $idColumn, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();
        $sql = sprintf('SELECT 1 FROM `%s%s` WHERE `%s` = :id LIMIT 1', _DB_PREFIX_, $table, $idColumn);

        return false !== $connection->fetchOne($sql, ['id' => $id]);
    }

    protected function existsInPrestashopTable(string $table, string $idColumn, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();
        $sql = sprintf('SELECT 1 FROM %s%s WHERE %s = :id LIMIT 1', _DB_PREFIX_, $table, $idColumn);

        return false !== $connection->fetchOne($sql, ['id' => $id]);
    }

    /**
     * @param mixed $value
     *
     * @return int[]
     */
    protected function normalizeIntCollection($value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif (null === $value || '' === $value) {
            $values = [];
        } else {
            $values = [$value];
        }

        $normalized = array_values(array_filter(array_map(static function ($item): int {
            return (int) $item;
        }, $values), static function (int $item): bool {
            return $item > 0;
        }));

        return array_values(array_unique($normalized));
    }

    private function parseDate(string $date): ?DateTimeImmutable
    {
        $date = trim(str_replace('T', ' ', $date));
        foreach (['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d', 'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y'] as $format) {
            $parsed = DateTimeImmutable::createFromFormat('!' . $format, $date);
            $errors = DateTimeImmutable::getLastErrors();
            if (false !== $parsed && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))) {
                return $parsed;
            }
        }

        try {
            return new DateTimeImmutable($date);
        } catch (Throwable $exception) {
            return null;
        }
    }
}
