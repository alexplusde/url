<?php

/**
 * This file is part of the Url package.
 *
 * @author (c) Thomas Blum <thomas@addoff.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Url;

class RedirectManager
{
    /**
     * Creates a 301 redirect from old URL to new URL in yrewrite_redirect table
     *
     * @param string $sourceUrl The old URL to redirect from
     * @param string $targetUrl The new URL to redirect to
     * @param int $domainId The yrewrite domain ID (required, no default)
     * @return bool True if redirect was created successfully
     */
    public static function createRedirect(string $sourceUrl, string $targetUrl, int $domainId): bool
    {
        if (!\rex_addon::get('yrewrite')->isAvailable()) {
            return false;
        }

        // Validate URLs are non-empty
        if (empty($sourceUrl) || empty($targetUrl)) {
            return false;
        }

        // Don't create redirect if source and target are the same
        if ($sourceUrl === $targetUrl) {
            return false;
        }

        // Remove any existing redirect that would create a loop
        // If the new target URL was previously a source URL, delete it
        self::deleteRedirectBySource($targetUrl);

        // Check if redirect already exists
        $sql = \rex_sql::factory();
        $existing = $sql->getArray(
            'SELECT id FROM ' . \rex::getTable('yrewrite_redirect') . 
            ' WHERE url_source = ? AND domain_id = ?',
            [$sourceUrl, $domainId]
        );

        if (count($existing) > 0) {
            // Update existing redirect
            $sql->setTable(\rex::getTable('yrewrite_redirect'));
            $sql->setWhere('id = ?', [$existing[0]['id']]);
            $sql->setValue('url_target', $targetUrl);
            $sql->setValue('status', 301);
            $sql->setValue('is_url_addon', 1);
            try {
                $sql->update();
                self::clearYrewriteCache();
                return true;
            } catch (\rex_sql_exception $e) {
                \rex_logger::logException($e);
                return false;
            }
        }

        // Create new redirect
        $sql = \rex_sql::factory();
        $sql->setTable(\rex::getTable('yrewrite_redirect'));
        $sql->setValue('domain_id', $domainId);
        $sql->setValue('url_source', $sourceUrl);
        $sql->setValue('url_target', $targetUrl);
        $sql->setValue('status', 301);
        $sql->setValue('type', 'url');
        $sql->setValue('is_url_addon', 1);

        try {
            $sql->insert();
            self::clearYrewriteCache();
            return true;
        } catch (\rex_sql_exception $e) {
            \rex_logger::logException($e);
            return false;
        }
    }

    /**
     * Deletes a redirect by its source URL to prevent loops
     *
     * @param string $sourceUrl The source URL of the redirect to delete
     * @return bool True if redirect was deleted or didn't exist
     */
    public static function deleteRedirectBySource(string $sourceUrl): bool
    {
        if (!\rex_addon::get('yrewrite')->isAvailable()) {
            return false;
        }

        $sql = \rex_sql::factory();
        $sql->setTable(\rex::getTable('yrewrite_redirect'));
        $sql->setWhere('url_source = ? AND is_url_addon = 1', [$sourceUrl]);

        try {
            $sql->delete();
            self::clearYrewriteCache();
            return true;
        } catch (\rex_sql_exception $e) {
            \rex_logger::logException($e);
            return false;
        }
    }

    /**
     * Clears the YRewrite redirect cache
     */
    private static function clearYrewriteCache(): void
    {
        if (class_exists('\rex_yrewrite_forward')) {
            \rex_yrewrite_forward::clearCache();
        }
    }

    /**
     * Gets all redirects created by the URL addon
     *
     * @return array Array of redirects
     */
    public static function getUrlAddonRedirects(): array
    {
        if (!\rex_addon::get('yrewrite')->isAvailable()) {
            return [];
        }

        $sql = \rex_sql::factory();
        return $sql->getArray(
            'SELECT * FROM ' . \rex::getTable('yrewrite_redirect') . ' WHERE is_url_addon = 1'
        );
    }
}
