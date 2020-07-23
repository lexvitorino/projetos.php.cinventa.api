<?php

namespace Controllers;

use \Core\Controller;
use \Models\Inscription;

class InscriptionController extends Controller
{
    public function index()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        $this->toJson($retObj);
    }

    public function byEvento(string $evento)
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if ($this->isValid()) {
            if ($this->isGet()) {
                $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);
                if ($inscription->get($evento)) {
                    $retObj = $inscription->getResult();
                }
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
            }
        }

        $this->toJson($retObj);
    }

    public function create()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if ($this->isPost()) {
            $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);
            $inscription->create($this->data());
            $retObj = $inscription->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function byId($id)
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

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
                    $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
                    break;
            }
        }

        $this->toJson($retObj);
    }

    public function byEventoAndDataAndEmail($evento, $data, $email)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isGet()) {
            if ($inscription->getByEventoAndDataAndEmail($evento, $data, $email)) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function byEventoAndData($evento, $data)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isGet()) {
            if ($inscription->getByEventoAndData($evento, $data)) {
                $retObj = $inscription->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
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
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
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
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function sendEmail()
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($this->isGet()) {
            if ($inscription->sendEmail()) {
                $retObj = $inscription->getResult();
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    private function getById($id)
    {
        $inscription = new Inscription($this->dataToken["subscriberId"] ?? 0);

        if ($inscription->getById($id)) {
            $retObj = $inscription->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
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
            $this->toJson(array('message' =>  array('hasError' => true, 'errors' => array('Acesso negado'))));
            die;
        }
        return $isLogged;
    }
}
