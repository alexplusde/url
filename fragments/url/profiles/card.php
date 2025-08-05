<?php

/** @var rex_fragment $this */

$profile = $this->getVar('profile', []);

$tableName = explode('_xxx_', $profile['table_name'] ?? '')[1];
$tableParameters = json_decode($profile['table_parameters'], true);

if (!function_exists('makeLabel')) {
    /**
     * Generates a badge HTML element.
     *
     * @param string $value The text to display inside the badge.
     * @param string $class The CSS class for styling the badge.
     * @return string The HTML for the badge.
     */
    function makeLabel($value, $class = 'info', ?string $icon = null): string
    {
        if ($icon) {
            $value = '<i class="rex-icon ' . htmlspecialchars($icon) . '"></i> ' . htmlspecialchars($value);
        }
        return '<span class="label label-' . htmlspecialchars($class) . '"> ' . $value . '</span>';
    }
}


$article = rex_article::get($profile['article_id'] ?? 0, $profile['clang_id'] ?? 0);
if ($article instanceof rex_article) {
    $article_frontend_url = $article->getUrl();
    $article_backend_url = rex_url::backendPage('content/edit', ['article_id' => $profile['article_id'], 'clang' => $profile['clang_id']]);
}
$clang = rex_clang::get($profile['clang_id'] ?? 0);

$domain = rex_yrewrite::getDomainByArticleId($profile['article_id'] ?? 0, $profile['clang_id'] ?? 0);
if ($domain) {
    $domain = $domain->getUrl();
} else {
    $domain = rex::getServer();
}

$query = 'SELECT COUNT(*) AS total FROM ' . rex::getTable('url_generator_url') . ' WHERE id = :id';
$params = ["id" => $profile['id'] ?? 0];
$total = rex_sql::factory()->setQuery($query, $params);
$total = $total->getValue('total') ?: 0;
if ($total > 0) {
    $total = ' (' . $total . ')';
} else {
    $total = '';
}

$is_yform_table = false;
if (rex_yform_manager_table::get($tableName)) {
    $is_yform_table = true;
    // Link zur YForm-Tabelle
    $backend_page = rex_url::backendPage('yform/manager/data_edit', [
        'table_name' => $tableName,
    ], false);
}

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<strong class="panel-title"><span
				style="font-weight: 300">#<?= $profile['id'] ?></span>
			<?php echo htmlspecialchars($this->getVar('profile')['namespace'] ?? ''); ?></strong>
		<?php echo $total; ?>

		<a href="<?php echo rex_url::backendPage('url/generator/profiles', ['func' => 'refresh', 'id' => $profile['id'] ?? ''] + rex_csrf_token::factory('url_profile_refresh')->getUrlParams()); ?>"
			class="btn btn-primary btn-xs pull-right"
			data-confirm="<?php echo rex_i18n::msg('url_generator_url_refresh'); ?> ?">
			<i class="rex-icon rex-icon-refresh"></i>
			<?php echo rex_i18n::msg('url_generator_url_refresh'); ?>
		</a>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-md-12">
				<?php
                $url_segments = '';
if ($domain) {
    $url_segments .= '<code>' . htmlspecialchars(trim($domain, '/')) . '</code> <code>' . htmlspecialchars(trim($article->getUrl(), '')) . '</code> ';
}
if (!empty($tableParameters['column_segment_part_1'])) {
    $url_segments .= ' <code>' . htmlspecialchars($tableParameters['column_segment_part_1']) . '</code>';
}
if (!empty($tableParameters['column_segment_part_2'])) {
    $url_segments .= ' <code>' . htmlspecialchars($tableParameters['column_segment_part_2_separator']) . '</code> <code>' . htmlspecialchars($tableParameters['column_segment_part_2'] ?? '') . '</code>';
}
if (!empty($tableParameters['column_segment_part_3'])) {
    $url_segments .= ' <code>' . htmlspecialchars($tableParameters['column_segment_part_3_separator']) . '</code> <code>' . htmlspecialchars($tableParameters['column_segment_part_3'] ?? '') . '</code>';
}

echo rex_i18n::msg('url.profile.segments')  . ':' . $url_segments;

