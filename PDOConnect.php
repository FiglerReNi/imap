<?php

class PDOConnect
{
    private $pdo;

    /**
     * PDOConnect constructor.
     */
    public function __construct($db)
    {
        $host = constant($db . '_DATABASE_HOST');
        $username = constant($db . '_DATABASE_USERNAME');
        $password = constant($db . '_DATABASE_PASSWORD');
        $dbname = constant($db . '_DATABASE_NAME');

        try {
            $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host;
            $this->pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            throw new Exception('Adatbázis kapcsolódási hiba' . $e->getMessage());
        }
    }

    public function prepare($query)
    {
        return $this->pdo->prepare($query);
    }

    public function lastInsertId(){
        return $this->pdo->lastInsertId();
    }

    public function insert($table, $items)
    {
        $query = "INSERT INTO " . $table . " (" . implode(', ', array_keys($items)) . ") 
                  VALUES (:" . implode(', :', array_keys($items)) . ")";
        $this->executeStatement($query, $items);
    }

    public function update($table, $sets, $items)
    {
        $query = "UPDATE " . $table . " SET " . implode(',', $this->transformation($sets)) .
            " WHERE " . implode(' AND ', $this->transformation($items));
        $this->executeStatement($query, $sets, $items);
    }

    public function select($query, $items)
    {
        $stmt = $this->executeStatement($query, $items);
        try {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Eredmény visszaadása sikertelen' . $e->getMessage());
        }
    }

    public function executeStatement($query, $sets = array(), $items = array())
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $this->bindValueFromArray($stmt, $sets);
            $this->bindValueFromArray($stmt, $items);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Lekérdezés hiba' . $e->getMessage());
        }
    }

    private function transformation($datas): array
    {
        $dataArray = array();
        foreach (array_keys($datas) as $data) {
            $dataArray[] = $data . " =:" . $data;
        }
        return $dataArray;
    }


    private function bindValueFromArray($stmt, $datas): void
    {
        if (!empty($datas)) {
            foreach ($datas as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
    }
}