<?php

namespace Controllers;

use \Core\Controller;
use Core\Helpers;
use \Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if ($this->isGet()) {
            $event = new Event($this->dataToken["subscriberId"] ?? 0);
            $event->get();
            $retObj = $event->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function getActivosToInc()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if (Helpers::request_limit("getActivosToInc", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array("Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            $event = new Event($this->dataToken["subscriberId"] ?? 0);
            $event->getActivosToInc();
            $retObj = $event->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }

    public function proximoEvento()
    {
        $retObj = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if (Helpers::request_limit("proximoEvento", SESSION_QTDE, SESSION_SECOND)) {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array("Desculpe, mas por segurança aguarde pelo " . SESSION_SECOND . " segundos para tentar novamente.")));
            $this->toJson($retObj);
            return;
        }

        if ($this->isGet()) {
            $event = new Event($this->dataToken["subscriberId"] ?? 0);
            $event->proximoEvento();
            $retObj = $event->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Método ' . $this->method() . ' não disponível')));
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
                $event = new Event($this->dataToken["subscriberId"] ?? 0);
                $event->create($this->data());
                $retObj = $event->getResult();
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
        $event = new Event($this->dataToken["subscriberId"] ?? 0);

        if ($event->getById($id)) {
            $retObj = $event->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('Usuário não cadastrado')));
        }

        $this->toJson($retObj);
    }

    private function update($id)
    {
        $event = new Event($this->dataToken["subscriberId"] ?? 0);

        $event->update($id, $this->data());
        $retObj = $event->getResult();

        $this->toJson($retObj);
    }

    private function delete($id)
    {
        $event = new Event($this->dataToken["subscriberId"] ?? 0);

        $event->delete($id);
        $retObj = $event->getResult();

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

    public function active($id)
    {
        $event = new Event($this->dataToken["subscriberId"] ?? 0);

        $event->active($id);
        $retObj = $event->getResult();

        $this->toJson($retObj);
    }
}
