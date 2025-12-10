<?php
/**
 * Database Connection Manager
 * Haklai SaaS - Church Management System
 * 
 * Gerencia conexões PDO com MySQL
 * Singleton pattern para garantir uma única instância
 * 
 * @author Jefter Ruthes
 * @version 1.0.0
 */

namespace Haklai\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;
    
    /**
     * Construtor privado (Singleton)
     */
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }
    
    /**
     * Obtém instância única (Singleton)
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Conecta ao banco de dados
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
            
        } catch (PDOException $e) {
            $this->logError('Database connection failed: ' . $e->getMessage());
            throw new Exception('Não foi possível conectar ao banco de dados.');
        }
    }
    
    /**
     * Obtém conexão PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Executa query SELECT e retorna todos os resultados
     * 
     * @param string $sql Query SQL com placeholders
     * @param array $params Parâmetros para prepared statement
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Erro ao executar consulta.');
        }
    }
    
    /**
     * Executa query SELECT e retorna um único resultado
     * 
     * @param string $sql
     * @param array $params
     * @return object|null
     */
    public function queryOne(string $sql, array $params = []): ?object
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            $this->logError('QueryOne failed: ' . $e->getMessage());
            throw new Exception('Erro ao executar consulta.');
        }
    }
    
    /**
     * Executa INSERT e retorna o ID inserido
     * 
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insert(string $table, array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($data);
            
            return (int) $this->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            $this->logError('Insert failed: ' . $e->getMessage() . ' | Table: ' . $table);
            throw new Exception('Erro ao inserir registro.');
        }
    }
    
    /**
     * Executa UPDATE
     * 
     * @param string $table
     * @param array $data
     * @param string $where Condição WHERE (ex: "id = :id")
     * @param array $whereParams Parâmetros do WHERE
     * @return int Número de linhas afetadas
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        try {
            $set = [];
            foreach (array_keys($data) as $key) {
                $set[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $set);
            
            $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
            
            $params = array_merge($data, $whereParams);
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError('Update failed: ' . $e->getMessage());
            throw new Exception('Erro ao atualizar registro.');
        }
    }
    
    /**
     * Executa DELETE
     * 
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        try {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError('Delete failed: ' . $e->getMessage());
            throw new Exception('Erro ao deletar registro.');
        }
    }
    
    /**
     * Inicia transação
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Confirma transação
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }
    
    /**
     * Reverte transação
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }
    
    /**
     * Registra erro em log
     */
    private function logError(string $message): void
    {
        $logPath = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $logFile = $logPath . 'database-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Previne clonagem (Singleton)
     */
    private function __clone() {}
    
    /**
     * Previne unserialization (Singleton)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
