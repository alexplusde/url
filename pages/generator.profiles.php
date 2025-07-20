<?php

/**
 * This file is part of the Url package.
 *
 * @author (c) Thomas Blum <thomas@addoff.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Url\Cache;
use Url\Database;
use Url\Profile;
use Url\Url;
use Url\UrlManager;
use Url\UrlManagerSql;

/** @var rex_addon $this */

$id = rex_request('id', 'int');
$func = rex_request('func', 'string');
$action = rex_request('action', 'string');
$message = '';

if ($action === 'cache') {
    Cache::deleteProfiles();
}

$a = [];

if ($func === 'delete' && $id > 0) {
    if (!rex_csrf_token::factory('url_profile_delete')->isValid()) {
        $message = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        $profile = Profile::get($id);
        if ($profile !== null) {
            $profile->deleteUrls();

            $sql = rex_sql::factory()
                ->setTable(rex::getTable(Profile::TABLE_NAME))
                ->setWhere('id = :id', ['id' => $id]);
            $sql->delete();
            $message .= rex_view::success(rex_i18n::msg('url_generator_profile_removed'));
            Cache::deleteProfiles();
        }
    }
    $func = '';
}

if (($func === 'refresh' && $id > 0) || $func === 'refresh_all') {
    if (!rex_csrf_token::factory('url_profile_refresh')->isValid()) {
        $message = rex_view::error(rex_i18n::msg('csrf_token_invalid'));
    } else {
        switch ($func) {
            case 'refresh':
                $profile = Profile::get($id);
                if ($profile !== null) {
                    $profile->deleteUrls();
                    $profile->buildUrls();
                    $message .= rex_view::success(rex_i18n::msg('url_generator_url_refreshed', $id));
                }
                break;
            case 'refresh_all':
                UrlManagerSql::deleteAll();
                $profiles = Profile::getAll();
                if (count($profiles) > 0) {
                    foreach ($profiles as $profile) {
                        $profile->buildUrls();
                        $message .= rex_view::success(rex_i18n::msg('url_generator_url_refreshed', $profile->getId()));
                    }
                }
                break;
        }
    }
    $func = '';
}

if ($message !== '') {
    echo $message;
}
if ($func === '') {

    $fragment = new rex_fragment();
    echo $fragment->parse('url/profiles/cards.php');

} elseif ($func === 'add' || $func === 'edit') {
        $fragment = new rex_fragment();
        $fragment->setVar('func', $func, false);
        $fragment->setVar('id', $id, false);
        $fragment->setVar('addon', $this, false);
        echo $fragment->parse('url/profiles/form.php');
}

if ($func === 'add' || $func === 'edit') {
    for ($i = 1; $i <= Profile::RELATION_COUNT; ++$i) {
        ?>
    <script type="text/javascript">
        (function($) {
            var $currentShownRelationSection = $(".js-change-relation-<?= $i ?>-container");
            $currentShownRelationSection.hide();
            $(".js-change-relation-<?= $i ?>-select select").change(function(){
                if ($(this).closest(".rex-form-container").is(":visible")) {
                    if ($(this).val().length > 0) {
                        $currentShownRelationSection.show();
                    } else {
                        $currentShownRelationSection.hide();
                    }
                }
            }).change();
        })(jQuery);
    </script>
<?php
    }
}
?>
