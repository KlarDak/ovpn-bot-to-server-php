<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\UserException;
use CNS\OvpnBotToServer\Types\User;
use CNS\OvpnBotToServer\Utils\Utils;

class userAdapter {
    private IDBConnector $db;
    private int $user_id; 

    function __construct(IDBConnector $db, int $user_id)
    {
        $this->user_id = $user_id;
        $this->db = $db;
    }

    public function isExists() {
        $query = "SELECT id FROM users WHERE user_id = :user_id";
        $params = [":user_id" => $this->user_id];

        $exec = $this->db->fetchOne($query, $params);

        return ($exec === null) ? false : true;
    }

    public function getUserByID() {
        $query = "SELECT * FROM users WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];
        return new User($this->db->fetchOne($query, $params));
    }

    public function createUser(string $username) {
        try {
            $query = "INSERT INTO users (user_id, username, is_active) VALUES (:user_id, :username, 1)";
            $params = [
                ":user_id" => $this->user_id,
                ":username" => $username
            ];

            return $this->db->execute($query, $params);
        }
        catch (\Exception $e) {
            // Handle exception (e.g., log it)
            return false;
        }
    }

    public function updatePaymentInfo(int $expired_at, int $last_payment_at) : bool {
        try {
            if ($expired_at < time() || $last_payment_at < time()) {
                throw new UserException("Error has been detected: expired_at field must be greater than NOW()");
            }


            $query = "UPDATE users SET expired_at = :ea, last_payment_at = :lpa WHERE user_id = :user_id";
            $params = [":ea" => Utils::timeGenerator($expired_at), ":lpa" => Utils::timeGenerator($last_payment_at), ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch(UserException $error) {
            return false;
        }
    }

    public function updateUsername(string $username) : bool {
        try {
            $query = "UPDATE users SET username = :username WHERE user_id = :user_id";
            $params = [":username" => $username, ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch(UserException $error) {
            return false;
        }
    }

    public function updateLanguage(string $language) : bool {
        try {
            $query = "UPDATE users SET language = :language WHERE user_id = :user_id";
            $params = [":language" => $language, ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch (UserException $error) {
            return false;
        }
    }

    public function updateConfigsCount(int $configs_count) : bool {
        try {
            $query = "UPDATE users SET configs_count = configs_count + :cc WHERE user_id = :user_id";
            $params = [":cc" => $configs_count, ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch (UserException $error) {
            return false;
        }
    }

    public function updateActiveStatus(bool $is_active, ?int $disabled_at = null) {
        try {
            $query = "UPDATE users SET is_active = :ia, disabled_at = :da WHERE user_id = :user_id";
            $params = [":ia" => (int) $is_active, ":da" => Utils::timeGenerator($disabled_at), ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch (UserException $error) {
            return false;
        }
    }
}