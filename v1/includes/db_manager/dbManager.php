<?php

/**
 * Class DBManager
 */
class DBManager
{
    private $host;
    private $dbName;
    private $user;
    private $password;
    private $pdo;

    public $dbManager;
    public $entityManager;

    /**
     * DBManager constructor.
     */
    public function __construct()
    {
        require_once dirname(dirname(dirname(__DIR__))) . '/libs/orm/NotORM.php';

        $this->host = "localhost";
        $this->dbName = "api_application";
        $this->user = "root";
        $this->password = "root";

        $dsn = "mysql:dbname=$this->dbName;host=$this->host";
        $this->pdo = new PDO($dsn, $this->user, $this->password);

        $this->entityManager = new NotORM($this->pdo); //$this->getEntityManager();
        $this->entityManager->debug = true;
    }

    /**
     * @return NotORM
     */
    public function getEntityManager()
    {
        return new NotORM($this->pdo);
    }

}