<?php

/*

array:33 [▼
    "column_id" => "id"
    "column_clang_id" => ""
    "restriction_1_column" => "status"
    "restriction_1_comparison_operator" => ">="
    "restriction_1_value" => "0"
    "restriction_2_logical_operator" => ""
    "restriction_2_column" => ""
    "restriction_2_comparison_operator" => "="
    "restriction_2_value" => ""
    "restriction_3_logical_operator" => ""
    "restriction_3_column" => ""
    "restriction_3_comparison_operator" => "="
    "restriction_3_value" => ""
    "column_segment_part_1" => "name"
    "column_segment_part_2_separator" => "/"
    "column_segment_part_2" => "id"
    "column_segment_part_3_separator" => "/"
    "column_segment_part_3" => ""
    "relation_1_column" => ""
    "relation_1_position" => "BEFORE"
    "relation_2_column" => ""
    "relation_2_position" => "BEFORE"
    "relation_3_column" => ""
    "relation_3_position" => "BEFORE"
    "append_user_paths" => ""
    "append_structure_categories" => "0"
    "column_seo_title" => "name"
    "column_seo_description" => "short_text"
    "column_seo_image" => "image"
    "sitemap_add" => "1"
    "sitemap_frequency" => "always"
    "sitemap_priority" => "1.0"
    "column_sitemap_lastmod" => "updatedate"
]
    */

namespace Url;

class ProfileServiceTableParameters
{
    /**
     * The name of the table used for profiles.
     * @var string
     */
    private $tableName = 'url_generator_profile';

    private $columnId = '';
    private $columnClangId = '';
    private $restriction1Column = '';
    private $restriction1ComparisonOperator = '>=';
    private $restriction1Value = '0';
    private $restriction2LogicalOperator = '';
    private $restriction2Column = '';
    private $restriction2ComparisonOperator = '=';
    private $restriction2Value = '';
    private $restriction3LogicalOperator = '';
    private $restriction3Column = '';
    private $restriction3ComparisonOperator = '=';
    private $restriction3Value = '';
    private $columnSegmentPart1 = 'name';
    private $columnSegmentPart2Separator = '/';
    private $columnSegmentPart2 = 'id';
    private $columnSegmentPart3Separator = '/';
    private $columnSegmentPart3 = '';
    private $relation1Column = '';
    private $relation1Position = 'BEFORE';
    private $relation2Column = '';
    private $relation2Position = 'BEFORE';
    private $relation3Column = '';
    private $relation3Position = 'BEFORE';
    private $appendUserPaths = '';
    private $appendStructureCategories = '0';
    private $columnSeoTitle = 'name';
    private $columnSeoDescription = 'short_text';
    private $columnSeoImage = 'image';
    private $sitemapAdd = '1';
    private $sitemapFrequency = 'always';
    private $sitemapPriority = '1.0';
    private $columnSitemapLastmod = 'updatedate';

    /**
     * Returns the table parameters as an associative array.
     *
     * @return array
     */
    public function getTableParameters(): array
    {
        return [
            'column_id' => $this->columnId,
            'column_clang_id' => $this->columnClangId,
            'restriction_1_column' => $this->restriction1Column,
            'restriction_1_comparison_operator' => $this->restriction1ComparisonOperator,
            'restriction_1_value' => $this->restriction1Value,
            'restriction_2_logical_operator' => $this->restriction2LogicalOperator,
            'restriction_2_column' => $this->restriction2Column,
            'restriction_2_comparison_operator' => $this->restriction2ComparisonOperator,
            'restriction_2_value' => $this->restriction2Value,
            'restriction_3_logical_operator' => $this->restriction3LogicalOperator,
            'restriction_3_column' => $this->restriction3Column,
            'restriction_3_comparison_operator' => $this->restriction3ComparisonOperator,
            'restriction_3_value' => $this->restriction3Value,
            'column_segment_part_1' => $this->columnSegmentPart1,
            'column_segment_part_2_separator' => $this->columnSegmentPart2Separator,
            'column_segment_part_2' => $this->columnSegmentPart2,
            'column_segment_part_3_separator' => $this->columnSegmentPart3Separator,
            'column_segment_part_3' => $this->columnSegmentPart3,
            'relation_1_column' => $this->relation1Column,
            'relation_1_position' => $this->relation1Position,
            'relation_2_column' => $this->relation2Column,
            'relation_2_position' => $this->relation2Position,
            'relation_3_column' => $this->relation3Column,
            'relation_3_position' => $this->relation3Position,
            'append_user_paths' => $this->appendUserPaths,
            'append_structure_categories' => $this->appendStructureCategories,
            'column_seo_title' => $this->columnSeoTitle,
            'column_seo_description' => $this->columnSeoDescription,
            'column_seo_image' => $this->columnSeoImage,
            'sitemap_add' => $this->sitemapAdd,
            'sitemap_frequency' => $this->sitemapFrequency,
            'sitemap_priority' => $this->sitemapPriority,
            'column_sitemap_lastmod' => $this->columnSitemapLastmod,
        ];
    }

