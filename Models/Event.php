<?php

namespace Models;

use Exception;
use \Core\Model;
use PDO;

class Event extends Model
{
    private $subscriberId;
    private $result;

    public function __construct(int $subscriberId)
    {
        parent::__construct();

        $this->subscriberId = $subscriberId;
        $this->result = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
            'data' => array()
        );
    }

    public function get(): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   events
                    WHERE  ativo = 1
                    AND    ativo_as <= NOW()
                    ORDER BY data";

            $sql = $this->db->prepare($sql);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetchAll(PDO::FETCH_ASSOC);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getById(int $id): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   events
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetch(PDO::FETCH_ASSOC);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getByChave($chave, $data): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   events
                    WHERE  chave = :chave
                    AND    DATE(data) = DATE(:data)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':chave', $chave);
            $sql->bindValue(':data', $data);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetch(PDO::FETCH_ASSOC);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id, $data): bool
    {
        return false;
    }

    public function update(int $id, $data): bool
    {
        return false;
    }

    public function create($data): bool
    {
        try {
            if (!$this->isValid($data)) {
                return false;
            }

            $sql = "INSERT INTO events
                    (ativo, data, chave, descricao, simples, dupla)
                    VALUES 
                    (1, :data, :chave, :descricao, :simples, :dupla)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':chave', $data['chave']);
            $sql->bindValue(':descricao', $data['descricao']);
            $sql->bindValue(':simples', ($data['simples'] ?? 0));
            $sql->bindValue(':dupla', ($data['dupla'] ?? 0));
            $sql->execute();

            $id = $this->db->lastInsertId();

            if (!$this->getById($id)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getResult()
    {
        return $this->result;
    }

    public function error()
    {
        return $this->result['message'];
    }

    private function isValid($data): bool
    {
        if (empty($data['data'])) {
            $this->result['message']['errors'][] = 'Data não informado';
        }

        if (empty($data['chave'])) {
            $this->result['message']['errors'][] = 'Chave não informado';
        }

        if (empty($data['descricao'])) {
            $this->result['message']['errors'][] = 'Descricao não informada';
        }

        if (empty($data['simples'])) {
            $this->result['message']['errors'][] = 'Oupação simples não informado';
        }

        if (empty($data['dupla'])) {
            $this->result['message']['errors'][] = 'Oupação dupla não informado';
        }

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }
}
