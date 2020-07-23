<?php

namespace Models;

use \Core\Model;
use Exception;

class File extends Model
{
    private $subscriberId;
    private $result;
    private $error;

    public function __construct(int $subscriberId)
    {
        parent::__construct();

        $this->subscriberId = $subscriberId;
        $this->result = null;
        $this->error = array(
            'hasError' => false,
            'errors' => array()
        );
    }

    public function getById(int $id): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   files
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();


            if ($sql->rowCount() > 0) {
                $this->result = $sql->fetch();
                $this->result['url'] = BASE_URL . "/{$this->result['path']}";
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
        try {
            if (!$this->isPermited($data)) {
                return false;
            }

            $sql = "DELETE FROM files
                    WHERE  subscriber_Id = :subscriberId
                    AND    id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':id', $id);
            $sql->execute();

            unlink($data['url']);

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function create($data): bool
    {
        try {
            if (!$this->isValid($data)) {
                return false;
            }

            $sql = "INSERT INTO files
                    (subscriber_Id, name, path, created_at, updated_at)
                    VALUES 
                    (:subscriberId, :name, :path, NOW(), NOW() )";

            preg_match("/\.(gif|bmp|png|jpg|jpeg){1}$/i", $data["name"], $ext);

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':name', $data['name']);
            $sql->bindValue(':path', md5(uniqid(time())) . "." . $ext);
            $sql->execute();

            $id = $this->db->lastInsertId();

            return $this->getById($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function single()
    {
        return $this->result;
    }

    public function error()
    {
        return $this->error;
    }

    private function isPermited($data): bool
    {
        if (intVal($data['subscriber_id']) != $this->subscriberId) {
            $this->error['hasError'] = true;
            $this->error['errors'][] = 'Violação de autenticidade';
            return false;
        }
        return true;
    }

    private function isValid($data): bool
    {
        if (empty($data['name'])) {
            $this->error['errors'][] = 'Arquivo não informado';
        }

        $this->error['hasError'] = count($this->error['errors']) > 0;
        return !$this->error['hasError'];
    }
}
