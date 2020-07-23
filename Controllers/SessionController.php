<?php

namespace Controllers;

use \Core\Controller;

class SessionController extends Controller
{
    public function index()
    {
        $this->toJson(array());
    }

    public function auth()
    {
        $retObj = array(
            'error' => array(
                'hasError' => false,
                'errors' => array()
            ),
        );

        if ($this->isPost()) {
            if (!empty($this->data()['email']) && !empty($this->data()['password'])) {
                $session = new \Models\Session();
                if ($session->auth($this->data()['email'], $this->data()['password'])) {
                    $retObj['data'] = $session->getToken();
                } else {
                    $this->toJson(array('hasError' => true, 'errors' => array('Acesso negado')));
                }
            } else {
                $retObj = array('hasError' => true, 'errors' => array('E-mail e/ou senha não preenchido.'));
            }
        } else {
            $retObj = array('hasError' => true, 'errors' => array('Método de requisição incompatível'));
        }

        $this->toJson($retObj);
    }
}
