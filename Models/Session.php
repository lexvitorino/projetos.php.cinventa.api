<?php

namespace Models;

use \Core\Model;

class Session extends Model
{
    private $token;

    public function __construct()
    {
        parent::__construct();

        $this->token = "";
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
            if (password_verify($password, $data['password_hash'])) {

                $data['avatar'] = array('url' => '');
                if (!is_null($data['avatar_id']) && intVal($data['avatar_id']) > 0) {
                    $file = new File($data['subscriber_id']);
                    if ($file->getById($data['avatar_id'])) {
                        $data['avatar'] = array(
                            'url' => $file->single()['url'],
                        );
                    }
                }

                $data['subscriber_email'] = '';
                if (!is_null($data['subscriber_id']) && intVal($data['subscriber_id']) > 0) {
                    $subscriber = new Subscriber();
                    if ($file->getById($data['subscriber_id'])) {
                        $data['subscriber_email'] = $subscriber->single()['email'];
                    }
                }

                $this->createJwt($data);
                return true;
            }
            return false;
        } else {
            return false;
        }
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
            "subscriberId" => intVal($user["subscriber_id"]),
            "subscriberEmail" => $user["subscriber_email"],
            "userId" => intVal($user['id']),
            "userName" => $user["name"],
            "userEmail" => $user["email"],
            "userAvatar" => $user["avatar"],
            "userPermission" => $user["permission"],
            "iat" => strtotime("+{$config['jwt_valid_per']} days"),
            "exp" => strtotime("+{$config['jwt_valid_per']} days")
        ));

        $hbase = $this->base64url_encode($header);
        $pbase = $this->base64url_encode($payload);

        $signature = hash_hmac("sha256", $hbase . "." . $pbase, $config['jwt_secret_key'], true);
        $bsig = $this->base64url_encode($signature);

        $jwt = $hbase . "." . $pbase . "." . $bsig;

        $this->token = $jwt;
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
