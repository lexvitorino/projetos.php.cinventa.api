<?php

namespace Controllers;

use \Core\Controller;
use \Models\User;

class UserController extends Controller
{
    public function index()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if ($this->isValid()) {
            if ($this->isGet()) {
                $user = new User($this->dataToken["subscriberId"]);
                if ($user->get()) {
                    $retObj = $user->getResult();
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

        if ($this->isValid()) {
            if ($this->isPost()) {
                $user = new User($this->dataToken["subscriberId"]);
                $user->create($this->data());
                $retObj = $user->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
            }
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

    private function getById($id)
    {
        $user = new User($this->dataToken["subscriberId"]);

        if ($user->getById($id)) {
            $retObj = $user->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
        }

        $this->toJson($retObj);
    }

    private function update($id)
    {
        $user = new User($this->dataToken["subscriberId"]);

        $user->update($id, $this->data());
        $retObj = $user->getResult();

        $this->toJson($retObj);
    }

    private function delete($id)
    {
        $user = new User($this->dataToken["subscriberId"]);

        $user->delete($id);
        $retObj = $user->getResult();

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
