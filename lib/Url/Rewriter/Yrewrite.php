<?php

/**
 * This file is part of the Url package.
 *
 * @author (c) Thomas Blum <thomas@addoff.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Url\Rewriter;

use Url\UrlManager;

class Yrewrite extends Rewriter
{
    /**
     * @return string
     */
    public function articleIdNotFound(): string
    {
        \rex_extension::register('YREWRITE_PREPARE', function (\rex_extension_point $ep) {
            return UrlManager::getArticleParams();
        }, \rex_extension::EARLY);
        return '';
    }

    public function getDomainByArticleId($articleId, ?int $clangId = null)
    {
        return \rex_yrewrite::getDomainByArticleId($articleId, $clangId);
    }

    public function getCurrentDomain()
    {
        return \rex_yrewrite::getCurrentDomain();
    }

    public function usesSitemapDomains(): bool
    {
        return true;
    }

    /**
     * Domain der Sitemap-Anfrage wie in rex_yrewrite_seo::sendSitemap(): aus dem Host, bei einer einzigen Domain die
     * Standarddomain. rex_yrewrite::getCurrentDomain() taugt dafür nicht, weil es die Domain über den aktuellen Artikel
     * bestimmt – bei sitemap.xml ist das der 404- oder Startartikel, der bei gemeinsam genutzten Artikeln zu einer
     * anderen Domain gehören kann (Sitemap von Domain A listete dann die Profile von Domain B, alexplusde/url#50).
     * Kein Fallback auf den aktuellen Artikel: Bei unbekanntem Host lässt YRewrite die Sitemap leer, aber ruft den
     * Extension Point trotzdem auf – dann gehören auch keine Profil-URLs hinein.
     *
     * @return \rex_yrewrite_domain|null
     */
    public function getSitemapDomain(): ?object
    {
        if (1 === count(\rex_yrewrite::getDomains())) {
            return \rex_yrewrite::getDefaultDomain();
        }

        return \rex_yrewrite::getDomainByName(\rex_yrewrite::getHost());
    }

    /**
     * @return string
     */
    public function getSitemapExtensionPoint(): string
    {
        return 'YREWRITE_SITEMAP';
    }

    /**
     * @return \rex_yrewrite_seo
     */
    public function getSeoInstance(): \rex_yrewrite_seo
    {
        return new \rex_yrewrite_seo();
    }

    /**
     * @return string
     */
    public function getSeoTags(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSeoTagsExtensionPoint(): string
    {
        return 'YREWRITE_SEO_TAGS';
    }

    /**
     * @param int $article_id
     * @param int $clang_id
     *
     * @return string
     */
    public function getFullUrl($article_id, $clang_id): string
    {
        return \rex_yrewrite::getFullUrlByArticleId($article_id, $clang_id);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getFullPath($path): string
    {
        return \rex_yrewrite::getFullPath($path);
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        $scheme = \rex_yrewrite::getScheme();
        return $scheme->getSuffix();
    }

    /**
     * @param string $domain
     *
     * @return string
     */
    public function getSchemeByDomain($domain): string
    {
        $domain = \rex_yrewrite::getDomainByName($domain);
        if (!$domain) {
            return '';
        }
        return (string) $domain->getScheme();
    }

    /**
     * @return bool
     */
    public function isHttps(): bool
    {
        return \rex_yrewrite::isHttps();
    }

    /**
     * @param string $string
     * @param int    $clang
     *
     * @return string
     */
    public function normalize($string, $clang = 1): string
    {
        $scheme = \rex_yrewrite::getScheme();
        return $scheme->normalize($string, $clang);
    }
}
