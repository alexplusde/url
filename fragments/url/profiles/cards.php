<?php

/** @var rex_fragment $this */


$query = '  SELECT * FROM '.rex::getTable('url_generator_profile').' ORDER BY `namespace`';

$profiles = rex_sql::factory()->getArray($query);

$list = rex_list::factory($query);

$content = '';

$content .= '<div style="margin: 0 0 10px"><a class="btn btn-primary" href="'.$list->getUrl(['func' => 'add']).'"'.rex::getAccesskey($this->i18n('add'), 'add').'><i class="rex-icon rex-icon-add-article"></i> Profil erstellen</a></div>';

$yform_tables = rex_yform_manager_table::getAll();

// Grid erstellen
if (empty($profiles)) {
    $content .= '<div class="alert alert-info">' . rex_i18n::msg('url_generator_profiles_empty') . '</div>';
}

foreach ($profiles as $profile) {
    $this->setVar('yform_tables', $yform_tables);
    $this->setVar('profile', $profile, false);
    $content .= $this->getSubfragment('url/profiles/card.php');
}

echo $content;
