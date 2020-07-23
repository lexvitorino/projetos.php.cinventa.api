<?php

namespace Models;

use Exception;
use \Core\Model;
use \Models\File;

class User extends Model
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
            $url = BASE_URL . "/";
            $sql = "SELECT u.*, CONCAT('{$url}', f.path) as url
                    FROM   users u
                        LEFT JOIN files f on f.id = u.avatar_id
                    WHERE  u.subscriber_Id = :subscriberId";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetchAll();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getByEmail($id, $email): bool
    {
        try {
            $sql = "SELECT u.*
                    FROM   users u
                    WHERE  u.subscriber_Id = :subscriberId
                    AND    u.email = :email
                    AND    u.id <> :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetchAll();
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
            $url = BASE_URL . "/";
            $sql = "SELECT u.*, CONCAT('{$url}', f.path) as url
                    FROM   users u
                        LEFT JOIN files f on f.id = u.avatar_id
                    WHERE  u.subscriber_Id = :subscriberId
                    AND    u.id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $this->result['data'] = $sql->fetch();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->result['message']['hasError'] = true;
                $this->result['message']['errors'][] = "Usuário não cadastrado";
                return false;
            }

            $data = $this->result['data'];
            if (!is_null($data['avatar_id']) && $data['avatar_id'] > 0) {
                $file = new File($this->subscriberId);
                if (!$file->delete($data['avatar_id'], $data['avatar'])) {
                    $this->resul['message']['hasError'] = true;
                    $this->resul['message']['errors'][] = 'Falha ao remover imagem';
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

            $this->result['data'] = array();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->result['message']['hasError'] = true;
                $this->result['message']['errors'][] = "Usuário não cadastrado";
                return false;
            }

            if (!$this->isValid($data)) {
                return false;
            }

            if (isset($data['Files'])) {
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

            $avatar_id = intval($data['avatar_id'] ?? 0) == 0 ? null : intval($data['avatar_id']);

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':subscriberId', $this->subscriberId);
            $sql->bindValue(':id', intval($id));
            $sql->bindValue(':name', $data['name']);
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':permission', $data['permission']);
            $sql->bindValue(':avatar_id', $avatar_id);
            $sql->execute();

            if (!$this->getById($id)) {
                return false;
            }

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

            if (isset($data['Files'])) {
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
        if (empty($data['email'])) {
            $this->result['message']['errors'][] = 'E-mail não informado';
        }

        if (!empty($data['email'])) {
            if ($this->getByEmail(($data['id'] ?? 0), $data['email'])) {
                $this->result['message']['errors'][] = 'E-mail já cadastro';
            }
        }

        if (empty($data['name'])) {
            $this->result['message']['errors'][] = 'Name não informado';
        }

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }
}