    /**
     * Sets the table name.
     *
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Returns the table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setColumnId(string $column_id): void
    {
        $this->columnId = $column_id;
    }

    public function getColumnId(): string
    {
        return $this->columnId;
    }

    public function setColumnClangId(string $column_clang_id): void
    {
        $this->columnClangId = $column_clang_id;
    }

    public function getColumnClangId(): string
    {
        return $this->columnClangId;
    }

    public function setRestriction1Column(string $restriction_1_column): void
    {
        $this->restriction1Column = $restriction_1_column;
    }

    public function getRestriction1Column(): string
    {
        return $this->restriction1Column;
    }

    public function setRestriction1ComparisonOperator(string $restriction_1_comparison_operator): void
    {
        $this->restriction1ComparisonOperator = $restriction_1_comparison_operator;
    }

    public function getRestriction1ComparisonOperator(): string
    {
        return $this->restriction1ComparisonOperator;
    }

    public function setRestriction1Value(string $restriction_1_value): void
    {
        $this->restriction1Value = $restriction_1_value;
    }

    public function getRestriction1Value(): string
    {
        return $this->restriction1Value;
    }

    public function setRestriction2LogicalOperator(string $restriction_2_logical_operator): void
    {
        $this->restriction2LogicalOperator = $restriction_2_logical_operator;
    }

    public function getRestriction2LogicalOperator(): string
    {
        return $this->restriction2LogicalOperator;
    }

    public function setRestriction2Column(string $restriction_2_column): void
    {
        $this->restriction2Column = $restriction_2_column;
    }

    public function getRestriction2Column(): string
    {
        return $this->restriction2Column;
    }

    public function setRestriction2ComparisonOperator(string $restriction_2_comparison_operator): void
    {
        $this->restriction2ComparisonOperator = $restriction_2_comparison_operator;
    }

    public function getRestriction2ComparisonOperator(): string
    {
        return $this->restriction2ComparisonOperator;
    }

    public function setRestriction2Value(string $restriction_2_value): void
    {
        $this->restriction2Value = $restriction_2_value;
    }

    public function getRestriction2Value(): string
    {
        return $this->restriction2Value;
    }

    public function setRestriction3LogicalOperator(string $restriction_3_logical_operator): void
    {
        $this->restriction3LogicalOperator = $restriction_3_logical_operator;
    }

    public function getRestriction3LogicalOperator(): string
    {
        return $this->restriction3LogicalOperator;
    }

    public function setRestriction3Column(string $restriction_3_column): void
    {
        $this->restriction3Column = $restriction_3_column;
    }

    public function getRestriction3Column(): string
    {
        return $this->restriction3Column;
    }

    public function setRestriction3ComparisonOperator(string $restriction_3_comparison_operator): void
    {
        $this->restriction3ComparisonOperator = $restriction_3_comparison_operator;
    }

    public function getRestriction3ComparisonOperator(): string
    {
        return $this->restriction3ComparisonOperator;
    }

    public function setRestriction3Value(string $restriction_3_value): void
    {
        $this->restriction3Value = $restriction_3_value;
    }

    public function getRestriction3Value(): string
    {
        return $this->restriction3Value;
    }
    
    public function setColumnSegmentPart1(string $column_segment_part_1): void
    {
        $this->columnSegmentPart1 = $column_segment_part_1;
    }

    public function getColumnSegmentPart1(): string
    {
        return $this->columnSegmentPart1;
    }

    public function setColumnSegmentPart2Separator(string $column_segment_part_2_separator): void
    {
        $this->columnSegmentPart2Separator = $column_segment_part_2_separator;
    }

    public function getColumnSegmentPart2Separator(): string
    {
        return $this->columnSegmentPart2Separator;
    }

    public function setColumnSegmentPart2(string $column_segment_part_2): void
    {
        $this->columnSegmentPart2 = $column_segment_part_2;
    }

    public function getColumnSegmentPart2(): string
    {
        return $this->columnSegmentPart2;
    }

    public function setColumnSegmentPart3Separator(string $column_segment_part_3_separator): void
    {
        $this->columnSegmentPart3Separator = $column_segment_part_3_separator;
    }

    public function getColumnSegmentPart3Separator(): string
    {
        return $this->columnSegmentPart3Separator;
    }

    public function setColumnSegmentPart3(string $column_segment_part_3): void
    {
        $this->columnSegmentPart3 = $column_segment_part_3;
    }

    public function getColumnSegmentPart3(): string
    {
        return $this->columnSegmentPart3;
    }

    public function setRelation1Column(string $relation_1_column): void
    {
        $this->relation1Column = $relation_1_column;
    }

    public function getRelation1Column(): string
    {
        return $this->relation1Column;
    }

    public function setRelation1Position(string $relation_1_position): void
    {
        $this->relation1Position = $relation_1_position;
    }

    public function getRelation1Position(): string
    {
        return $this->relation1Position;
    }

    public function setRelation2Column(string $relation_2_column): void
    {
        $this->relation2Column = $relation_2_column;
    }

    public function getRelation2Column(): string
    {
        return $this->relation2Column;
    }

    public function setRelation2Position(string $relation_2_position): void
    {
        $this->relation2Position = $relation_2_position;
    }

    public function getRelation2Position(): string
    {
        return $this->relation2Position;
    }

    public function setRelation3Column(string $relation_3_column): void
    {
        $this->relation3Column = $relation_3_column;
    }

    public function getRelation3Column(): string
    {
        return $this->relation3Column;
    }

    public function setRelation3Position(string $relation_3_position): void
    {
        $this->relation3Position = $relation_3_position;
    }

    public function getRelation3Position(): string
    {
        return $this->relation3Position;
    }

    public function setAppendUserPaths(string $append_user_paths): void
    {
        $this->appendUserPaths = $append_user_paths;
    }

    public function getAppendUserPaths(): string
    {
        return $this->appendUserPaths;
    }

    public function setAppendStructureCategories(string $append_structure_categories): void
    {
        $this->appendStructureCategories = $append_structure_categories;
    }

    public function getAppendStructureCategories(): string
    {
        return $this->appendStructureCategories;
    }

    public function setColumnSeoTitle(string $column_seo_title): void
    {
        $this->columnSeoTitle = $column_seo_title;
    }

    public function getColumnSeoTitle(): string
    {
        return $this->columnSeoTitle;
    }

    public function setColumnSeoDescription(string $column_seo_description): void
    {
        $this->columnSeoDescription = $column_seo_description;
    }

    public function getColumnSeoDescription(): string
    {
        return $this->columnSeoDescription;
    }

    public function setColumnSeoImage(string $column_seo_image): void
    {
        $this->columnSeoImage = $column_seo_image;
    }

    public function getColumnSeoImage(): string
    {
        return $this->columnSeoImage;
    }

    public function setSitemapAdd(string $sitemap_add): void
    {
        $this->sitemapAdd = $sitemap_add;
    }

    public function getSitemapAdd(): string
    {
        return $this->sitemapAdd;
    }

    public function setSitemapFrequency(string $sitemap_frequency): void
    {
        $this->sitemapFrequency = $sitemap_frequency;
    }

    public function getSitemapFrequency(): string
    {
        return $this->sitemapFrequency;
    }

    public function setSitemapPriority(string $sitemap_priority): void
    {
        $this->sitemapPriority = $sitemap_priority;
    }

    public function getSitemapPriority(): string
    {
        return $this->sitemapPriority;
    }

    public function setColumnSitemapLastmod(string $column_sitemap_lastmod): void
    {
        $this->columnSitemapLastmod = $column_sitemap_lastmod;
    }

    public function getColumnSitemapLastmod(): string
    {
        return $this->columnSitemapLastmod;
    }
}
