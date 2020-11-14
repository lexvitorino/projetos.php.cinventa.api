<?php

namespace Controllers;

use \Core\Controller;
use Core\Helpers;
use \Models\Inscription;

class InscriptionController extends Controller
{
    public function index()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array(
                    'show' => false,
                    'value' => ''
                )
            ),
        );

        $this->toJson($retObj);
    }

    public function byEvento(string $evento)
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array(
                    'show' => false,
                    'value' => ''
                )
            ),
        );

        if ($this->isValid()) {
            if ($this->isGet()) {
                $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);
                if ($inscription->get($evento)) {
                    $retObj = $inscription->getResult();
                }
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
            }
        }

        $this->toJson($retObj);
    }

    public function create()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array(
                    'show' => false,
                    'value' => ''
                )
            ),
        );

        if (Helpers::request_limit("create", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => "Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isPost()) {
            $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);
            $inscription->create($this->data());
            $retObj = $inscription->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function byId($id)
    {
        if ($this->isValid()) {
            switch ($this->method()) {
                case 'GET':
                    $retObj = $this->getById($id);
                    break;
                case 'PUT':
                    $retObj = $this->update($id);
                    break;
                case 'DELETE':
                    $retObj = $this->delete($id);
                    break;
                default:
                    $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
                    break;
            }
        }

        $this->toJson($retObj);
    }

    public function byEventoAndDataAndEmail($evento, $data, $periodo, $email)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if (Helpers::request_limit("byEventoAndDataAndEmail", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => "Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            if ($inscription->buscaEmailCadastradoPeriodo($evento, $data, $periodo, $email)) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function byEventoAndData($evento, $data)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if (Helpers::request_limit("byEventoAndData", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => "Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            if ($inscription->getByEventoAndData($evento, $data)) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function byEmail($email, $ativo)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if (Helpers::request_limit("byEmail", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => "Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            if ($inscription->getByEmail($email, $ativo)) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function confirmar()
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isPut()) {
            if ($inscription->confirmar($this->data()['id'])) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function vagasValidas($chave, $data, $duplas)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isGet()) {
            $inscription->vagasValidas($chave, $data, $duplas);
            $retObj = $inscription->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function sendEmail($id)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isGet()) {
            if ($inscription->sendEmail($id)) {
                $retObj = $inscription->getResult();
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    private function getById($id)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($inscription->getById($id)) {
            $retObj = $inscription->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
        }

        $this->toJson($retObj);
    }

    private function update($id)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        $inscription->update($id, $this->data());
        $retObj = $inscription->getResult();

        $this->toJson($retObj);
    }

    private function delete($id)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        $retObj = $inscription->getResult();

        $this->toJson($retObj);
    }

    private function isValid(): bool
    {
        $isLogged = $this->isLogged();

        if (!$isLogged) {
            $this->toJson(array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Acesso negado'))));
            die;
        }

        return $isLogged;
    }
}
