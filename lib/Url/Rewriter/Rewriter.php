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

abstract class Rewriter
{
    /**
     * @return string
     */
    abstract public function articleIdNotFound(): string;

    /**
     * @return string
     */
    abstract public function getSitemapExtensionPoint(): string;

    /**
     * Kennt der Rewriter Domains, nach denen die Sitemap gefiltert wird? Dann liefert getSitemapDomain() die Domain
     * der Anfrage, und ohne Treffer bleibt die Sitemap ohne Profil-URLs. Die Standardimplementierung kennt keine
     * Domains, alle Profile werden aufgenommen.
     */
    public function usesSitemapDomains(): bool
    {
        return false;
    }

    /**
     * Domain, für die der Rewriter gerade die Sitemap erzeugt (null: nicht zuzuordnen). Yrewrite liest sie aus dem
     * Host der Anfrage.
     */
    public function getSitemapDomain(): ?object
    {
        return null;
    }

    /**
     * @return object
     */
    abstract public function getSeoInstance(): object;

    /**
     * @return string
     */
    abstract public function getSeoTags(): string;

    /**
     * @return string
     */
    abstract public function getSeoTagsExtensionPoint(): string;

    /**
     * @param int $article_id
     * @param int $clang_id
     *
     * @return string
     */
    abstract public function getFullUrl(int $article_id, int $clang_id): string;

    /**
     * @param string $path
     *
     * @return string
     */
    abstract public function getFullPath(string $path): string;

    /**
     * @return string
     */
    abstract public function getSuffix(): string;

    /**
     * @param string $domain
     *
     * @return string
     */
    abstract public function getSchemeByDomain(string $domain): string;

    /**
     * @return bool
     */
    abstract public function isHttps(): bool;

    /**
     * @param string  $string
     * @param integer $clangId
     *
     * @return string
     */
    abstract public function normalize(string $string, int $clangId): string;
}
