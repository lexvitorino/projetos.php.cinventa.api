<?php

namespace Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private $result;

    public function __construct()
    {
        $this->result = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
            'data' => array()
        );
    }

    public function send($subject, $body, $to, $toName)
    {
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();        //Devine o uso de SMTP no envio
            $mail->SMTPAuth = true; //Habilita a autenticação SMTP
            $mail->Username = EMAIL_USERNAME;
            $mail->Password = EMAIL_PASSWORD;
            $mail->SMTPAuth = EMAIL_SMTP_AUTH == 'true';
            $mail->SMTPSecure = EMAIL_SMTP_SECURE;
            // Informações específicadas pelo Google
            $mail->Host = EMAIL_HOST;
            $mail->Port = EMAIL_PORT;
            // Define o remetente
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            // Define o destinatário
            $mail->addAddress($to, $toName);
            // Conteúdo da mensagem
            $mail->isHTML(true);  // Seta o formato do e-mail para aceitar conteúdo HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            // Enviar
            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->result['message']['errors'][] = array('show' => false, 'value' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            $this->result['message']['hasError'] = true;
            return false;
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
}
