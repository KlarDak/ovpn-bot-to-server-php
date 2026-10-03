<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\UserAlreadyExistsException;
use CNS\BotToServer\Exceptions\UserNotFoundException;
use CNS\BotToServer\Types\User;
use CNS\BotToServer\Types\AccessType;

class UserResource {
    private IDBConnector $dbConnector;
    private int $id;
    function __construct(IDBConnector $dbConnector, int|string $identifier, AccessType $accessType)
    {
        $this->dbConnector = $dbConnector;
        $this->id = $this->setIdByIdentifier($identifier, $accessType);
    }

    public function exists() : bool {
        return $this->id !== 0;
    }

    public function get() : User {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $params = [':id' => $this->id];
        $result = $this->dbConnector->fetchOne($query, $params);
        
        return $result ? new User($result) : throw new UserNotFoundException("User with ID {$this->id} not found.");
    }

    public function create(AccessType $userType, int|string $identifier, string $language, ?string $username = null) : bool {
        if ($this->exists()) {
            throw new UserAlreadyExistsException($identifier);
        }
    
        $query = "INSERT INTO users ($userType->value, language, username) VALUES (:identifier, :language, :username)";
        $params = [
            ':identifier' => $identifier,
            ':language' => $language,
            ':username' => $username
        ];

        return $this->dbConnector->execute($query, $params);
    }

    public function update(
        ?string $username = null,
        ?string $email = null,
        ?string $language = null,
    ): bool {
        $fields = [];
        $params = [':id' => $this->id];

        if ($username !== null) {
            $fields[] = 'username = :username';
            $params[':username'] = $username;
        }

        if ($email !== null) {
            $fields[] = 'email = :email';
            $params[':email'] = $email;
        }

        if ($language !== null) {
            $fields[] = 'language = :language';
            $params[':language'] = $language;
        }

        if ($fields === []) {
            return false;
        }

        $query = sprintf(
            'UPDATE users SET %s WHERE id = :id AND is_dropped = 0',
            implode(', ', $fields)
        );

        return $this->dbConnector->execute($query, $params);
    }

    public function updatePayment(\DateTimeImmutable $paymentDate, \DateTimeImmutable $expiryDate) : bool {
        $this->touch();

        $params = [
            ':id' => $this->id,
            ':payment_date' => $paymentDate->format('Y-m-d H:i:s'),
            ':expiry_date' => $expiryDate->format('Y-m-d H:i:s')
        ];

        $query = "UPDATE users SET expired_at = :expiry_date, last_payment_at = :payment_date WHERE id = :id AND is_dropped = 0";
        return $this->dbConnector->execute($query, $params);
    }
    
    public function touch() : bool {
        $query = "UPDATE users SET last_activity_at = NOW() WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    public function block() : bool {
        $query = "UPDATE users SET is_active = 0 AND disabled_at = NOW() WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    public function unblock() : bool {
        $query = "UPDATE users SET is_active = 1 AND disabled_at = NULL WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    public function drop() : bool {
        $query = "UPDATE users SET is_active = 0, disabled_at = NOW(), is_dropped = 1 WHERE id = :id";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    private function setIdByIdentifier(int|string $identifier, AccessType $accessType) {
        $query = "SELECT id FROM users WHERE $accessType->value = :identifier LIMIT 1";

        $params = [':identifier' => $identifier];
        $result = $this->dbConnector->fetchOne($query, $params);
        
        return (int) $result['id'] ?? 0;
    }
}