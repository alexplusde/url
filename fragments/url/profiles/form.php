<?php

use Url\Database;
use Url\Profile;
use Url\UrlManager;

$addon = $this->getVar('addon');
$id = rex_request('id', 'int');
$func = rex_request('func', 'string', 'add');

$title = $func === 'edit' ? $addon->i18n('edit') : $addon->i18n('add');

    rex_extension::register('REX_FORM_CONTROL_FIELDS', function (rex_extension_point $ep) {
        $controlFields = $ep->getSubject();
        $controlFields['delete'] = '';
        return $controlFields;
    });

    $form = rex_form::factory(rex::getTable('url_generator_profile'), '', 'id = '.$id, 'post', false);
    $form->addParam('id', $id);
    $form->addParam('action', 'cache');
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode($func === 'edit');

    $form->addFieldset($addon->i18n('url_generator_article_legend'));
    $form->addErrorMessage(REX_FORM_ERROR_VIOLATE_UNIQUE_KEY, $addon->i18n('url_generator_namespace_error'));

    $form->addHiddenField('ep_pre_save_called', '0');

    $fieldNamespace = $form->addTextField('namespace');
    $fieldNamespace->setHeader('
        <div class="col-md-4">
                <label>'.$addon->i18n('url_generator_namespace').'</label>');
    $fieldNamespace->setFooter('
        </div>');
    $fieldNamespace->getValidator()
        ->add('notEmpty', $addon->i18n('url_generator_namespace_error'))
        ->add('match', $addon->i18n('url_generator_namespace_error'), '/^[a-z0-9_-]*$/');

    $fieldArticleId = $form->addLinkmapField('article_id');
    $fieldArticleId->setHeader('
        <div class="col-md-4">
                <label>'.$addon->i18n('url_generator_structure_article').'</label>');
    $fieldArticleId->getValidator()
        ->add('notEmpty', $addon->i18n('url_generator_article_error'))
        ->add('match', $addon->i18n('url_generator_article_error'), '/^[1-9][0-9]*$/');

    if (count(rex_clang::getAll()) >= 2) {
        $fieldArticleId->setFooter('
                </div>');

        $fieldArticleClangId = $form->addSelectField('clang_id');
        $fieldArticleClangId->setHeader('
                <div class="col-md-4">');
        $fieldArticleClangId->setFooter('</div>');
        $fieldArticleClangId->setNotice($addon->i18n('url_generator_article_clang').'; '.$addon->i18n('url_generator_article_clang_notice', $addon->i18n('url_generator_identify_record')));
        $select = $fieldArticleClangId->getSelect();
        $select->addOption($addon->i18n('url_generator_article_clang_option_all'), '0');
        foreach (rex_clang::getAll() as $clang) {
            $select->addOption($clang->getName(), $clang->getId());
        }
    } else {
        $fieldArticleId->setFooter('
            </div>');
    }

    $form->addFieldset($addon->i18n('url_generator_table_legend'));

    $fieldTable = $form->addSelectField('table_name');
    $fieldTable->setHeader('
        <div class="addon-url-grid">
            <div class="addon-url-grid-item">
                <label>'.$addon->i18n('url_generator_table').'</label>
            </div>
            <div class="addon-url-grid-item">');
    $fieldTable->setFooter('
            </div>
        </div>');
    $fieldTable->getValidator()
        ->add('notEmpty', $addon->i18n('url_generator_table_error'));
    $fieldTableSelect = $fieldTable->getSelect();
    $fieldTableSelect->addOption($addon->i18n('url_generator_table_not_selected'), '');

    $script = '
    <script type="text/javascript">
    <!--
    (function($) {
        var currentShown = null;
        $("#'.$fieldTable->getAttribute('id').'").change(function(){
            if(currentShown) currentShown.hide().find(":input").prop("disabled", true);
            var tableParamsId = "#rex-"+ jQuery(this).val();
            currentShown = $(tableParamsId);
            currentShown.show().find(":input").prop("disabled", false);
        }).change();
    })(jQuery);
    //-->
    </script>';

    $fieldContainer = $form->addContainerField('table_parameters');
    $fieldContainer->setAttribute('style', 'display: none');
    $fieldContainer->setSuffix($script);
    $fieldContainer->setMultiple(false);
    $fieldContainer->setActive($fieldTable->getValue());

    $supportedTables = Database::getSupportedTables();

    $fields = [];
    foreach ($supportedTables as $DBID => $databases) {
        $fieldTableSelect->addOptgroup($databases['name']);
        foreach ($databases['tables'] as $table) {
            $fieldTableSelect->addOption($table['name'], $table['name_unique']);
            foreach ($table['columns'] as $column) {
                $fields[$table['name_unique']][] = $column['name'];
            }
        }
    }

    if (count($fields) > 0) {
        foreach ($fields as $table => $columns) {
            $group = $table;
            $options = $columns;

            $type = 'select';
            $name = 'column_id';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                <hr class="addon-url-hr">
                <div class="addon-url-grid">
                    <div class="addon-url-grid-item">
                        <label>'.$addon->i18n('url_generator_identify_record').'</label>
                    </div>
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>');
            $f->setAttribute('disabled', 'true');
            $select = $f->getSelect();
            $select->setAttribute('class', 'form-control selectpicker');
            $select->setAttribute('data-live-search', 'true');

            $select->addOptions($options, true);

            if (count(rex_clang::getAll()) >= 2) {
                $type = 'select';
                $name = 'column_clang_id';
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader('
                        <div class="addon-url-grid-item">');
                $f->setFooter('
                        </div>
                    </div>');
                $f->setAttribute('disabled', 'true');
                $f->setNotice($addon->i18n('url_language').' '.$addon->i18n('url_generator_clang_id_notice'));
                $select = $f->getSelect();
                $select->addOption($addon->i18n('url_generator_no_clang_id'), '');
                $select->addOptions($options, true);
            } else {
                $type = 'hidden';
                $name = 'column_clang_id';
                $f = $fieldContainer->addGroupedField($group, $type, $name, '');
                $f->setFooter('</div>');
            }

            for ($i = 1; $i <= Profile::RESTRICTION_COUNT; ++$i) {
                if ($i > 1) {
                    $type = 'select';
                    $name = 'restriction_'.$i.'_logical_operator';
                    /** @var rex_form_select_element $f */

                    $f = $fieldContainer->addGroupedField($group, $type, $name);
                    $f->setHeader('
                                <div class="addon-url-grid">
                                    <div class="addon-url-grid-item" data-addon-url-size="3of10" data-addon-url-shift="2of10">');
                    $f->setFooter('
                                    </div>
                                </div>');
                    $f->setAttribute('disabled', 'true');
                    $select = $f->getSelect();
                    $select->addOption('', '');
                    $select->addOptions(Database::getLogicalOperators());
                }

                $type = 'select';
                $name = 'restriction_'.$i.'_column';
                /** @var rex_form_select_element $f */
                $f = $fieldContainer->addGroupedField($group, $type, $name);

                $prependHeader = '';
                if ($i == 1) {
                    $prependHeader = '
                    <hr class="addon-url-hr" />
                    <div class="addon-url-grid">
                        <div class="addon-url-grid-item">
                            <label>'.$addon->i18n('url_generator_restriction').'</label>
                            <p class="help-block">'.$addon->i18n('url_generator_restriction_notice').'</p>
                        </div>
                        <div class="addon-url-grid-item" data-addon-url-size="10">';
                }
                $f->setHeader(
                        $prependHeader.'
                            <div class="addon-url-grid">
                                <div class="addon-url-grid-item" data-addon-url-size="3of10">');
                $f->setFooter('
                                </div>');
                $f->setAttribute('disabled', 'true');
                $select = $f->getSelect();
                $select->addOption($addon->i18n('url_generator_no_restriction'), '');
                $select->addOptions($options, true);
                $select->setAttribute('class', 'form-control selectpicker');
                $select->setAttribute('data-live-search', 'true');

                $type = 'select';
                $name = 'restriction_'.$i.'_comparison_operator';
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader('<div class="addon-url-grid-item" data-addon-url-size="1of10">');
                $f->setFooter('</div>');
                $f->setAttribute('disabled', 'true');
                $select = $f->getSelect();
                $select->addOptions(Database::getComparisonOperators());

                $type = 'text';
                $name = 'restriction_'.$i.'_value';
                $value = '';
                /* @var $f rex_form_element */
                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader('
                                <div class="addon-url-grid-item" data-addon-url-size="3of10">');

                $appendFooter = ($i == Profile::RESTRICTION_COUNT) ? '</div></div>' : '';
                $f->setFooter('
                                </div>
                            </div>'.$appendFooter);
                $f->setAttribute('disabled', 'true');
            }

            for ($i = 1; $i <= Profile::SEGMENT_PART_COUNT; ++$i) {
                if ($i > 1) {
                    $type = 'select';
                    $name = 'column_segment_part_'.$i.'_separator';
                    /** @var rex_form_select_element $f */

                    $f = $fieldContainer->addGroupedField($group, $type, $name);
                    $f->setHeader('<div class="addon-url-grid-item text-center addon-url-text-large">');
                    $f->setFooter('</div>');
                    $f->setAttribute('disabled', 'true');
                    $select = $f->getSelect();
                    $select->addOptions(UrlManager::getSegmentPartSeparators());
                }

                $type = 'select';
                $name = 'column_segment_part_'.$i;
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);

                $prependHeader = '';
                if ($i == 1) {
                    $prependHeader = '
                    <hr class="addon-url-hr" />
                    <div class="addon-url-grid">
                        <div class="addon-url-grid-item">
                            <label>'.$addon->i18n('url').'</label>
                            <p class="help-block">'.$addon->i18n('url_generator_url_notice').'</p>
                        </div>
                    ';
                }
                $f->setHeader($prependHeader.'
                        <div class="addon-url-grid-item">');

                $appendFooter = ($i == Profile::SEGMENT_PART_COUNT) ? '</div>' : '';
                $f->setFooter('
                        </div>'.$appendFooter);
                $f->setAttribute('disabled', 'true');
                $select = $f->getSelect();
                $select->setAttribute('class', 'form-control selectpicker');
                $select->setAttribute('data-live-search', 'true');
                if ($i > 1) {
                    $select->addOption($addon->i18n('url_generator_no_additive'), '');
                }
                $select->addOptions($options, true);
            }

            for ($i = 1; $i <= Profile::RELATION_COUNT; ++$i) {
                $prependHeader = '';
                if ($i == 1) {
                    $prependHeader = '
                    <hr class="addon-url-hr">
                    <div class="addon-url-grid">
                        <div class="addon-url-grid-item">
                            <label>'.$addon->i18n('url_generator_relation_paths').'</label>
                            <p class="help-block">'.$addon->i18n('url_generator_relation_column_notice').'</p>
                            <p class="help-block">'.$addon->i18n('url_generator_relation_position_in_url').'<br />'.$addon->i18n('url_generator_relation_position_notice').' '.$addon->i18n('url_generator_relation_position_notice__2').'</p>
                        </div>
                        <div class="addon-url-grid-item" data-addon-url-size="10">
                            <div class="addon-url-grid">
                                <div class="addon-url-grid-item" data-addon-url-size="2of10" data-addon-url-shift="1of10">
                                    <p class="help-block">'.$addon->i18n('url_generator_relation_column', '').'</p>
                                </div>
                                <div class="addon-url-grid-item" data-addon-url-size="2of10">
                                    <p class="help-block">'.$addon->i18n('url_generator_relation_position_in_url').'</p>
                                </div>
                            </div>';
                }

                $type = 'select';
                $name = 'relation_'.$i.'_column';
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader(
                        $prependHeader.'
                            <div class="addon-url-grid">
                                <div class="addon-url-grid-item" data-addon-url-size="1of10"><label>'.$addon->i18n('url_generator_relation', $i).'</label></div>
                                <div class="addon-url-grid-item" data-addon-url-size="2of10">');
                $f->setFooter('
                                </div>');
                $f->setPrefix('<div class="js-change-relation-'.$i.'-select">');
                $f->setSuffix('</div>');
                $f->setAttribute('disabled', 'true');
                $f->setAttribute('class', 'form-control selectpicker');
                $f->setAttribute('data-live-search', 'true');
    
                // $f->setNotice($addon->i18n('url_generator_relation_column_notice'));
                $select = $f->getSelect();
                $select->addOption($addon->i18n('url_generator_no_relation_column'), '');
                $select->addOptions($options, true);

                $type = 'select';
                $name = 'relation_'.$i.'_position';
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader('
                                <div class="addon-url-grid-item" data-addon-url-size="2of10">');
                $appendFooter = '';
                if ($i == Profile::RELATION_COUNT) {
                    $appendFooter = '
                            <div class="addon-url-grid">
                                <div class="addon-url-grid-item" data-addon-url-size="1of10">
                                    <p class="help-block">'.$addon->i18n('url_generator_relation_position_eg_label', '').'</p>
                                </div>
                                <div class="addon-url-grid-item" data-addon-url-size="4of10">
                                    <p class="help-block">'.$addon->i18n('url_generator_relation_position_eg_code').'</p>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                $f->setFooter('
                                </div>
                            </div>'.$appendFooter);
                $f->setAttribute('disabled', 'true');
                // $f->setNotice($addon->i18n('url_generator_relation_position_notice'));
                $select = $f->getSelect();
                $select->addOptions(['BEFORE' => $addon->i18n('before'), 'AFTER' => $addon->i18n('after')]);
            }

            $type = 'textarea';
            $name = 'append_user_paths';
            /* @var $f rex_form_element */
            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                <hr class="addon-url-hr">
                <div class="addon-url-grid">
                    <div class="addon-url-grid-item">
                        <label>'.$addon->i18n('url_generator_paths').'</label>
                    </div>
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>');
            $f->setPrefix('<label>'.$addon->i18n('url_generator_append_user_path').'</label>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_append_user_path_notice'));

            $type = 'select';
            $name = 'append_structure_categories';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>
                </div>');
            $f->setPrefix('<label>'.$addon->i18n('url_generator_append_structure_categories_append').'</label>');
            $f->setNotice($addon->i18n('url_generator_append_structure_categories_notice'));
            $select = $f->getSelect();
            $select->addOptions(['0' => $addon->i18n('no'), '1' => $addon->i18n('yes')]);

            $type = 'select';
            $name = 'column_seo_title';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                <hr class="addon-url-hr">
                <div class="addon-url-grid">
                    <div class="addon-url-grid-item">
                        <label>'.$addon->i18n('url_generator_seo').'</label>
                    </div>
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_seo_title_notice'));
            $select = $f->getSelect();
            $select->addOption($addon->i18n('url_generator_no_selection'), '');
            $select->addOptions($options, true);

            $type = 'select';
            $name = 'column_seo_description';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_seo_description_notice'));
            $select = $f->getSelect();
            $select->addOption($addon->i18n('url_generator_no_selection'), '');
            $select->addOptions($options, true);

            $type = 'select';
            $name = 'column_seo_image';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>
                </div>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_seo_image_notice'));
            $select = $f->getSelect();
            $select->addOption($addon->i18n('url_generator_no_selection'), '');
            $select->addOptions($options, true);

            $type = 'select';
            $name = 'sitemap_add';
            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                <hr class="addon-url-hr">
                <div class="addon-url-grid">
                    <div class="addon-url-grid-item">
                        <label>'.$addon->i18n('url_generator_sitemap').'</label>
                    </div>
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_sitemap_add_notice'));
            $select = $f->getSelect();
            $select->addOptions(['0' => $addon->i18n('no'), '1' => $addon->i18n('yes')]);


            $type = 'select';
            $name = 'column_sitemap_lastmod';


            /** @var rex_form_select_element $f */

            $f = $fieldContainer->addGroupedField($group, $type, $name);
            $f->setHeader('
                    <div class="addon-url-grid-item">');
            $f->setFooter('
                    </div>
                </div>');
            $f->setAttribute('disabled', 'true');
            $f->setNotice($addon->i18n('url_generator_sitemap_lastmod_notice'));
            $select = $f->getSelect();
            $select->addOption($addon->i18n('url_generator_no_selection'), '');
            $select->addOptions($options, true);
        }
    }

    for ($i = 1; $i <= Profile::RELATION_COUNT; ++$i) {
        $form->addRawField('<div class="js-change-relation-'.$i.'-container" style="display: none;"><fieldset><legend>'.$addon->i18n('url_generator_table_relation_legend', $i).'</legend>');

        $f = $form->addSelectField('relation_'.$i.'_table_name');
        $f->setHeader('
            <div class="addon-url-grid">
                <div class="addon-url-grid-item">
                    <label>'.$addon->i18n('url_generator_table').'</label>
                </div>
                <div class="addon-url-grid-item">');
        $f->setFooter('
                </div>
            </div>');
        $fieldRelationTableSelect = $f->getSelect();
        $fieldRelationTableSelect->addOption($addon->i18n('url_generator_table_not_selected'), '');

        $activeRelationTable = $f->getValue();

        $script = '
        <script type="text/javascript">
        <!--
        (function($) {
            var currentShown = null;
            $("#'.$f->getAttribute('id').'").change(function(){
                if(currentShown) currentShown.hide().find(":input").prop("disabled", true);
                var tableParamsId = "#rex-"+ jQuery(this).val();
                currentShown = $(tableParamsId);
                currentShown.show().find(":input").prop("disabled", false);
            }).change();
        })(jQuery);
        //-->
        </script>';

        $fields = [];
        foreach ($supportedTables as $DBID => $databases) {
            $fieldRelationTableSelect->addOptgroup($databases['name']);
            foreach ($databases['tables'] as $table) {
                $mergedTableName = Database::merge('relation_'.$i, $table['name_unique']);
                $fieldRelationTableSelect->addOption($table['name'], $mergedTableName);
                foreach ($table['columns'] as $column) {
                    $fields[$mergedTableName][] = $column['name'];
                }
            }
        }

        $fieldContainer = $form->addContainerField('relation_'.$i.'_table_parameters');
        $fieldContainer->setAttribute('style', 'display: none');
        $fieldContainer->setSuffix($script);
        $fieldContainer->setMultiple(false);
        $fieldContainer->setActive($activeRelationTable);

        if (count($fields) > 0) {
            foreach ($fields as $table => $columns) {
                $group = $table;
                $options = $columns;

                $type = 'select';
                $name = 'column_id';
                /** @var rex_form_select_element $f */

                $f = $fieldContainer->addGroupedField($group, $type, $name);
                $f->setHeader('
                    <hr class="addon-url-hr" />
                    <div class="addon-url-grid">
                        <div class="addon-url-grid-item">
                            <label>'.$addon->i18n('url_generator_identify_record').'</label>
                        </div>
                        <div class="addon-url-grid-item">');
                $f->setFooter('
                        </div>');
                $f->setAttribute('disabled', 'true');
                $f->setNotice($addon->i18n('url_generator_id_notice'));
                $select = $f->getSelect();
                $select->addOptions($options, true);

                if (count(rex_clang::getAll()) >= 2) {
                    $type = 'select';
                    $name = 'column_clang_id';
                    /** @var rex_form_select_element $f */

                    $f = $fieldContainer->addGroupedField($group, $type, $name);
                    $f->setHeader('
                            <div class="addon-url-grid-item">');
                    $f->setFooter('
                            </div>
                        </div>');
                    $f->setAttribute('disabled', 'true');
                    $f->setNotice($addon->i18n('url_generator_clang_id_notice'));
                    $select = $f->getSelect();
                    $select->addOption($addon->i18n('url_generator_no_clang_id'), '');
                    $select->addOptions($options, true);
                } else {
                    $f->setFooter('
                        </div>
                    </div>');

                    $type = 'hidden';
                    $name = 'column_clang_id';
                    $f = $fieldContainer->addGroupedField($group, $type, $name, '');
                }

                for ($j = 1; $j <= Profile::SEGMENT_PART_COUNT; ++$j) {
                    if ($j > 1) {
                        $type = 'select';
                        $name = 'column_segment_part_'.$j.'_separator';
                        /** @var rex_form_select_element $f */

                        $f = $fieldContainer->addGroupedField($group, $type, $name);
                        $f->setHeader('<div class="addon-url-grid-item text-center addon-url-text-large">');
                        $f->setFooter('</div>');
                        $f->setAttribute('disabled', 'true');
                        $select = $f->getSelect();
                        $select->addOptions(UrlManager::getSegmentPartSeparators());
                    }

                    $type = 'select';
                    $name = 'column_segment_part_'.$j;
                    /** @var rex_form_select_element $f */

                    $f = $fieldContainer->addGroupedField($group, $type, $name);

                    // $prependHeader = '<div class="addon-url-grid-item text-center addon-url-text-large"><b>/</b></div>';
                    $prependHeader = '';
                    if ($j === 1) {
                        $prependHeader = '
                        <hr class="addon-url-hr" />
                        <div class="addon-url-grid">
                            <div class="addon-url-grid-item">
                                <label>'.$addon->i18n('url').'</label>
                                <p class="help-block">'.$addon->i18n('url_generator_url_notice').'</p>
                            </div>
                        ';
                    }
                    $f->setHeader($prependHeader.'
                            <div class="addon-url-grid-item">');

                    $appendFooter = ($j == Profile::SEGMENT_PART_COUNT) ? '</div>' : '';
                    $f->setFooter('
                            </div>'.$appendFooter);
                    $f->setAttribute('disabled', 'true');
                    $select = $f->getSelect();
                    if ($j > 1) {
                        $select->addOption($addon->i18n('url_generator_no_additive'), '');
                    }
                    $select->addOptions($options, true);
                }
            }
        }

        $form->addRawField('</fieldset></div>');
    }



    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit url-container', false);
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
