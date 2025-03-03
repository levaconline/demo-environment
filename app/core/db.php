<?php

/**
 * Work with database if needed (Some sections no need DB).
 * So, connection will be establish obly calling initDb().
 * Esoablish connection and provides basic sql query executions.
 */
class Db extends Root
{
    /**
     * MySQL connection
     * @var private PDO $mysql_conn
     */
    private $mysql_conn;

    /**
     * Errors collector
     * 
     * @var array $dbErrors
     */
    private array $dbErrors = [];

    /**
     * DB connection variables.
     */
    protected $dbPrefix = '';
    protected $tablePrefix = '';
    private $host = '';
    private $user = '';
    private $password = '';
    private $database = '';

    /**
     * Where to put log file.
     * @var string  $dbLogFile - file name.
     */
    private $dbLogFile = 'db.log';

    /**
     * @var int $defaultLimit (for paginator)
     */
    private int $defaultLimit = 100;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Errors getter
     * @return array
     */
    public function getErrors(): array
    {
        return $this->dbErrors;
    }

    /**
     * Establish DB connection if needed.
     * In case of no need db connection, skop calling this method.
     * 
     * @param string $dbt 
     * 
     * @return mixed
     */
    protected function initDb(string $dbt = 'mysql'): mixed
    {
        switch ($dbt) {
            case 'pg':
                // TODO: No need for now.
                break;

            case 'sqlite':
                // TODO: No need for now.
                break;

            default:
                $this->parseCustomEnv();
                $this->connectMysql();
                return $this->checkMysqlConnection();
        }
        return true;
    }

    /************** MYSQL DB CONNECTION (PDO) ***************/

    /**
     * Try to establish db connection or die.
     * @return void
     */
    private function connectMysql(): void
    {
        $this->getParamsFromConfig();
        try {
            $this->mysql_conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbPrefix . $this->database . ';charset=utf8', $this->dbPrefix . $this->user, $this->password);
        } catch (\PDOException $e) {
            $this->dbErrors['msg'] = "Error connecting to mysql:" . $e->getMessage();
        }

        if (empty($this->mysql_conn)) {
            $this->logMe($this->dbLogFile, $this->dbErrors['msg']);
            die();
        }

        $this->mysql_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Read DB connection params from config and store it in properties.
     * @return void
     */
    private function getParamsFromConfig(): void
    {
        //$this->parseCustomEnv();

        if (empty($this->envData)) {
            $this->logMe($this->dbLogFile, ".env file not parsed.");
            die();
        }
        $this->logMe($this->dbLogFile, var_export($this->envData, true));

        $this->host = $this->envData['MYSQL_DB_HOST'];
        $this->user = $this->envData['MYSQL_DB_USER_NAME'];
        $this->password = $this->envData['MYSQL_DB_PASSWORD'];
        $this->database = $this->envData['MYSQL_DB_NAME'];
        $this->dbPrefix = $this->envData['MYSQL_DB_NAME_PREFIX'];
        $this->tablePrefix = '';
    }

    /**
     * Check of connection is realy exist.
     * @return bool
     */
    private function checkMysqlConnection(): bool
    {
        return (bool)!$this->mysql_conn ? false : true;
    }
    /************** /MYSQL DB CONNECTION ***************/



    /************** MYSQL DB ***************/

