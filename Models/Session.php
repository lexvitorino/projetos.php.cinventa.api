<?php

namespace Models;

use \Core\Model;

class Session extends Model
{
    private $result;

    public function __construct()
    {
        parent::__construct();

        $this->result = array(
            'message' => array(
                'hasError' => false,
                'errors' => array()
            ),
            'data' => array()
        );
    }

    public function auth(string $email, string $password): bool
    {
        $sql = "SELECT * 
                FROM   users 
                WHERE  email = :email";

        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email', $email);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $data = $sql->fetch();
            if (password_verify($password, $data['password'])) {
                $this->createJwt($data);
                return true;
            }
            return false;
        } else {
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

    public function getToken(): string
    {
        return $this->token;
    }

    public static function validateJwt($jwt)
    {
        global $config;
        $array = array();

        $jwt = str_replace("Bearer ", "", $jwt);
        $jwt_splits = explode('.', $jwt);

        if (count($jwt_splits) == 3) {
            $signature = hash_hmac("sha256", $jwt_splits[0] . "." . $jwt_splits[1], $config['jwt_secret_key'], true);
            $bsig = Session::base64url_encode($signature);

            if ($bsig == $jwt_splits[2]) {
                $array = json_decode(Session::base64url_decode($jwt_splits[1]));
            }
        }

        return (array) $array;
    }

    private function createJwt($user)
    {
        global $config;

        $header = json_encode(array("typ" => "JWT", "alg" => "HS256"));

        $payload = json_encode(array(
            "userId" => intVal($user['id']),
            "userName" => $user["name"],
            "userEmail" => $user["email"],
            "iat" => strtotime("+{$config['jwt_valid_per']} days"),
            "exp" => strtotime("+{$config['jwt_valid_per']} days")
        ));

        $hbase = $this->base64url_encode($header);
        $pbase = $this->base64url_encode($payload);

        $signature = hash_hmac("sha256", $hbase . "." . $pbase, $config['jwt_secret_key'], true);
        $bsig = $this->base64url_encode($signature);

        $jwt = $hbase . "." . $pbase . "." . $bsig;

        $this->result['data']['token'] = $jwt;
    }

    private static function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