?>
				<p class="help-block rex-note" style="font-size: 0.8em;">
					Aufruf via
					<code class="url-copy-code" 
						  data-copy="rex_getUrl('', '', ['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])" 
						  title="Klicken zum Kopieren">rex_getUrl('', '', ['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])</code>
					oder via Artikel
					<code class="url-copy-code" 
						  data-copy="rex_article::get(<?= $article->getId() ?>)->getUrl(['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])" 
						  title="Klicken zum Kopieren">rex_article::get(<?= $article->getId() ?>)->getUrl(['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])</code><br>


				</p>
			</div>
			<div class="col-12 col-md-6">
				<h5><i class="rex-icon rex-icon-article"></i>
					<?= rex_i18n::msg('url_generator_profiles_article')  ?>
					<?= ' (' . htmlspecialchars($clang->getName()) .
        ')' ?>

				</h5>
				<?= htmlspecialchars($article->getName()); ?>
				<a href="<?= $article_frontend_url ?>"
					class="btn btn-default btn-xs" style="margin-right: 5px;">
					<i class="rex-icon rex-icon-frontend"></i>
					<?php echo rex_i18n::msg('url_generator_profiles_frontend'); ?>
				</a>
				<a href="<?= $article_backend_url ?>"
					class="btn btn-default btn-xs" style="margin-right: 5px;">
					<i class="rex-icon rex-icon-backend"></i>
					<?php echo rex_i18n::msg('url_generator_profiles_backend'); ?>
				</a>
				<h5><i class="rex-icon fa-database"></i>
					<?= rex_i18n::msg('url_generator_profiles_table')  ?>
				</h5>
				<?php echo htmlspecialchars($tableName); ?>
				<a href="<?= $backend_page ?? '' ?>"
					class="btn btn-default btn-xs" style="margin-left: 5px;">
					<i class="rex-icon fa-list"></i>
					<?php echo rex_i18n::msg('url_generator_profiles_yform_data'); ?>
				</a>
				<!-- Relationen -->
				<h5><i class="rex-icon fa-project-diagram"></i>
					<?= rex_i18n::msg('url_generator_profiles_relations')  ?>
				</h5>
				<?php
if ($tableParameters['append_structure_categories'] === '1') {
    echo makeLabel(rex_i18n::msg('url.profile.append_structure_categories'), 'success', 'fa-folder') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.append_structure_categories.none'), 'danger', 'fa-times') . ' ';
}

if ($tableParameters['append_user_paths'] === '1') {
    echo makeLabel(rex_i18n::msg('url.profile.append_user_paths'), 'success', 'fa-user') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.append_user_paths.none'), 'danger', 'fa-times') . ' ';
}
?>
			</div>
			<div class="col-12 col-md-4">
				<h5><i class="rex-icon fa-filter"></i> Filter</h5>

				<?php
if ($tableParameters['restriction_1_column'] !== '') {
    $value = $tableParameters['restriction_1_column'] . ' ' . $tableParameters['restriction_1_comparison_operator'] . ' ' . $tableParameters['restriction_1_value'];
    echo makeLabel($value, 'info', '') . ' ';
}
if ($tableParameters['restriction_2_column'] !== '') {
    $value = $tableParameters['restriction_2_column'] . ' ' . $tableParameters['restriction_2_comparison_operator'] . ' ' . $tableParameters['restriction_2_value'];
    echo makeLabel($value, 'info', '') . ' ';
}
if ($tableParameters['restriction_3_column'] !== '') {
    $value = $tableParameters['restriction_3_column'] . ' ' . $tableParameters['restriction_3_comparison_operator'] . ' ' . $tableParameters['restriction_3_value'];
    echo makeLabel($value, 'info', '') . ' ';
}
?>
				<h5><i class="rex-icon fa-google"></i> SEO-Einstellungen</h5>
				<?= makeLabel("Sitemap", ($tableParameters['sitemap_add'] === 1) ? 'success' : 'danger', 'fa-sitemap') . ' '; ?>
				<?php
if ($tableParameters['column_seo_title'] !== '') {
    echo makeLabel(rex_i18n::msg('url.profile.seo_title') . ': ' . htmlspecialchars($tableParameters['column_seo_title']), 'success', 'fa-tag') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.seo_title') . ': ' . rex_i18n::msg('url.profile.not_set'), 'danger', 'fa-times') . ' ';
}
if ($tableParameters['column_seo_description'] !== '') {
    echo makeLabel(rex_i18n::msg('url.profile.seo_description') . ': ' . htmlspecialchars($tableParameters['column_seo_description']), 'success', 'fa-info-circle') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.seo_description') . ': ' . rex_i18n::msg('url.profile.not_set'), 'danger', 'fa-times') . ' ';
}
if ($tableParameters['column_seo_image'] !== '') {
    echo makeLabel(rex_i18n::msg('url.profile.seo_image') . ': ' . htmlspecialchars($tableParameters['column_seo_image']), 'success', 'fa-image') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.seo_image') . ': ' . rex_i18n::msg('url.profile.not_set'), 'danger', 'fa-times') . ' ';
}
if ($tableParameters['column_sitemap_lastmod'] !== '') {
    echo makeLabel(rex_i18n::msg('url.profile.sitemap-lastmod') . ': ' . htmlspecialchars($tableParameters['column_sitemap_lastmod']), 'success', 'fa-calendar') . ' ';
} else {
    echo makeLabel(rex_i18n::msg('url.profile.sitemap-lastmod') . ': ' . rex_i18n::msg('url.profile.not_set'), 'danger', 'fa-times') . ' ';
}
?>
			</div>
			<div class="col-12 col-md-2">
				<h5><i class="rex-icon fa-globe"></i> Aktionen</h5>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<a href="<?php echo rex_url::backendPage('url/generator/profiles', ['func' => 'edit', 'id' => $profile['id'] ?? '']); ?>"
			class="btn btn-default">
			<i class="rex-icon rex-icon-edit"></i>
			<?php echo rex_i18n::msg('edit'); ?>
		</a>
		<a href="<?php echo rex_url::backendPage('url/generator/profiles', ['func' => 'delete', 'id' => $profile['id'] ?? ''] + rex_csrf_token::factory('url_profile_delete')->getUrlParams()); ?>"
			class="btn btn-danger pull-right"
			data-confirm="<?php echo rex_i18n::msg('delete'); ?> ?">
			<i class="rex-icon rex-icon-delete"></i>
			<?php echo rex_i18n::msg('delete'); ?>
		</a>
	</div>
</div>

<script type="text/javascript">
(function($) {
    // Copy to clipboard functionality
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            // Modern clipboard API
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            return new Promise(function(resolve, reject) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    var successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    if (successful) {
                        resolve();
                    } else {
                        reject(new Error('Copy failed'));
                    }
                } catch (err) {
                    document.body.removeChild(textArea);
                    reject(err);
                }
            });
        }
    }

    function showCopyFeedback(element) {
        var feedback = $('<span class="url-copy-feedback">Kopiert!</span>');
        element.css('position', 'relative').append(feedback);
        
        setTimeout(function() {
            feedback.css('opacity', '1');
        }, 10);
        
        setTimeout(function() {
            feedback.css('opacity', '0');
            setTimeout(function() {
                feedback.remove();
            }, 300);
        }, 1500);
    }

    // Event handler for copy buttons
    $(document).on('click', '.url-copy-code', function(e) {
        e.preventDefault();
        var $this = $(this);
        var textToCopy = $this.data('copy');
        
        if (!textToCopy) {
            return;
        }
        
        copyToClipboard(textToCopy).then(function() {
            // Success feedback
            $this.addClass('copied');
            showCopyFeedback($this);
            
            setTimeout(function() {
                $this.removeClass('copied');
            }, 2000);
        }).catch(function(err) {
            // Error feedback
            console.error('Copy failed:', err);
            var feedback = $('<span class="url-copy-feedback url-copy-feedback-error">Fehler beim Kopieren</span>');
            $this.css('position', 'relative').append(feedback);
            
            setTimeout(function() {
                feedback.css('opacity', '1');
            }, 10);
            
            setTimeout(function() {
                feedback.css('opacity', '0');
                setTimeout(function() {
                    feedback.remove();
                }, 300);
            }, 2000);
        });
    });
})(jQuery);
</script>
