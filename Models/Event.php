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

    public function proximoEvento(): bool
    {
        try {
            $sql = "select x.descricao, x.ativo_as as proxEvento FROM events x WHERE x.ativo_as > NOW() ORDER BY x.ativo_as LIMIT 1";

            $sql = $this->db->prepare($sql);
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

    public function getActivosToInc(): bool
    {
        try {
            $sql = "SELECT X.* FROM (
                        SELECT e.*,
                            (e.Dupla - ( SELECT COUNT(*) FROM inscriptions i WHERE i.evento = e.chave AND i.data = e.data AND i.cadeira = 'Dupla' )) as dispDupla, 
                            (e.Simples - ( SELECT COUNT(*) FROM inscriptions i WHERE i.evento = e.chave AND i.data = e.data AND i.cadeira = 'Simples' )) as dispSimples, 
                            (e.Tripla - ( SELECT COUNT(*) FROM inscriptions i WHERE i.evento = e.chave AND i.data = e.data AND i.cadeira = 'Tripla' )) as dispTripla, 
                            (e.Quadrupla - ( SELECT COUNT(*) FROM inscriptions i WHERE i.evento = e.chave AND i.data = e.data AND i.cadeira = 'Quadrupla' )) as dispQuadrupla
                        FROM   events e
                        WHERE  e.ativo = 1
                        AND    e.ativo_as <= NOW()
                        AND    e.inativo_as > NOW()
                        ORDER BY e.data
                    ) AS X 
                    WHERE X.dispQuadrupla > 0 || X.dispTripla > 0 || X.dispDupla > 0 || X.dispSimples > 0";

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

    public function getActivos(): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   events
                    WHERE  ativo = 1
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

    public function Name($name): string
    {
        $format = array();
        $format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
        $data = strtr(utf8_decode($name), utf8_decode($format['a']), $format['b']);
        $data = strip_tags(trim($data));
        $data = str_replace(' ', '-', $data);
        $data = str_replace(array('-----', '----', '---', '--'), '-', $data);
        return strtoupper(utf8_encode($data));
    }

    public function Data($data)
    {
        $format = explode(' ', $data);
        $formatData = explode('/', $format[0]);
        $formatHora = count($format) > 1 ? $format[1] : '';

        if (!checkdate($formatData[1], $formatData[0], $formatData[2])) :
            return false;
        else :
            if (!empty($formatHora)) :
                $format[1] = date('H:i:s');
            else :
                $formatHora = "00:00";
            endif;
            $dormat = $formatData[2] . '-' . $formatData[1] . '-' . $formatData[0] . ' ' . substr($formatHora, 0, 5) . ':00';
            return $dormat;
        endif;
    }

    public function get(): bool
    {
        try {
            $sql = "SELECT e.*, 
                           DATE_FORMAT(e.data, '%d/%m/%Y') as dataFmt,
                           DATE_FORMAT(e.ativo_as, '%d/%m/%Y %H:%i') as ativoAsFmt,
                           DATE_FORMAT(e.inativo_as, '%d/%m/%Y %H:%i') as inativoAsFmt
                    FROM   events e
                    ORDER BY e.ativo_as ASC, e.inativo_as ASC";

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
            $sql = "SELECT e.* , 
                           DATE_FORMAT(e.data, '%d/%m/%Y') as dataFmt,
                           DATE_FORMAT(e.ativo_as, '%d/%m/%Y %H:%i') as ativoAsFmt,
                           DATE_FORMAT(e.inativo_as, '%d/%m/%Y %H:%i') as inativoAsFmt
                    FROM   events e
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

    public function active(int $id): bool
    {
        try {

            $sql = "UPDATE events SET ativo = 1 WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($this->getById($id)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {

            $sql = "DELETE FROM events
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();

            if ($this->getById($id)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, $data): bool
    {
        try {

            $data['data'] = substr($this->Data($data['dataFmt']), 0, 10);
            $data['ativo_as'] = $this->Data(substr($data['ativoAsFmt'], 0, 16) . ":00");
            $data['inativo_as'] = $this->Data(substr($data['inativoAsFmt'], 0, 16) . ":00");

            if (!$this->isValid($data)) {
                return false;
            }

            if (!(intval($data['id']) > 0)) {
                $this->result['message']['errors'][] = 'ID não informado';
                $this->result['message']['hasError'] = true;
                return false;
            }

            $sql = "UPDATE events
                    SET    ativo = :ativo, 
                           data = :data, 
                           chave = :chave, 
                           descricao = :descricao, 
                           simples = :simples, 
                           dupla = :dupla, 
                           quadrupla = :quadrupla, 
                           tripla = :tripla, 
                           ativo_as = :ativo_as, 
                           inativo_as = :inativo_as,
                           sol_idade = :sol_idade,
                           sol_periodo = :sol_periodo,
                           periodos = :periodos,
                           criancas_de = :criancas_de
                    WHERE  id = :id";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $data['id']);
            $sql->bindValue(':ativo', ($data['ativo'] ?? 0));
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':chave', $data['chave']);
            $sql->bindValue(':descricao', $data['descricao']);
            $sql->bindValue(':simples', ($data['simples'] ?? 0));
            $sql->bindValue(':quadrupla', ($data['quadrupla'] ?? 0));
            $sql->bindValue(':tripla', ($data['tripla'] ?? 0));
            $sql->bindValue(':dupla', ($data['dupla'] ?? 0));
            $sql->bindValue(':sol_idade', ($data['sol_idade'] ?? 0));
            $sql->bindValue(':sol_periodo', ($data['sol_periodo'] ?? 0));
            $sql->bindValue(':ativo_as', $data['ativo_as']);
            $sql->bindValue(':inativo_as', $data['inativo_as']);
            $sql->bindValue(':periodos', $data['periodos'] ?? 0);
            $sql->bindValue(':criancas_de', $data['criancas_de'] ?? "");
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

            $data['chave'] = $this->Name($data['descricao']);
            $data['data'] = substr($this->Data($data['dataFmt']), 0, 10);
            $data['ativo_as'] = $this->Data(substr($data['ativoAsFmt'], 0, 16) . ":00");
            $data['inativo_as'] = $this->Data(substr($data['inativoAsFmt'], 0, 16) . ":00");

            if (!$this->isValid($data)) {
                return false;
            }

            $sql = "INSERT INTO events
                    (ativo, data, chave, descricao, simples, dupla, tripla, quadrupla, sol_idade, ativo_as, inativo_as, sol_periodo, periodos, criancas_de)
                    VALUES 
                    (1, :data, :chave, :descricao, :simples, :dupla, :tripla, :quadrupla, :sol_idade, :ativo_as, :inativo_as, :sol_periodo, :periodos, :criancas_de)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':chave', $data['chave']);
            $sql->bindValue(':descricao', $data['descricao']);
            $sql->bindValue(':simples', ($data['simples'] ?? 0));
            $sql->bindValue(':dupla', ($data['dupla'] ?? 0));
            $sql->bindValue(':tripla', ($data['tripla'] ?? 0));
            $sql->bindValue(':quadrupla', ($data['quadrupla'] ?? 0));
            $sql->bindValue(':sol_idade', ($data['sol_idade'] ?? 0));
            $sql->bindValue(':sol_periodo', ($data['sol_periodo'] ?? 0));
            $sql->bindValue(':ativo_as', $this->Data($data['ativoAsFmt']));
            $sql->bindValue(':inativo_as', $this->Data($data['inativoAsFmt']));
            $sql->bindValue(':periodos', ($data['periodos'] ?? ""));
            $sql->bindValue(':criancas_de', ($data['criancas_de'] ?? ""));
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

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }
}
