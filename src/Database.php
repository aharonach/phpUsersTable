<?php

namespace Aharon;

use Exception;
use PDO;
use PDOException;

class Database
{
    /**
     * @var string DB Host.
     */
    private string $host;

    /**
     * @var string Database Name.
     */
    private string $db_name;

    /**
     * @var PDO PDO Instance.
     */
    private PDO $pdo;

    /**
     * Table name.
     *
     * @var string
     */
    private string $table_name = 'users';

    /**
     * Table columns.
     *
     * @var string[]
     */
    private array $columns = array(
        'id'          => 'INT NOT NULL AUTO_INCREMENT',
        'name'        => 'VARCHAR(50)',
        'age'         => 'INT(3)',
        'country'     => 'VARCHAR(30)',
        'email'       => 'VARCHAR(50)',
        'profile_pic' => 'VARCHAR(150)',
    );

    /**
     * Table keys.
     *
     * @var string[]
     */
    private array $keys = [
        'id'
    ];

    /**
     * Table columns for indexing.
     *
     * @var string[]
     */
    private array $indexes = [
        'age',
        'country'
    ];

    /**
     * Hold column names for INSERT statement.
     * @var array
     */
    private array $columns_for_insert;

    /**
     * Constructor.
     *
     * @param string $host
     * @param string $db_name
     * @param string $user
     * @param string $password
     *
     * @throws PDOException
     */
    public function __construct(
        string $host,
        string $db_name,
        string $user,
        string $password
    ) {
        $this->host               = $host;
        $this->db_name            = $db_name;
        $this->pdo                = new PDO("mysql:host=$this->host;dbname=$this->db_name", $user, $password);
        $this->columns_for_insert = $this->prepareColumnsForInsert();
    }

    /**
     * Migrate database.
     *
     * @return void
     */
    public function migrate(): void
    {
        $this->createTable();
    }

    /**
     * Create database table.
     *
     * @return void
     */
    private function createTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS $this->table_name (";

        foreach ($this->columns as $column_id => $column_type) {
            $query .= "$column_id $column_type,";
        }

        foreach ($this->keys as $key) {
            $query .= "PRIMARY KEY ($key),";
        }

        foreach ($this->indexes as $index) {
            $query .= "KEY $index ($index),";
        }

        $query = rtrim($query, ',');
        $query .= ") DEFAULT CHARSET=utf8;";
        $this->pdo->exec( $query );
    }

    /**
     * Insert multiple users to the database.
     *
     * @throws Exception
     */
    public function insertUsers(array $users)
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($users as $user) {
                $this->insertUser($user);
            }

            $this->pdo->commit();
        } catch( Exception $exception ) {
            $this->pdo->rollback();
            throw $exception;
        }
    }

    /**
     * Insert a single user to the database.
     *
     * @param array $user
     * @return void
     */
    public function insertUser(array $user): void
    {
        $user  = $this->validateUser($user);
        $query = <<<EOA
INSERT INTO $this->table_name ( {$this->columns_for_insert['names']} ) VALUES 
( {$this->columns_for_insert['placeholders']} )
EOA;

        $this->pdo
            ->prepare($query)
            ->execute($user);
    }

    /**
     * Get users from database with basic fuzzy text filter.
     *
     * @param array $filters
     * @param string $operator
     * @param int $limit
     * @return array
     */
    public function getUsers(array $filters = [], string $operator = 'AND', int $limit = -1): array
    {
        $query        = "SELECT * FROM $this->table_name ";
        $where_clause = [];
        $operator     = $operator === 'AND' ? 'AND' : 'OR';

        foreach ($filters as $column => $value) {
            if (!array_key_exists($column, $this->columns)) {
                continue;
            }

            $where_clause[] = " $column LIKE ? ";
        }

        $where_clause = implode(" $operator ", $where_clause );

        if ($where_clause) {
            $query .= " WHERE $where_clause ";
        }

        if ( $limit > 0 ) {
            $query .= " LIMIT $limit";
        }

        $query    .= ';';
        $statement = $this->pdo->prepare($query);
        $statement->execute(array_values($filters));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate user columns before insert.
     *
     * @param array $user
     * @return array validated user.
     */
    private function validateUser(array $user): array
    {
        $validated_user = [];

        foreach (array_keys($this->columns) as $column_id) {
            if ($column_id === 'id') {
                continue;
            }

            $validated_user[$column_id] = $user[$column_id] ?? '';
        }

        return $validated_user;
    }

    /**
     * Prepare user columns for insert to DB.
     *
     * @return array
     */
    private function prepareColumnsForInsert(): array {
        $columns = $this->columns;
        unset($columns['id']);
        $columns = array_keys($columns);

        // Prepare placeholders for query statement.
        $column_placeholders = implode(',', array_map(fn( $column ) => ":" . $column, $columns));
        $column_names        = implode(',', $columns);

        return [
            'names'        => $column_names,
            'placeholders' => $column_placeholders,
        ];
    }
}