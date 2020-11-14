<?php

namespace Controllers;

use \Core\Controller;
use Core\Helpers;
use \Models\Children;

class ChildrenController extends Controller
{
    public function index()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array(
                    'show' => false,
                    'value' => ""
                )
            ),
        );

        $this->toJson($retObj);
    }

    public function byEventoAndData($evento, $data)
    {
        $children = new Children($this->dataToken["subscriberId"] ?? 0);

        if (Helpers::request_limit("byEventoAndData", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => "Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            if ($children->getByEventoAndData($evento, $data)) {
                $retObj = $children->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
            }
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
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
                $children = new Children($this->dataToken["subscriberId"] ?? 0);
                $children->create($this->data());
                $retObj = $children->getResult();
            } else {
                $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
            }
        }

        $this->toJson($retObj);
    }

    public function byId($id)
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array(
                    'show' => false,
                    'value' => ""
                )
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
                    $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
                    break;
            }
        }

        $this->toJson($retObj);
    }

    private function getById($id)
    {
        $children = new Children($this->dataToken["subscriberId"] ?? 0);

        if ($children->getById($id)) {
            $retObj = $children->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Usuário não cadastrado')));
        }

        $this->toJson($retObj);
    }

    private function update($id)
    {
        $children = new Children($this->dataToken["subscriberId"] ?? 0);

        $children->update($id, $this->data());
        $retObj = $children->getResult();

        $this->toJson($retObj);
    }

    private function delete($id)
    {
        $children = new Children($this->dataToken["subscriberId"] ?? 0);

        $children->delete($id);
        $retObj = $children->getResult();

        $this->toJson($retObj);
    }

    private function isValid(): bool
    {
        return true;

        $isLogged = $this->isLogged();

        if (!$isLogged) {
            $this->toJson(array('message' =>  array('hasError' => true, 'errors' => array('Acesso negado'))));
            die;
        }
        return $isLogged;
    }
}
