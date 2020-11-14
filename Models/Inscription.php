<?php

namespace Models;

use Exception;
use \Core\Model;
use PDO;

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
                return $this->result = $sql->fetchAll(PDO::FETCH_ASSOC);
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
                $this->result['data'] = $sql->fetch(PDO::FETCH_ASSOC);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function vagasValidas($chave, $data, $cadeira): bool
    {
        try {
            $event = new Event($this->subscriberId);
            if ($event->getByChave($chave, $data)) {

                $sql = "SELECT COUNT(id) as QT 
                        FROM   inscriptions 
                        WHERE  evento = :chave and data = :data and cadeira = :cadeira";

                $sql = $this->db->prepare($sql);
                $sql->bindValue(':chave', $chave);
                $sql->bindValue(':data', $data);
                $sql->bindValue(':cadeira', $cadeira);
                $sql->execute();

                if ($sql->rowCount() > 0) {
                    $res = $sql->fetch(PDO::FETCH_ASSOC);

                    $limite = 0;
                    if ($cadeira == "Simples") {
                        $limite = intval($event->getResult()['data']['simples']);
                    } else if ($cadeira == "Dupla") {
                        $limite = intval($event->getResult()['data']['dupla']);
                    } else if ($cadeira == "Tripla") {
                        $limite = intval($event->getResult()['data']['tripla']);
                    } else if ($cadeira == "Quadrupla") {
                        $limite = intval($event->getResult()['data']['quadrupla']);
                    }

                    $this->result['data'] = array(
                        'inscricoes' => intval($res['QT']),
                        'limite' => $limite,
                    );

                    if (intval($this->result['data']['inscricoes']) >= $limite) {
                        $this->result['message']['errors'][] = array('show' => false, 'value' => 'Todas as ' . $limite . ' vagas destinadas as cadeiras ' . $cadeira . ' foram preenchidas');
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

    public function buscaEmailCadastradoParaOMesmoPeriodo(int $id, string $data, string $periodo, string $email): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
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

    public function buscaEmailCadastradoPeriodo(string $evento, string $data, string $periodo, string $email): bool
    {
        try {
            $sql = "SELECT * 
                    FROM   inscriptions
                    WHERE  evento = :evento
                    AND    data = :data
                    AND    email = :email
                    AND    periodo = :periodo";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
            $sql->bindValue(':data', $data);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':periodo', $periodo ?? "UNICO");
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

    public function getByEmail(string $email, string $ativo): bool
    {
        try {
            $sql = "SELECT i.* 
                    FROM   inscriptions i
                        INNER JOIN events e ON e.chave = i.evento 
                    WHERE  i.email = :email
                    AND    e.ativo = :ativo
                    LIMIT  1";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':ativo', $ativo);
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
                $this->result['data'] = $sql->fetchAll(PDO::FETCH_ASSOC);
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
                    (evento, data, email, nome, sobrenome, area, supervisor, lider, conjuge, cadeira, idade, acompanhante3, acompanhante4, periodo)
                    VALUES 
                    (:evento, :data, :email, :nome, :sobrenome, :area, :supervisor, :lider, :conjuge, :cadeira, :idade, :acompanhante3, :acompanhante4, :periodo)";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $data['evento']);
            $sql->bindValue(':email', $data['email']);
            $sql->bindValue(':data', $data['data']);
            $sql->bindValue(':nome', ($data['nome']));
            $sql->bindValue(':sobrenome', ($data['sobrenome'] ?? ""));
            $sql->bindValue(':conjuge', ($data['cadeira'] == 'Simples' ? "" : ($data['conjuge'] ?? "")));
            $sql->bindValue(':cadeira', $data['cadeira']);
            $sql->bindValue(':acompanhante3', $data['acompanhante3'] ?? "");
            $sql->bindValue(':acompanhante4', $data['acompanhante4'] ?? "");
            $sql->bindValue(':area', ($data['area'] ?? ""));
            $sql->bindValue(':supervisor', ($data['supervisor'] ?? ""));
            $sql->bindValue(':lider', ($data['lider'] ?? ""));
            $sql->bindValue(':idade', ($data['idade'] ?? 0));
            $sql->bindValue(':periodo', ($data['periodo'] ?? "UNICO"));
            $sql->execute();

            $id = $this->db->lastInsertId();

            if (!$this->getById($id)) {
                return false;
            }

            // $this->sendEmail();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function sendEmail($id = 0)
    {
        try {
            if ($id > 0) {
                if (!$this->getById($id)) {
                    return;
                }
            }

            $inscricao = $this->getResult()['data'];

            $event = new Event($this->subscriberId);
            if (!$event->getByChave($inscricao['evento'], $inscricao['data'])) {
                return;
            }

            $evento = $event->getResult()['data'];

            $subject = 'CGErmelino Informa';

            $body = '<!DOCTYPE html
                            PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                            <title>Comunidade da Graça de Ermelino Matarazzo</title>
                            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                        </head>
                        
                        <body style="margin: 0; padding: 0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
                                            style="border-collapse: collapse;">
                                            <tr>
                                                <td>
                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                                                        <tr>
                                                            <td align="center" style="padding: 40px 0 30px 0;">
                                                                <img src="http://cgermelino.com.br/wp-content/uploads/2018/08/logo.png"
                                                                    alt="Criando Mágica de E-mail" width="300" height="auto"
                                                                    style="display: block;" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td>
                                                                            <p
                                                                                style="color: #153643; font-family: Arial, sans-serif; font-size: 20px;">
                                                                                É isso ai.. <strong>{{NOME_SOBRENOME}}</strong></p>
                                                                            <p
                                                                                style="color: #153643; font-family: Arial, sans-serif; font-size: 16px;">
                                                                                Aguardamos você para podermos congregar juntos no dia
                                                                                <strong>{{DATA}}</strong> para o <strong>{{EVENTO}}</strong>.
                                                                            </p>
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>ID: </strong></span>
                                                                            {{ID}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Cadeira: </strong></span>
                                                                            {{CADEIRA}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Período: </strong></span>
                                                                            {{PERIODO}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Email: </strong></span>
                                                                            {{EMAIL}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Nome completo: </strong></span>
                                                                            {{NOME_SOBRENOME}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Nome do seu par: </strong></span>
                                                                            {{CONJUGE}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                        <span><strong>Nome do terceiro acompanhante: </strong></span>
                                                                        {{ACOMPANHANTE3}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Nome do quarto acompanhante: </strong></span>
                                                                            {{ACOMPANHANTE4}}
                                                                        </td>
                                                                    </tr>
                                                                    <tr
                                                                        style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
                                                                        <td>
                                                                            <span><strong>Supervisor de área: </strong></span>
                                                                            {{AREA}}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td bgcolor="#ee4c50" style="padding: 30px 30px 30px 30px;">
                                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                                    <tr>
                                                                        <td
                                                                            style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
                                                                            &reg;2020
                                                                            <a href="http://mi7dev.com.br" style="color: #ffffff;">
                                                                                <font color="#ffffff">MI7Dev</font>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        
                        </html>';

            $nomeSobrenome = $toName = $inscricao['nome'] . " " . $inscricao['sobrenome'];
            $body = str_replace('{{NOME_SOBRENOME}}', $nomeSobrenome, $body);

            $data = explode('-', $evento['data']);
            $data = $data[2] . '/' . $data[1] . '/' . $data[0];
            $body = str_replace('{{DATA}}', $data, $body);
            $body = str_replace('{{EVENTO}}', $evento['descricao'], $body);

            $body = str_replace('{{ID}}',  $inscricao['id'], $body);
            $body = str_replace('{{EMAIL}}',  $inscricao['email'], $body);
            $body = str_replace('{{NOME_SOBRENOME}}', $nomeSobrenome, $body);
            $body = str_replace('{{CONJUGE}}',  $inscricao['conjuge'] ?? "", $body);
            $body = str_replace('{{ACOMPANHANTE3}}',  $inscricao['acompanhante3'] ?? "", $body);
            $body = str_replace('{{ACOMPANHANTE4}}',  $inscricao['acompanhante4'] ?? "", $body);
            $body = str_replace('{{AREA}}',  $inscricao['area'], $body);
            $body = str_replace('{{CADEIRA}}', $inscricao['cadeira'], $body);
            $body = str_replace('{{PERIODO}}', $inscricao['periodo'], $body);

            $to = $inscricao['email'];
            $toName = $inscricao['nome'] . " " . $inscricao['sobrenome'];

            $mail = new Mail();
            $mail->send($subject, $body, $to, $toName);

            if ($id > 0) {
                $this->result = $mail->getResult();
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

    public function eventoAtivo(string $evento): bool
    {
        try {
            $sql = "SELECT *
                    FROM   events
                    WHERE  chave = :evento
                    AND    ativo = 1
                    AND    ativo_as <= NOW() 
                    LIMIT  1";

            $sql = $this->db->prepare($sql);
            $sql->bindValue(':evento', $evento);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                return true;
            } else {
                $this->result['message']['errors'][] = array('show' => false, 'value' => 'Evento não está mais disponível');
                $this->result['message']['hasError'] = true;
                return false;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function isValid($data): bool
    {
        if (empty($data['evento'])) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => 'Evento não informado');
        }

        if (empty($data['email'])) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => 'E-mail não informado');
        }

        if (empty($data['nome'])) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => 'Nome não informado');
        }

        if (empty($data['sobrenome'])) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => 'Sobrenome não informado');
        }

        if (empty($data['area'])) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => 'Área não informado');
        }

        if (count($this->result['message']['errors']) == 0) {
            $this->eventoAtivo($data['evento']);
        }

        if (count($this->result['message']['errors']) == 0) {
            if ($this->buscaEmailCadastradoParaOMesmoPeriodo(($data['id'] ?? 0), $data['data'], ($data['periodo'] ?? "UNIQUE"), $data['email'])) {
                $this->result['message']['errors'][] = array('show' => false, 'value' => 'Você já possui uma inscrição para este período');
            }
        }

        if (count($this->result['message']['errors']) == 0) {
            $this->vagasValidas($data['evento'], $data['data'], $data['cadeira']);
        }

        $this->result['message']['hasError'] = count($this->result['message']['errors']) > 0;
        return !$this->result['message']['hasError'];
    }
}
