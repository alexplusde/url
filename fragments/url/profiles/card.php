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
        } else {
            $value = htmlspecialchars($value);
        }
        return '<span class="label label-' . htmlspecialchars($class) . '"> ' . $value . '</span>';
    }
}


$article = rex_article::get($profile['article_id'] ?? 0, $profile['clang_id'] ?? 0);
if ($article instanceof rex_article) {
    $article_frontend_url = $article->getUrl();
    $article_backend_url = rex_url::backendPage('content/edit', ['article_id' => $profile['article_id'], 'clang' => $profile['clang_id']]);

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
    if (class_exists('rex_yform_manager_table') && rex_yform_manager_table::get($tableName)) {
        $is_yform_table = true;
        // Link zur YForm-Tabelle
        $backend_page = rex_url::backendPage('yform/manager/data_edit', [
            'table_name' => $tableName,
        ], false);
    }

    /**
     * Builds a copy-friendly basis-module template for the given profile.
     *
     * The resulting snippet contains:
     *  - the typical "if ($manager !== null) { detail } else { list }" pattern
     *  - dataset retrieval based on the YOrm Model class (when available) with all
     *    public getters of the model class pre-listed as sample output
     *  - a list/overview branch using the profile namespace via rex_getUrl()
     *  - if the profile has relation tables, an additional getTableName() switch
     *    that distinguishes records originating from the main table vs. relation tables.
     *
     * @param array $profile          Raw profile row from the database
     * @param string $tableName       Plain main table name (without dbid prefix)
     * @return string                 Generated PHP code as plain text
     */
    if (!function_exists('buildProfileTemplate')) {
        function buildProfileTemplate(array $profile, string $tableName): string
        {
            $namespace = (string) ($profile['namespace'] ?? '');

            // Try to resolve YOrm model classes for the main table and its relation tables.
            $resolveModelClass = static function (string $table): ?string {
                if ($table === '' || !class_exists('rex_yform_manager_dataset')) {
                    return null;
                }
                if (!is_callable(['rex_yform_manager_dataset', 'getModelClass'])) {
                    return null;
                }
                $model = rex_yform_manager_dataset::getModelClass($table);
                return $model ?: null;
            };

            // Generate the public getter calls of a model class as echo-tag lines.
            $renderGetters = static function (?string $modelClass, string $objectVar): string {
                if ($modelClass === null || !class_exists($modelClass)) {
                    return '';
                }
                try {
                    $reflection = new ReflectionClass($modelClass);
                } catch (ReflectionException $e) {
                    return '';
                }
                $lines = [];
                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->isStatic() || strpos($method->getName(), 'get') !== 0) {
                        continue;
                    }
                    if ($method->getName() === 'get' || $method->getName() === 'getInstance') {
                        continue;
                    }
                    $params = [];
                    $hasRequiredParams = false;
                    foreach ($method->getParameters() as $param) {
                        if (!$param->isOptional()) {
                            $hasRequiredParams = true;
                        }
                        $type = '';
                        if ($param->hasType()) {
                            $reflectionType = $param->getType();
                            // Only render simple named types to avoid issues with union/intersection types
                            if ($reflectionType instanceof ReflectionNamedType) {
                                $type = $reflectionType->getName() . ' ';
                            } else {
                                $type = (string) $reflectionType . ' ';
                            }
                        }
                        $default = $param->isDefaultValueAvailable()
                            ? ' = ' . var_export($param->getDefaultValue(), true)
                            : '';
                        $params[] = '/* ' . $type . '*/ $' . $param->getName() . $default;
                    }
                    $paramsStr = implode(', ', $params);
                    $call = '<?= rex_escape($' . $objectVar . '->' . $method->getName() . '(' . $paramsStr . ')) ?>';
                    if ($hasRequiredParams) {
                        // Getter requires arguments – emit as a commented-out example so the
                        // copied module stays runnable. Developers can uncomment and supply values.
                        $lines[] = '    <!-- ' . $call . ' -->';
                    } else {
                        $lines[] = '    ' . $call;
                    }
                }
                return implode("\n", $lines);
            };

            $mainModel = $resolveModelClass($tableName);

            // Collect optional relation tables (with their model classes).
            $relations = [];
            for ($i = 1; $i <= 3; ++$i) {
                $relTableRaw = (string) ($profile['relation_' . $i . '_table_name'] ?? '');
                if ($relTableRaw === '') {
                    continue;
                }
                $relTable = explode('_xxx_', $relTableRaw)[1] ?? $relTableRaw;
                $relations[] = [
                    'table' => $relTable,
                    'model' => $resolveModelClass($relTable),
                ];
            }
            $hasRelations = count($relations) > 0;

            // ---- Detail branch (single dataset) -------------------------------------------------
            $detailFetch = $mainModel !== null
                ? '    $dataset = ' . $mainModel . '::get($manager->getDatasetId());'
                : '    $dataset = rex_yform_manager_table::get(\'' . $tableName . '\')->query()->findId($manager->getDatasetId());';

            $detailGetters = $renderGetters($mainModel, 'dataset');
            $detailGettersBlock = $detailGetters !== ''
                ? "\n        <?php /* Verfügbare Getter der Model-Klasse " . $mainModel . " */ ?>\n" . $detailGetters . "\n"
                : "        <h1><?= rex_escape(\$dataset->getValue('name')) ?></h1>\n";

            // ---- Detail body --------------------------------------------------------------------
            // Note: Url\Profile::getTableName() always returns the profile's main table, so we
            // cannot reliably branch on the originating relation table here. If relations are
            // configured we still emit a comment listing them so developers know they exist and
            // can extend the module manually if their setup requires it.
            $relationsComment = '';
            if ($hasRelations) {
                $relationsComment = "    // Hinweis: Dieses Profil verweist zusätzlich auf folgende Relationstabelle(n):\n";
                foreach ($relations as $rel) {
                    $relationsComment .= "    //   - " . $rel['table']
                        . ($rel['model'] !== null ? ' (Model: ' . $rel['model'] . ')' : '')
                        . "\n";
                }
                $relationsComment .= "    // Der aufgelöste Datensatz stammt immer aus der Haupttabelle '" . $tableName . "'.\n\n";
            }

            $detailBody = $relationsComment
                . $detailFetch . "\n"
                . "    if (\$dataset) {\n"
                . "        ?>\n"
                . $detailGettersBlock
                . "        <?php\n"
                . "    }\n";

            // ---- List branch (overview) ---------------------------------------------------------
            $listFetch = $mainModel !== null
                ? '    $datasets = ' . $mainModel . '::query()->find();'
                : '    $datasets = rex_yform_manager_table::get(\'' . $tableName . '\')->query()->find();';

            $listLabel = $mainModel !== null && method_exists($mainModel, 'getName')
                ? '$dataset->getName()'
                : "\$dataset->getValue('name')";

            // ---- Final assembly -----------------------------------------------------------------
            $code = "<?php\n"
                . "use Url\\Url;\n\n"
                . "\$manager = Url::resolveCurrent();\n\n"
                . "if (\$manager !== null) {\n"
                . "    // === Detailseite =====================================================\n"
                . $detailBody
                . "} else {\n"
                . "    // === Übersicht / Liste ==============================================\n"
                . $listFetch . "\n"
                . "    foreach (\$datasets as \$dataset) {\n"
                . "        ?>\n"
                . "        <a href=\"<?= rex_getUrl('', '', ['" . $namespace . "' => \$dataset->getId()]) ?>\">\n"
                . "            <?= rex_escape(" . $listLabel . ") ?>\n"
                . "        </a>\n"
                . "        <?php\n"
                . "    }\n"
                . "}\n";

            return $code;
        }
    }

    $profile_template_code = buildProfileTemplate($profile, $tableName);
    $profile_template_modal_id = 'url-profile-template-' . (int) ($profile['id'] ?? 0);

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
					<code>rex_getUrl('', '', ['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])</code>
					oder via Artikel
					<code>rex_article::get(<?= $article->getId() ?>)->getUrl(['<?= htmlspecialchars($profile['namespace'] ?? '') ?>' => {id}])</code><br>


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


				<?php
    // Display YForm model class if available
    if ($is_yform_table && class_exists('rex_yform_manager_dataset') && is_callable(['rex_yform_manager_dataset', 'getModelClass'])) {
        $modelClass = rex_yform_manager_dataset::getModelClass($tableName);
        if ($modelClass) {
            echo '<br><small>' . makeLabel('Model: ' . $modelClass, 'info', 'fa-code') . '</small>';

            // Check if model class has getUrl() method
            if (method_exists($modelClass, 'getUrl')) {
                echo ' ' . makeLabel('✅ getUrl()', 'success', null);
            }
        } else {
            echo '<br><small>' . makeLabel('Model: ' . rex_i18n::msg('url.profile.not_set'), 'default', 'fa-times') . '</small>';
        }

        // Display dataset identification field only if it's not the default 'id'
        $columnId = $tableParameters['column_id'] ?? 'id';
        if ($columnId !== 'id') {
            echo '<br><small class="text-muted">' . rex_i18n::msg('url_generator_identify_record') . ': <code>' . htmlspecialchars($columnId) . '</code></small>';
        }
    }
    ?>
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
		<button type="button" class="btn btn-default"
			data-toggle="modal" data-target="#<?= $profile_template_modal_id ?>"
			title="<?= rex_i18n::msg('url_generator_profile_copy_template_title') ?>">
			<i class="rex-icon fa-clipboard"></i>
			<?= rex_i18n::msg('url_generator_profile_copy_template') ?>
		</button>
		<a href="<?php echo rex_url::backendPage('url/generator/profiles', ['func' => 'delete', 'id' => $profile['id'] ?? ''] + rex_csrf_token::factory('url_profile_delete')->getUrlParams()); ?>"
			class="btn btn-danger pull-right"
			data-confirm="<?php echo rex_i18n::msg('delete'); ?> ?">
			<i class="rex-icon rex-icon-delete"></i>
			<?php echo rex_i18n::msg('delete'); ?>
		</a>
	</div>
</div>

<div class="modal fade" id="<?= $profile_template_modal_id ?>" tabindex="-1" role="dialog"
	aria-labelledby="<?= $profile_template_modal_id ?>-label" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="<?= $profile_template_modal_id ?>-label">
					<i class="rex-icon fa-clipboard"></i>
					<?= rex_i18n::msg('url_generator_profile_copy_template_title') ?>
					<small>#<?= (int) ($profile['id'] ?? 0) ?>
						<?= htmlspecialchars($profile['namespace'] ?? '') ?></small>
				</h4>
			</div>
			<div class="modal-body">
				<p class="help-block"><?= rex_i18n::msg('url_generator_profile_copy_template_help') ?></p>
				<pre style="max-height: 60vh; overflow: auto;"><code id="<?= $profile_template_modal_id ?>-code"><?= htmlspecialchars($profile_template_code) ?></code></pre>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<?= rex_i18n::msg('cancel') ?>
				</button>
				<button type="button" class="btn btn-primary"
					data-url-copy-target="#<?= $profile_template_modal_id ?>-code"
					data-url-copy-label-default="<?= rex_i18n::msg('url_generator_profile_copy_template_button') ?>"
					data-url-copy-label-success="<?= rex_i18n::msg('url_generator_profile_copy_template_copied') ?>">
					<i class="rex-icon fa-clipboard"></i>
					<?= rex_i18n::msg('url_generator_profile_copy_template_button') ?>
				</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	(function () {
		var modal = document.getElementById('<?= $profile_template_modal_id ?>');
		if (!modal || modal.dataset.urlCopyBound === '1') {
			return;
		}
		modal.dataset.urlCopyBound = '1';
		var btn = modal.querySelector('[data-url-copy-target]');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function () {
			var target = modal.querySelector(btn.getAttribute('data-url-copy-target'));
			if (!target) {
				return;
			}
			var text = target.textContent || '';
			var done = function () {
				var original = btn.getAttribute('data-url-copy-label-default');
				var success = btn.getAttribute('data-url-copy-label-success');
				btn.innerHTML = '<i class="rex-icon fa-check"></i> ' + success;
				setTimeout(function () {
					btn.innerHTML = '<i class="rex-icon fa-clipboard"></i> ' + original;
				}, 2000);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(done, function () {});
			} else {
				var range = document.createRange();
				range.selectNodeContents(target);
				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
				try {
					document.execCommand('copy');
					done();
				} catch (e) {}
				sel.removeAllRanges();
			}
		});
	})();
</script>
<?php

}

?>
