<?php

namespace Models;

use Exception;
use \Core\Model;

class Inscription extends Model
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

    public function get(string $evento)
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
                    WHERE  evento = :evento";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
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
                    FROM   inscriptions
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
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

    public function vagasValidas($chave, $data, $duplas): bool
    {
        try {
            $event = new Event($this->subscriberId);
            if ($event->getByChave($chave, $data)) {

                $sql = "";
                if ($duplas == "S") {
                    $sql = "SELECT COUNT(id) as QT 
                            FROM   inscriptions 
                            WHERE  evento = :chave and data = :data and conjuge is not  null AND conjuge <> ''";
                } else {
                    $sql = "SELECT COUNT(id) as QT 
                            FROM   inscriptions 
                            WHERE  evento = :chave and data = :data and (conjuge is null or conjuge = '')";
                }

                $sql = $this->db->prepare($sql);
                $sql->bindValue(':chave', $chave);
                $sql->bindValue(':data', $data);
                $sql->execute();

                if ($sql->rowCount() > 0) {
                    $res = $sql->fetch();

                    $this->result['data'] = array(
                        'inscricoes' => intval($res['QT']),
                        'limite' => $duplas == "S" ? intval($event->getResult()['data']['dupla']) : intval($event->getResult()['data']['simples']),
                    );

                    if (intval($this->result['data']['inscricoes']) >= intval($this->result['data']['limite'])) {
                        $this->result['message']['errors'][] = 'Todas as ' . $this->result['data']['limite'] . ' vagas destinadas as cadeiras ' . ($duplas == "S" ? 'duplas' : 'simples') . ' foram preenchidas';
                        $this->result['message']['hasError'] = true;
                    }

                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getByDataAndEmail(int $id, string $data, string $email): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
                    WHERE  id <> :id
                    AND    data = :data
                    AND    email = :email";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->bindValue(':data', $data);
            $sql->bindValue(':email', $email);
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

    public function getByEventoAndDataAndEmail(string $evento, string $data, string $email): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
                    WHERE  evento = :evento
                    AND    data = :data
                    AND    email = :email";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
            $sql->bindValue(':data', $data);
            $sql->bindValue(':email', $email);
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

    public function getByEventoAndData(string $evento, string $data): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
                    WHERE  evento = :evento
                    AND    data = :data";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
            $sql->bindValue(':data', $data);
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

    public function confirmar(int $id): bool
    {
        try {
            $sql = "UPDATE inscriptions
                    SET    confirmado = 1
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if (!$this->getById($id)) {
                return false;
            }

            return true;
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

            $sql = "INSERT INTO inscriptions
                    (evento, data, email, nome, sobrenome, area, supervisor, lider, conjuge)
                    VALUES 
                    (:evento, :data, :email, :nome, :sobrenome, :area, :supervisor, :lider, :conjuge)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $data['evento']);
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':nome', $data['nome']);
            $sql->bindValue(':sobrenome', $data['sobrenome']);
            $sql->bindValue(':conjuge', ($data['conjuge'] ?? ""));
            $sql->bindValue(':area', $data['area']);
            $sql->bindValue(':supervisor', ($data['supervisor'] ?? ""));
            $sql->bindValue(':lider', ($data['lider'] ?? ""));
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
        if (empty($data['evento'])) {
            $this->result['message']['errors'][] = 'Evento não informado';
        }

        if (empty($data['email'])) {
            $this->result['message']['errors'][] = 'E-mail não informado';
        }

        if (empty($data['nome'])) {
            $this->result['message']['errors'][] = 'Nome não informado';
        }

        if (empty($data['sobrenome'])) {
            $this->result['message']['errors'][] = 'Sobrenome não informado';
        }

        if (empty($data['area'])) {
            $this->result['message']['errors'][] = 'Área não informada';
        }

        if (count($this->result['message']['errors']) == 0) {
            if ($this->getByDataAndEmail(($data['id'] ?? 0), $data['data'], $data['email'])) {
                $this->result['message']['errors'][] = 'Você já possui uma inscricao para esta data';
            }
        }

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }
}
