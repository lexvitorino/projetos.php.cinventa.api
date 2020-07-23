<?php

namespace Models;

use Exception;
use \Core\Model;
use \Models\File;

class Subscriber extends Model
{
    private $result;
    private $error;

    public function __construct()
    {
        parent::__construct();

        $this->result = null;
        $this->error = array(
            'hasError' => false,
            'errors' => array()
        );
    }

    public function get()
    {
        try {
            $sql = "SELECT * 
                    FROM   subscribers";

            $sql = $this->db->prepare($sql);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return $this->result = $sql->fetchAll();
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getById(int $id): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   subscribers
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result = $sql->fetch();

                if (!is_null($this->result['avatar_id']) && intVal($this->result['avatar_id']) > 0) {
                    $file = new File($this->subscriberId);
                    if ($file->getById($this->result['avatar_id'])) {
                        $this->result['avatar'] = array(
                            'url' => $file->single()['url'],
                        );
                    }
                }

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

            if (!is_null($data['avatar_id']) && $data['avatar_id'] > 0) {
                $file = new File($this->subscriberId);
                if (!$file->delete($data['avatar_id'], $data['avatar'])) {
                    return false;
                }
            }

            $sql = "DELETE FROM users
                    WHERE  subscriber_Id = :subscriberId
                    AND    id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':id', $id);
            $sql->execute();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, $data): bool
    {
        try {
            if (!$this->isPermited($data)) {
                return false;
            }

            if (!$this->isValid($data)) {
                return false;
            }

            if ($data['Files']) {
                if ($data['Files']['name'] != $data['avatar']['name']) {
                    $file = new File($this->subscriberId);
                    if (!$file->delete($data['avatar_id'], $data['avatar'])) {
                        return false;
                    }
                    if ($file->create($data['Files'])) {
                        $data['avatar_id'] = $file->single()['id'];
                    }
                }
            }

            $sql = "UPDATE users
                    SET    name = :name, 
                           email = :email, 
                           permission = :permission,
                           avatar_id = :avatar_id,
                           updated_at = NOW()
                    WHERE  subscriber_Id = :subscriberId
                    AND    id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':id', $id);
            $sql->bindValue(':name', $data['name']);
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':permission', $data['permission']);
            $sql->bindValue(':avatar_id', $data['avatar_id']);
            $sql->execute();

            return $this->getById($id);
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

            if ($data['Files']) {
                $file = new File($this->subscriberId);
                if ($file->create($data['Files'])) {
                    $data['avatar_id'] = $file->single()['id'];
                }
            }

            $sql = "INSERT INTO users
                    (subscriber_Id, name, email, permission, avatar_id, created_at, updated_at)
                    VALUES 
                    (:subscriberId, :name, :email, :permission, :avatar_id, NOW(), NOW() )";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':name', $data['name']);
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':permission', $data['permission']);
            $sql->bindValue(':avatar_id', $data['avatar_id']);
            $sql->execute();

            $id = $this->db->lastInsertId();

            return $this->getById($id);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function toList()
    {
        return $this->result;
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
        if (empty($data['email'])) {
            $this->error['errors'][] = 'E-mail não informado';
        }

        if (empty($data['name'])) {
            $this->error['errors'][] = 'Name não informado';
        }

        $this->error['hasError'] = count($this->error['errors']) > 0;
        return !$this->error['hasError'];
    }
}
