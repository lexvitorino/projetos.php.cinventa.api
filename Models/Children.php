<?php

namespace Models;

use Exception;
use \Core\Model;
use PDO;

class Children extends Model
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

    public function getByEventoAndData(string $evento, string $data): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   childrens
                    WHERE  evento = :evento
                    AND    data = :data";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
            $sql->bindValue(':data', $data);
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
            $sql = "SELECT e.* 
                    FROM   Childrens e
                    WHERE  e.id = :id";

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

    public function delete(int $id): bool
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

            $sql = "INSERT INTO Childrens
                    (evento, data, periodo, email, responsavel, children1, children2, children3, children4, children5, old1, old2, old3, old4, old5)
                    VALUES 
                    (:evento, :data, :periodo, :email, :responsavel, :children1, :children2, :children3, :children4, :children5, :old1, :old2, :old3, :old4, :old5)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $data['evento']);
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':periodo', $data['periodo'] ?? "UNICO");
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':responsavel', $data['responsavel']);
            $sql->bindValue(':children1', $data['children1']);
            $sql->bindValue(':old1', $data['old1']);
            $sql->bindValue(':children2', ($data['children2'] ?? ""));
            $sql->bindValue(':old2', ($data['old2'] ?? 0));
            $sql->bindValue(':children3', ($data['children3'] ?? ""));
            $sql->bindValue(':old3', ($data['old3'] ?? 0));
            $sql->bindValue(':children4', ($data['children4'] ?? ""));
            $sql->bindValue(':old4', ($data['old4'] ?? 0));
            $sql->bindValue(':children5', ($data['children5'] ?? ""));
            $sql->bindValue(':old5', ($data['old5'] ?? 0));
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

    public function buscaEmailCadastradoParaOMesmoPeriodo(int $id, string $data, string $periodo, string $email): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   childrens
                    WHERE  id <> :id
                    AND    data = :data
                    AND    periodo = :periodo
                    AND    email = :email";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->bindValue(':data', $data);
            $sql->bindValue(':periodo', $periodo);
            $sql->bindValue(':email', $email);
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
            $this->result['message']['errors'][] = array('show' => true, 'value' => 'Evento não informado');
        }

        if (empty($data['responsavel'])) {
            $this->result['message']['errors'][] = array('show' => true, 'value' => 'Responsável não informado');
        }

        if (empty($data['email'])) {
            $this->result['message']['errors'][] = array('show' => true, 'value' => 'E-mail não informado');
        } else {
            $inscription = new Inscription($this->subscriberId);
            if (!$inscription->buscaEmailCadastradoPeriodo($data['evento'], $data['data'], $data['periodo'] ?? "UNICO", $data['email'])) {
                $this->result['message']['errors'][] = array('show' => false, 'value' => "O responsável {$data['responsavel']} com o email {$data['email']} não foi cadastrado para este evento");
            }
        }

        $this->childrenIdValid(1, $data['children1'] ?? "", $data['old1'] ?? 0, true);
        $this->childrenIdValid(2, $data['children2'] ?? "", $data['old2'] ?? 0);
        $this->childrenIdValid(3, $data['children3'] ?? "", $data['old3'] ?? 0);
        $this->childrenIdValid(4, $data['children4'] ?? "", $data['old4'] ?? 0);
        $this->childrenIdValid(5, $data['children5'] ?? "", $data['old5'] ?? 0);

        $event = new Event($this->subscriberId);
        if ($event->getByChave($data['evento'], $data['data'])) {
            $idades = explode(',', $event->getResult()['data']['criancas_de'] ?? "");
            $this->oldIsValid(1, $idades, $data['old1'] ?? 0);
            $this->oldIsValid(2, $idades, $data['old2'] ?? 0);
            $this->oldIsValid(3, $idades, $data['old3'] ?? 0);
            $this->oldIsValid(4, $idades, $data['old4'] ?? 0);
            $this->oldIsValid(5, $idades, $data['old5'] ?? 0);
        }

        if (count($this->result['message']['errors']) == 0) {
            if ($this->buscaEmailCadastradoParaOMesmoPeriodo(0, $data['data'], ($data['periodo'] ?? "UNICO"), $data['email'])) {
                $this->result['message']['errors'][] = array('show' => false, 'value' => 'Você já possui uma inscrição para este período');
            }
        }

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }

    private function childrenIdValid($num, $children, $old, $nameIsRequired = false)
    {
        if ($nameIsRequired && empty($children)) {
            $this->result['message']['errors'][] = array('show' => true, 'value' => "Criança ({$num}) não informado");
        }

        if (!empty($children)) {
            if (empty($old) || $old == 0) {
                $this->result['message']['errors'][] = array('show' => true, 'value' => "Idade Criança ({$num}) não informado");
            }

            if (Count(explode(' ', $children)) < 2) {
                $this->result['message']['errors'][] = array('show' => true, 'value' => "Por favor, para Nome Criança ({$num}) preencha pelo menos Nome e Sobrenome");
            }
        }
    }

    private function oldIsValid($num, $idades, $old)
    {
        if (empty($old) || $old == 0) {
            return;
        }
        if (Count($idades) > 0) {
            $max = max($idades);
            $min = min($idades);

            if ($old < $min || $old > $max) {
                $this->result['message']['errors'][] = array('show' => true, 'value' => "Idade Criança ({$num}) não permitida para o evento informado");
            }
        }
    }
}
