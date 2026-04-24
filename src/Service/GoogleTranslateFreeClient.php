<?php

declare(strict_types=1);

namespace PrestaShop\Module\Everpsblog\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class GoogleTranslateFreeClient
{
    private const ENDPOINT = 'https://translate.googleapis.com/translate_a/single';
    private const USER_AGENT = 'EverPsBlog/6.0 (+https://www.team-ever.com/)';
    private const TIMEOUT = 15;

    /**
     * @param string[] $texts
     *
     * @return array<string, string>
     */
    public function translateTexts(array $texts, string $sourceLanguage, string $targetLanguage): array
    {
        $translations = [];
        $queue = [];

        foreach ($texts as $text) {
            $text = (string) $text;
            if ('' === $text) {
                continue;
            }

            if ($sourceLanguage === $targetLanguage) {
                $translations[$text] = $text;
                continue;
            }

            $queue[$text] = $text;
        }

        foreach ($queue as $text) {
            $translations[$text] = $this->translateOne($text, $sourceLanguage, $targetLanguage);
        }

        return $translations;
    }

    private function translateOne(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        [$preparedText, $placeholders] = $this->protectPlaceholders($text);
        $payload = $this->requestTranslation($preparedText, $sourceLanguage, $targetLanguage);
        $translated = $this->parseTranslatedText($payload);

        return $this->restorePlaceholders($translated, $placeholders);
    }

    private function requestTranslation(string $text, string $sourceLanguage, string $targetLanguage): string
    {
        $query = http_build_query([
            'client' => 'gtx',
            'sl' => $sourceLanguage,
            'tl' => $targetLanguage,
            'dt' => 't',
            'q' => $text,
        ], '', '&', PHP_QUERY_RFC3986);

        if (function_exists('curl_init')) {
            $handle = curl_init(self::ENDPOINT . '?' . $query);
            if (false === $handle) {
                throw new \RuntimeException('Unable to initialize cURL for Google Translate.');
            }

            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'User-Agent: ' . self::USER_AGENT,
                ],
            ]);

            $response = curl_exec($handle);
            $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $error = curl_error($handle);
            curl_close($handle);

            if (false === $response || $httpCode < 200 || $httpCode >= 300) {
                throw new \RuntimeException(
                    sprintf(
                        'Google Translate request failed (HTTP %d%s).',
                        $httpCode,
                        '' !== $error ? ', ' . $error : ''
                    )
                );
            }

            return (string) $response;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => self::TIMEOUT,
                'header' => implode("\r\n", [
                    'Accept: application/json',
                    'User-Agent: ' . self::USER_AGENT,
                ]),
            ],
        ]);

        $response = @file_get_contents(self::ENDPOINT . '?' . $query, false, $context);
        if (false === $response) {
            throw new \RuntimeException('Google Translate request failed.');
        }

        return (string) $response;
    }

    private function parseTranslatedText(string $payload): string
    {
        $decoded = json_decode($payload, true);
        if (!is_array($decoded) || !isset($decoded[0]) || !is_array($decoded[0])) {
            throw new \RuntimeException('Unexpected Google Translate response payload.');
        }

        $translated = '';
        foreach ($decoded[0] as $chunk) {
            if (!is_array($chunk) || !isset($chunk[0])) {
                continue;
            }

            $translated .= (string) $chunk[0];
        }

        if ('' === $translated) {
            throw new \RuntimeException('Google Translate returned an empty translation.');
        }

        return $translated;
    }

    /**
     * @return array{0:string,1:array<string,string>}
     */
    private function protectPlaceholders(string $text): array
    {
        $placeholders = [];
        $prepared = $text;
        $patterns = [
            '/%[A-Za-z0-9_]+%/',
            '/\{\{[^{}]+\}\}/',
            '/\{\$[^{}]+\}/',
        ];

        foreach ($patterns as $pattern) {
            $prepared = preg_replace_callback($pattern, function (array $matches) use (&$placeholders) {
                $token = 'QZEVERTOKEN' . count($placeholders) . 'QZ';
                $placeholders[$token] = (string) $matches[0];

                return $token;
            }, $prepared) ?: $prepared;
        }

        return [$prepared, $placeholders];
    }

    /**
     * @param array<string,string> $placeholders
     */
    private function restorePlaceholders(string $text, array $placeholders): string
    {
        if (empty($placeholders)) {
            return $text;
        }

        return strtr($text, $placeholders);
    }
}
