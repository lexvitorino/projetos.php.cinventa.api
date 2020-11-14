<?php

namespace Controllers;

use \Core\Controller;
use \Models\Session;
use \Models\User;

class SessionController extends Controller
{
    public function index()
    {
        $this->toJson(array());
    }

    public function auth()
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

        if ($this->isPost()) {
            if (!empty($this->data()['email']) && !empty($this->data()['password'])) {
                $session = new Session();
                if ($session->auth($this->data()['email'], $this->data()['password'])) {
                    $retObj = $session->getResult();
                } else {
                    $this->toJson(array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Acesso negado')));
                }
            } else {
                $retObj = array('hasError' => true, 'errors' => array('show' => false, 'value' => 'E-mail e/ou senha não preenchido.'));
            }
        } else {
            $retObj = array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método de requisição incompatível'));
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

        if ($this->isPost()) {
            $user = new User($this->dataToken["subscriberId"] ?? 0);
            $user->create($this->data());
            $retObj = $user->getResult();
        } else {
            $retObj = array('message' =>  array('hasError' => true, 'errors' => array('show' => false, 'value' => 'Método ' . $this->method() . ' não disponível')));
        }

        $this->toJson($retObj);
    }
}
