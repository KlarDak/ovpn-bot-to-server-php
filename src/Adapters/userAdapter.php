<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\UserException;
use CNS\OvpnBotToServer\Types\User;
use CNS\OvpnBotToServer\Utils\Utils;

class userAdapter {
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private IDBConnector $db;
    /**
     * UserID of user in Telegram
     * 
     * @var int
     */
    private int $user_id; 

    /**
     * Constructor of class UserAdapter
     * 
     * @param IDBConnector $db Database object variable
     * @param int $user_id UserID of user in Telegram
     */
    function __construct(IDBConnector $db, int $user_id)
    {
        $this->user_id = $user_id;
        $this->db = $db;
    }

    /**
     * Check if user exists
     * 
     * @return bool 
     * @throws UserException
     */
    public function isExists() : bool
    {
        try {
            $query = "SELECT id FROM users WHERE user_id = :user_id";
            $params = [":user_id" => $this->user_id];

            $exec = $this->db->fetchOne($query, $params);

            return ($exec === null) ? false : true;
        }
        catch (UserException $error) {
            return false;
        }
    }

    /**
     * Get user information
     * 
     * @return User
     * @throws UserException
     */
    public function getUserByID() {
        try {
            $query = "SELECT * FROM users WHERE user_id = :user_id AND is_dropped = 0";
            $params = [":user_id" => $this->user_id];
            return new User($this->db->fetchOne($query, $params));
        }
        catch(UserException $error) {
            return false;
        }
    }

    /**
     * Create new user
     * 
     * @param string $username Username of user in Telegram
     * @param string $language Language of user
     * 
     * @return bool
     * @throws UserException
     */
    public function createUser(string $username, string $language) : bool
    {
        try {
            $query = "INSERT INTO users (user_id, username, language, is_active) VALUES (:user_id, :username, :language, 1)";
            $params = [
                ":user_id" => $this->user_id,
                ":username" => $username,
                ":language" => $language
            ];

            return $this->db->execute($query, $params);
        }
        catch (UserException $e) {
            return false;
        }
    }

    /**
     * Update last payment info
     * 
     * @param int $expired_at UNIX time of new payment expiration date
     * @param int $last_payment_at UNIX time of last payment
     * 
     * @return bool
     * @throws UserException
     */
    public function updatePaymentInfo(int $expired_at, int $last_payment_at) : bool 
    {
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

    /**
     * Update username of user
     * 
     * @param string $username New username of user
     * 
     * @return bool
     * @throws UserException
     */
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

    /**
     * Update language of user
     * 
     * @param string $language New language of user
     * 
     * @return bool
     * @throws UserException
     */
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

    /**
     * Update (increase or decrease) user config files count
     * 
     * @param int $configs_count Positive of negative number
     * 
     * @return bool
     * @throws UserException
     */
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

    /**
     * Update active status of user account
     * 
     * @param bool $is_active Is account active
     * @param int $disabled_at Account lock UNIX time
     * 
     * @return bool
     * @throws UserException 
     */
    public function updateActiveStatus(bool $is_active, ?int $disabled_at = null) {
        try {
            $query = "UPDATE users SET is_active = :ia, disabled_at = :da WHERE user_id = :user_id";
            $params = [":ia" => (int) $is_active, ":da" => (!is_null($disabled_at)) ? Utils::timeGenerator($disabled_at) : null, ":user_id" => $this->user_id];

            return $this->db->execute($query, $params);
        }
        catch (UserException $error) {
            return false;
        }
    }
}