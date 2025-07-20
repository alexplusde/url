<?php

namespace Url;

class ProfileService extends \rex_sql
{

    /**
     * The name of the table used for profiles.
     * @var string
     */
    private $tableName = 'url_generator_profile';

    private int $id;
    private string $namespace;
    private int $article_id;
    private int $clang_id;
    private int $ep_pre_save_called;
    private string $table_name;
    private array $table_parameters;
    private string $relation_1_table_name;
    private array $relation_1_table_parameters;
    private string $relation_2_table_name;
    private array $relation_2_table_parameters;
    private string $relation_3_table_name;
    private array $relation_3_table_parameters;
    private string $createdate;
    private string $createuser;
    private string $updatedate;
    private string $updateuser;

    /**
     * Returns all profiles.
     *
     * @return array
     */
    public function getAllProfiles(): array
    {
        $query = 'SELECT * FROM ' . \rex::getTable('url_generator_profile') . ' ORDER BY `namespace`';
        return $this->getArray($query);
    }

    /**
     * Returns a profile by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getProfileById(int $id): ?array
    {
        $query = 'SELECT * FROM ' . \rex::getTable('url_generator_profile') . ' WHERE id = :id';
        $this->setQuery($query, ['id' => $id]);
        return $this->getRow();
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setArticleId(int $article_id): void
    {
        $this->article_id = $article_id;
    }

    public function getArticleId(): int
    {
        return $this->article_id;
    }

    public function setClangId(int $clang_id): void
    {
        $this->clang_id = $clang_id;
    }

    public function getClangId(): int
    {
        return $this->clang_id;
    }

    public function setEpPreSaveCalled(int $ep_pre_save_called): void
    {
        $this->ep_pre_save_called = $ep_pre_save_called;
    }

    public function getEpPreSaveCalled(): int
    {
        return $this->ep_pre_save_called;
    }

    public function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function setTableParameters(array $table_parameters): void
    {
        $this->table_parameters = $table_parameters;
    }

    public function getTableParameters(): array
    {
        return $this->table_parameters;
    }

    public function setRelation1TableName(string $relation_1_table_name): void
    {
        $this->relation_1_table_name = $relation_1_table_name;
    }

    public function getRelation1TableName(): string
    {
        return $this->relation_1_table_name;
    }

    public function setRelation1TableParameters(array $relation_1_table_parameters): void
    {
        $this->relation_1_table_parameters = $relation_1_table_parameters;
    }

    public function getRelation1TableParameters(): array
    {
        return $this->relation_1_table_parameters;
    }

    public function setRelation2TableName(string $relation_2_table_name): void
    {
        $this->relation_2_table_name = $relation_2_table_name;
    }

    public function getRelation2TableName(): string
    {
        return $this->relation_2_table_name;
    }

    public function setRelation2TableParameters(array $relation_2_table_parameters): void
    {
        $this->relation_2_table_parameters = $relation_2_table_parameters;
    }
    public function getRelation2TableParameters(): array
    {
        return $this->relation_2_table_parameters;
    }

    public function setRelation3TableName(string $relation_3_table_name): void
    {
        $this->relation_3_table_name = $relation_3_table_name;
    }

    public function getRelation3TableName(): string
    {
        return $this->relation_3_table_name;
    }

    public function setRelation3TableParameters(array $relation_3_table_parameters): void
    {
        $this->relation_3_table_parameters = $relation_3_table_parameters;
    }

    public function getRelation3TableParameters(): array
    {
        return $this->relation_3_table_parameters;
    }

    public function setCreatedate(string $createdate): void
    {
        $this->createdate = $createdate;
    }

    public function getCreatedate(): string
    {
        return $this->createdate;
    }

    public function setCreateuser(string $createuser): void
    {
        $this->createuser = $createuser;
    }

    public function getCreateuser(): string
    {
        return $this->createuser;
    }

    public function setUpdatedate(string $updatedate): void
    {
        $this->updatedate = $updatedate;
    }

    public function getUpdatedate(): string
    {
        return $this->updatedate;
    }

    public function setUpdateuser(string $updateuser): void
    {
        $this->updateuser = $updateuser;
    }

    public function getUpdateuser(): string
    {
        return $this->updateuser;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function saveProfile(array $data): bool
    {
        $this->setTable(\rex::getTable($this->tableName));
        $this->setValue('namespace', $data['namespace']);
        $this->setValue('article_id', $data['article_id']);
        $this->setValue('clang_id', $data['clang_id']);
        $this->setValue('ep_pre_save_called', $data['ep_pre_save_called']);
        $this->setValue('table_name', $data['table_name']);
        $this->setValue('table_parameters', json_encode($data['table_parameters']));
        $this->setValue('relation_1_table_name', $data['relation_1_table_name']);
        $this->setValue('relation_1_table_parameters', json_encode($data['relation_1_table_parameters']));
        $this->setValue('relation_2_table_name', $data['relation_2_table_name']);
        $this->setValue('relation_2_table_parameters', json_encode($data['relation_2_table_parameters']));
        $this->setValue('relation_3_table_name', $data['relation_3_table_name']);
        $this->setValue('relation_3_table_parameters', json_encode($data['relation_3_table_parameters']));
        $this->setValue('createdate', date('Y-m-d H:i:s'));
        $this->setValue('createuser', \rex::getUser()->getLogin());
        $this->insertOrUpdate();
        return $this->hasError() ? false : true;
    }



}
