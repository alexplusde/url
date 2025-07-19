<?php

$addon = rex_addon::get('url');
$currentVersion = $addon->getVersion();
$apiUrl = $addon->getProperty('release_url');

try {
    $socket = rex_socket::factoryUrl($apiUrl)
        ->addHeader('User-Agent', 'REDAXO-Release-Checker')
        ->acceptCompression();

    $response = $socket->doGet();

    if ($response->isOk()) {
        $data = json_decode($response->getBody(), true);
        $found = false;
        $sections = '';
        foreach ($data as $release) {
            $tag = ltrim($release['tag_name'] ?? '', 'v');
            if (!$tag || version_compare($tag, $currentVersion, '<=')) {
                continue;
            }
            $found = true;
            $isPreview = !empty($release['prerelease']);
            $releaseUrl = $release['html_url'] ?? null;
            $zipUrl = $release['zipball_url'] ?? null;
            $publishedAt = isset($release['published_at']) ? date('d.m.Y H:i', strtotime($release['published_at'])) : '';
            $body = $release['body'] ? rex_markdown::factory()->parse($release['body']) : '';
            $title = htmlspecialchars($release['name'] ?: $tag);
            $previewLabel = $isPreview ? ' <span class="label label-warning">Preview</span>' : '';
            $releaseLink = $releaseUrl ? '<a href="' . htmlspecialchars($releaseUrl) . '" target="_blank">' . $title . '</a>' : $title;
            $installBtn = $zipUrl ? '<a href="' . htmlspecialchars($zipUrl) . '" class="btn btn-primary" target="_blank"><i class="fa fa-download"></i> ZIP herunterladen</a>' : '';
            $alert = $isPreview ? rex_view::info('Dies ist eine Preview-Version.') : rex_view::success('Stable Release.');
            $fragment = new rex_fragment();
            $fragment->setVar('class', $isPreview ? 'info' : 'success', false);
            $fragment->setVar('title', 'Neue Version: ' . $releaseLink . $previewLabel, false);
            $fragment->setVar(
                'body',
                $alert .
                '<p><strong>Veröffentlicht am:</strong> ' . $publishedAt . '</p>' .
                ($body ? '<div class="release-notes markdown-body">' . $body . '</div>' : '') .
                '<div class="mt-3">' . $installBtn . '</div>',
                false
            );
            $sections .= $fragment->parse('core/page/section.php');
        }
        if (!$found) {
            $sections = rex_view::info('Keine neueren Versionen gefunden. Aktuell: ' . htmlspecialchars($currentVersion));
        }
        echo $sections;
    } else {
        echo rex_view::error('Fehler beim Abrufen der Release-Informationen: ' . $response->getStatusCode());
    }
} catch (Exception $e) {
    echo rex_view::error('Fehler: ' . htmlspecialchars($e->getMessage()));
}
