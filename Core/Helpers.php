<?php

namespace Core;

use DateTime;

class Helpers
{
    /**
     * @param string $email
     * @return bool
     */
    public static function is_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * ##################
     * ###   STRING   ###
     * ##################
     */

    /**
     * @param string $string
     * @return string
     */
    public static function str_slug(string $string): string
    {
        $string = filter_var(mb_strtolower($string), FILTER_SANITIZE_STRIPPED);
        $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        $slug = str_replace(
            ["-----", "----", "---", "--"],
            "-",
            str_replace(
                " ",
                "-",
                trim(strtr(utf8_decode($string), utf8_decode($formats), $replace))
            )
        );
        return $slug;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function str_studly_case(string $string): string
    {
        $string = self::str_slug($string);
        $studlyCase = str_replace(
            " ",
            "",
            mb_convert_case(str_replace("-", " ", $string), MB_CASE_TITLE)
        );

        return $studlyCase;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function str_camel_case(string $string): string
    {
        return lcfirst(self::str_studly_case($string));
    }

    /**
     * @param string $string
     * @return string
     */
    public static function str_title(string $string): string
    {
        return mb_convert_case(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS), MB_CASE_TITLE);
    }

    /**
     * @param string $text
     * @return string
     */
    public static function str_textarea(string $text): string
    {
        $text = filter_var($text, FILTER_SANITIZE_STRIPPED);
        $arrayReplace = ["&#10;", "&#10;&#10;", "&#10;&#10;&#10;", "&#10;&#10;&#10;&#10;", "&#10;&#10;&#10;&#10;&#10;"];
        return "<p>" . str_replace($arrayReplace, "</p><p>", $text) . "</p>";
    }

    /**
     * @param string $string
     * @param int $limit
     * @param string $pointer
     * @return string
     */
    public static function str_limit_words(string $string, int $limit, string $pointer = "..."): string
    {
        $string = trim(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
        $arrWords = explode(" ", $string);
        $numWords = count($arrWords);

        if ($numWords < $limit) {
            return $string;
        }

        $words = implode(" ", array_slice($arrWords, 0, $limit));
        return "{$words}{$pointer}";
    }

    /**
     * @param string $string
     * @param int $limit
     * @param string $pointer
     * @return string
     */
    public static function str_limit_chars(string $string, int $limit, string $pointer = "..."): string
    {
        $string = trim(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
        if (mb_strlen($string) <= $limit) {
            return $string;
        }

        $chars = mb_substr($string, 0, mb_strrpos(mb_substr($string, 0, $limit), " "));
        return "{$chars}{$pointer}";
    }

    /**
     * @param string $price
     * @return string
     */
    public static function str_price(?string $price): string
    {
        return number_format((!empty($price) ? $price : 0), 2, ",", ".");
    }

    /**
     * @param string|null $search
     * @return string
     */
    public static function str_search(?string $search): string
    {
        if (!$search) {
            return "all";
        }

        $search = preg_replace("/[^a-z0-9A-Z\@\ ]/", "", $search);
        return (!empty($search) ? $search : "all");
    }

    /**
     * @return \Source\Core\Session
     */
    public static function session(): \Core\Session
    {
        return new \Core\Session();
    }

    /**
     * ################
     * ###   DATE   ###
     * ################
     */

    /**
     * @param string $date
     * @param string $format
     * @return string
     * @throws Exception
     */
    public static function date_fmt(?string $date, string $format = "d/m/Y H\hi"): string
    {
        $date = (empty($date) ? "now" : $date);
        return (new DateTime($date))->format($format);
    }

    /**
     * @param string|null $date
     * @return string|null
     */
    public static function date_fmt_back(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        if (strpos($date, " ")) {
            $date = explode(" ", $date);
            return implode("-", array_reverse(explode("/", $date[0]))) . " " . $date[1];
        }

        return implode("-", array_reverse(explode("/", $date)));
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function passwd_verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * ###################
     * ###   REQUEST   ###
     * ###################
     */

    /**
     * @return string
     */
    public static function csrf_input(): string
    {
        $session = new \Core\Session();
        $session->csrf();
        return "<input type='hidden' name='csrf' value='" . ($session->csrf_token ?? "") . "'/>";
    }

    /**
     * @param $request
     * @return bool
     */
    public static function csrf_verify($request): bool
    {
        $session = new \Core\Session();
        if (empty($session->csrf_token) || empty($request['csrf']) || $request['csrf'] != $session->csrf_token) {
            return false;
        }
        return true;
    }

    /**
     * @return null|string
     */
    public static function flash(): ?string
    {
        $session = new \Core\Session();
        if ($flash = $session->flash()) {
            return $flash;
        }
        return null;
    }

    /**
     * @param string $key
     * @param int $limit
     * @param int $seconds
     * @return bool
     */
    public static function request_limit(string $key, int $limit = 5, int $seconds = 60): bool
    {
        if (SESSION_CONTROL_REQUEST == 0) {
            return false;
        }

        $session = new \Core\Session();
        if ($session->has($key) && $session->$key->time >= time() && $session->$key->requests < $limit) {
            $session->set($key, [
                "time" => time() + $seconds,
                "requests" => $session->$key->requests + 1
            ]);
            return false;
        }

        if ($session->has($key) && $session->$key->time >= time() && $session->$key->requests >= $limit) {
            return true;
        }

        $session->set($key, [
            "time" => time() + $seconds,
            "requests" => 1
        ]);

        return false;
    }

    /**
     * @param string $field
     * @param string $value
     * @return bool
     */
    public static function request_repeat(string $field, string $value): bool
    {
        $session = new \Core\Session();
        if ($session->has($field) && $session->$field == $value) {
            return true;
        }

        $session->set($field, $value);
        return false;
    }
}
