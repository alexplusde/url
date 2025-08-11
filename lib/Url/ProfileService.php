<?php

namespace Url;

class ProfileService extends \rex_sql
{

    /**
     * The name of the table used for profiles.
     * @var string
     */
    private $tableName = 'url_generator_profile';

    /**
     * Stores validation errors.
     * @var array
     */
    private array $validationErrors = [];

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

    /**
     * Creates a new profile.
     *
     * @param array $data Profile data
     * @return int|false The ID of the created profile or false on error
     */
    public function createProfile(array $data)
    {
        if (!$this->validateProfileData($data)) {
            return false;
        }

        $this->setTable(\rex::getTable($this->tableName));
        $this->setValues($this->prepareProfileData($data, true));
        
        if ($this->insert()) {
            \Url\Cache::deleteProfiles();
            return $this->getLastId();
        }
        
        return false;
    }

    /**
     * Updates an existing profile.
     *
     * @param int $id Profile ID
     * @param array $data Profile data
     * @return bool Success
     */
    public function updateProfile(int $id, array $data): bool
    {
        if (!$this->validateProfileData($data, $id)) {
            return false;
        }

        $this->setTable(\rex::getTable($this->tableName));
        $this->setWhere(['id' => $id]);
        $this->setValues($this->prepareProfileData($data, false));
        
        if ($this->update()) {
            \Url\Cache::deleteProfiles();
            return true;
        }
        
        return false;
    }

    /**
     * Deletes a profile and its associated URLs.
     *
     * @param int $id Profile ID
     * @return bool Success
     */
    public function deleteProfile(int $id): bool
    {
        $profile = Profile::get($id);
        if ($profile !== null) {
            $profile->deleteUrls();
        }

        $this->setTable(\rex::getTable($this->tableName));
        $this->setWhere(['id' => $id]);
        
        if ($this->delete()) {
            \Url\Cache::deleteProfiles();
            return true;
        }
        
        return false;
    }

    /**
     * Validates profile data.
     *
     * @param array $data Profile data
     * @param int|null $excludeId ID to exclude from duplicate check (for updates)
     * @return bool Validation result
     */
    private function validateProfileData(array $data, ?int $excludeId = null): bool
    {
        $this->validationErrors = [];
        
        // Required fields
        if (empty($data['namespace']) || !preg_match('/^[a-z0-9_-]*$/', $data['namespace'])) {
            $this->validationErrors[] = 'Namespace is required and must contain only lowercase letters, numbers, underscores and hyphens';
            return false;
        }

        if (empty($data['article_id']) || !is_numeric($data['article_id']) || $data['article_id'] < 1) {
            $this->validationErrors[] = 'Article ID is required and must be a positive integer';
            return false;
        }

        if (empty($data['table_name'])) {
            $this->validationErrors[] = 'Table name is required';
            return false;
        }

        // Check for duplicate namespace within same article/clang context
        $duplicateCheck = \rex_sql::factory();
        $query = 'SELECT id FROM ' . \rex::getTable($this->tableName) . ' WHERE namespace = ? AND article_id = ? AND clang_id = ?';
        $params = [$data['namespace'], $data['article_id'], $data['clang_id'] ?? 1];
        
        if ($excludeId !== null) {
            $query .= ' AND id != ?';
            $params[] = $excludeId;
        }
        
        $duplicateCheck->setQuery($query, $params);
        if ($duplicateCheck->getRows() > 0) {
            $this->validationErrors[] = 'A profile with this namespace already exists for the specified article and language';
            return false;
        }

        return true;
    }

    /**
     * Prepares profile data for database insertion/update.
     *
     * @param array $data Input data
     * @param bool $isCreate Whether this is a create operation
     * @return array Prepared data
     */
    private function prepareProfileData(array $data, bool $isCreate): array
    {
        $preparedData = [
            'namespace' => $data['namespace'],
            'article_id' => (int) $data['article_id'],
            'clang_id' => $data['clang_id'] ?? 1,
            'ep_pre_save_called' => $data['ep_pre_save_called'] ?? 0,
            'table_name' => $data['table_name'],
            'table_parameters' => is_array($data['table_parameters'] ?? null) ? json_encode($data['table_parameters']) : ($data['table_parameters'] ?? ''),
            'relation_1_table_name' => $data['relation_1_table_name'] ?? '',
            'relation_1_table_parameters' => is_array($data['relation_1_table_parameters'] ?? null) ? json_encode($data['relation_1_table_parameters']) : ($data['relation_1_table_parameters'] ?? ''),
            'relation_2_table_name' => $data['relation_2_table_name'] ?? '',
            'relation_2_table_parameters' => is_array($data['relation_2_table_parameters'] ?? null) ? json_encode($data['relation_2_table_parameters']) : ($data['relation_2_table_parameters'] ?? ''),
            'relation_3_table_name' => $data['relation_3_table_name'] ?? '',
            'relation_3_table_parameters' => is_array($data['relation_3_table_parameters'] ?? null) ? json_encode($data['relation_3_table_parameters']) : ($data['relation_3_table_parameters'] ?? ''),
        ];

        // Set timestamps and user info
        $currentUser = \rex::getUser();
        $currentTime = date('Y-m-d H:i:s');
        
        if ($isCreate) {
            $preparedData['createdate'] = $currentTime;
            $preparedData['createuser'] = $currentUser ? $currentUser->getLogin() : '';
        }
        
        $preparedData['updatedate'] = $currentTime;
        $preparedData['updateuser'] = $currentUser ? $currentUser->getLogin() : '';

        return $preparedData;
    }

    /**
     * Legacy method for backward compatibility.
     * Use createProfile() for new profiles or updateProfile() for existing ones.
     *
     * @deprecated Use createProfile() or updateProfile() instead
     * @param array $data Profile data
     * @return bool Success
     */
    public function saveProfile(array $data): bool
    {
        if (isset($data['id']) && $data['id'] > 0) {
            return $this->updateProfile($data['id'], $data);
        } else {
            return (bool) $this->createProfile($data);
        }
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Get last validation error message.
     *
     * @return string|null
     */
    public function getLastValidationError(): ?string
    {
        return !empty($this->validationErrors) ? end($this->validationErrors) : null;
    }



}
