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

use Url\Rewriter\Yrewrite;

class Generator
{
    protected ExtensionPointManager $manager;

    public function __construct(ExtensionPointManager $manager)
    {
        $this->manager = $manager;
    }

    public function execute(): void
    {
        switch ($this->manager->getMode()) {
            case ExtensionPointManager::MODE_UPDATE_URL_ALL:
                UrlManagerSql::deleteAll();
                $profiles = Profile::getAll();
                if (count($profiles) > 0) {
                    foreach ($profiles as $profile) {
                        $profile->buildUrls();
                    }
                }
                break;

            case ExtensionPointManager::MODE_UPDATE_URL_COLLECTION:
                $profiles = Profile::getByArticleId($this->manager->getStructureArticleId(), $this->manager->getStructureClangId());
                if (count($profiles) > 0) {
                    foreach ($profiles as $profile) {
                        $profile->deleteUrls();
                        $profile->buildUrls();
                    }
                }
                break;

            case ExtensionPointManager::MODE_UPDATE_URL_DATASET:
                $profiles = Profile::getByTableName($this->manager->getDatasetTableName());
                if (count($profiles) > 0) {
                    foreach ($profiles as $profile) {
                        // Get old URLs before deletion to create redirects
                        $oldUrls = UrlManagerSql::getOriginUrls($profile->getId(), $this->manager->getDatasetPrimaryId());
                        
                        $profile->deleteUrlsByDatasetId($this->manager->getDatasetPrimaryId());
                        $profile->buildUrlsByDatasetId($this->manager->getDatasetPrimaryId());
                        
                        // Get new URLs after building
                        $newUrls = UrlManagerSql::getOriginUrls($profile->getId(), $this->manager->getDatasetPrimaryId());
                        
                        // Create redirects from old to new URLs
                        self::createRedirectsForUrlChanges($oldUrls, $newUrls);
                    }
                }
                break;
        }
    }

    public static function boot(): void
    {
        if (null === Url::getRewriter()) {
            if (\rex_addon::get('yrewrite')->isAvailable()) {
                Url::setRewriter(new Yrewrite());
            } else {
                if (\rex_be_controller::getCurrentPage() === 'packages') {
                    \rex_extension::register('PAGE_TITLE_SHOWN', function (\rex_extension_point $ep) {
                        $ep->setSubject(\rex_view::error('<h4>Url Addon:</h4><p>Please install a rewriter addon or deactivate the Url AddOn.</p>'));
                    });
                }
            }
        }
    }

    /**
     * Creates redirects when URLs change
     *
     * @param array $oldUrls Old URL entries before change
     * @param array $newUrls New URL entries after change
     */
    private static function createRedirectsForUrlChanges(array $oldUrls, array $newUrls): void
    {
        if (empty($oldUrls) || empty($newUrls)) {
            return;
        }

        // Group by clang_id to match old and new URLs properly
        $oldUrlsByClang = [];
        foreach ($oldUrls as $oldUrl) {
            $clangId = $oldUrl['clang_id'];
            if (!isset($oldUrlsByClang[$clangId])) {
                $oldUrlsByClang[$clangId] = [];
            }
            $oldUrlsByClang[$clangId][] = $oldUrl;
        }

        $newUrlsByClang = [];
        foreach ($newUrls as $newUrl) {
            $clangId = $newUrl['clang_id'];
            if (!isset($newUrlsByClang[$clangId])) {
                $newUrlsByClang[$clangId] = [];
            }
            $newUrlsByClang[$clangId][] = $newUrl;
        }

        // Create redirects for each language
        foreach ($oldUrlsByClang as $clangId => $oldClangUrls) {
            if (!isset($newUrlsByClang[$clangId])) {
                continue;
            }

            $newClangUrls = $newUrlsByClang[$clangId];

            // Match origin URLs (not user_path, not structure)
            $oldOriginUrl = null;
            $newOriginUrl = null;

            foreach ($oldClangUrls as $url) {
                if ($url['is_user_path'] == 0 && $url['is_structure'] == 0) {
                    $oldOriginUrl = $url['url'];
                    break;
                }
            }

            foreach ($newClangUrls as $url) {
                if ($url['is_user_path'] == 0 && $url['is_structure'] == 0) {
                    $newOriginUrl = $url['url'];
                    break;
                }
            }

            if ($oldOriginUrl && $newOriginUrl && $oldOriginUrl !== $newOriginUrl) {
                // Get domain ID for the article
                $articleId = $newClangUrls[0]['article_id'] ?? null;
                $domainId = 1; // default
                
                if ($articleId && \rex_addon::get('yrewrite')->isAvailable()) {
                    $domain = \rex_yrewrite::getDomainByArticleId($articleId, $clangId);
                    if ($domain) {
                        $domainId = $domain->getId();
                    }
                }

                RedirectManager::createRedirect($oldOriginUrl, $newOriginUrl, $domainId);
            }
        }
    }
}