    /**
     * Please, take care about LIMIT or/and WHERE clause to avoud 
     * "cnockdown" mysql server in case of too big number of record pulled in memory.
     */
    protected function getBySql(string $sql, $values = [])
    {
        $pdo = $this->mysql_conn->prepare($sql);
        $pdo->execute($values);
        return $pdo->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert record.
     * 
     * @param string $sql
     * @param array $values - for PDO
     * 
     * @return bool
     */
    protected function insertBySql(string $sql, $values = []): bool
    {
        $pdo = $this->mysql_conn->prepare($sql);
        return $pdo->execute($values);
    }

    /**
     * Please, take care about WHERE to prevent accidentaly changes in all records. 
     *
     * @param string $sql
     * @param array $values - for PDO
     * 
     * @return bool
     */
    protected function updateBySql(string $sql, $values = []): bool
    {
        if (strpos(strtolower($sql), ' where ') === false) {
            $this->dbErrors['msg'] = "Danger! No WHERE in: " . $sql . "\n";
            return false;
        }
        return $this->mysql_conn->prepare($sql)->execute($values);
    }

    /**
     * Delete record(s) by posted sql qurey.
     * Please, take care about LIMIT or/and WHERE clause to avoud 
     * "cnockdoen" mysql server in case of too big number of record pulled in mempry.
     * 
     * @param string $sql
     * @param array $values - for PDO
     * 
     * @return bool
     */
    protected function deleteBySql(string $sql, $values = []): bool
    {
        if (strpos(strtolower($sql), ' where ') === false) {
            $this->dbErrors['msg'] = "Danger! No WHERE in: " . $sql . "\n";
            return false;
        }
        return $this->mysql_conn->prepare($sql)->execute($values);
    }

    /************** /MYSQL DB ***************/


    /************** Paginator ***************/
    /**
     * Paginator
     * @param int totalCount - total items count.
     */
    protected function paginator(int $totalItemsCount = 0): array
    {
        // Set defaolts;
        $data = [
            'pages_count' => 0,
            'current_page' => 0,
            'offset' => 0,
            'sqlPart' => '',

        ];

        // There is no any record found. No need to go further with calculations.
        if ($totalItemsCount < 1) {
            return $data;
        }

        // Ordering map. (number instead sting is safer iw does not use routes.) 
        $ordermap = [0 => ' ASC', 1 => 'DESC'];

        $limit = (int)filter_input(INPUT_GET, 'nu'); // Limit per page
        $start = (int)filter_input(INPUT_GET, 'of'); // Offset (starting row)
        $orderBy = (string)filter_input(INPUT_GET, 'col'); // Collumn by number.
        $order = (int)filter_input(INPUT_GET, 'o'); // Order: 1 asc, 2 desc.
        $currentPage = (int)filter_input(INPUT_GET, 'p'); // Actiall page if we ise gages as refferer.

        // Offset in SQL query (begin from record number)
        if (!empty($start)) {
            $start = 0;
        }

        // Limit in SQL query
        if (empty($limit)) {
            $limit = $this->defaultLimit;
        }

        // If we use page instead offset, offset must be recalculated.
        if (!empty($currentPage)) {
            $currentPage = 1;
        }

        if (!isset($order) || ($order < 0 || $order > 1)) {
            $order = 1;
        }

        if (!isset($orderBy) || empty($orderBy)) {
            $orderBy = 'id';
        }

        ### Calculations ###

        // How many pages we have?
        $numberOfPages = $this->numberOfpages($totalItemsCount, $limit);
        $offset = $this->getOffset($currentPage, $limit);


        $ordering = ' ORDER BY ' . $orderBy . ' '  . $ordermap[$order];
        $limiting = ' LIMIT ' . $start . ', ' . $limit;

        $sql = $ordering . $limiting;

        $data = [
            'pages_count' => $numberOfPages,
            'current_page' => $currentPage,
            'offset' => $offset,
            'sqlPart' => $sql,
        ];

        return $data;
    }

    /**
     * Calculate number of pages.
     */
    private function numberOfpages(int $totalCount, int $limit): int
    {
        // Just simplify things.
        if ($limit >  $totalCount) {
            $limit = $totalCount;
        }

        // Calculate...
        $rawNumberOfPages = $totalCount / $limit;
        $fullpages = floor($rawNumberOfPages);
        // If calculated number of pages has decimals, pages are fullpages + 1
        return ($fullpages == ceil($rawNumberOfPages)) ? (int)$fullpages : (int)$fullpages + 1;
    }

    /**
     * Calculate offser (starting record for sql)
     * 
     * @param int $currentPage
     * @param int $limit
     * 
     * @return integer
     */
    private function getOffset(int $currentPage, int $limit): int
    {
        return $currentPage *  $limit - $limit;
    }
    /************** /Paginator ***************/
}
