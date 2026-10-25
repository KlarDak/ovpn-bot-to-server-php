<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\UserAlreadyExistsException;
use CNS\BotToServer\Types\User;
use CNS\BotToServer\Types\UserType;

/**
 * Provides methods for managing a single user record.
 *
 * Supports retrieving, creating, updating, tracking activity,
 * managing payment dates, blocking, unblocking, and soft-deleting users.
 *
 * Users are identified by a value and its corresponding UserType.
 */
class UserResource
{
    private IDBConnector $dbConnector;
    private int $id;
    private int|string $identifier;
    private UserType $userType;

    /**
     * Initializes the resource and resolves the user's internal ID.
     *
     * Uses zero as the internal ID when no matching user is found.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param int|string $identifier User identifier.
     * @param UserType $userType Type of identifier used to locate the user.
     */
    function __construct(IDBConnector $dbConnector, int|string $identifier, UserType $userType)
    {
        $this->dbConnector = $dbConnector;
        $this->identifier = $identifier;
        $this->userType = $userType;
        $this->id = $this->setIdByIdentifier($identifier, $userType);
    }

    /**
     * Checks whether the user was found during resource initialization.
     *
     * @return bool True if an internal user ID was resolved, false otherwise.
     */
    public function exists() : bool {
        return $this->id !== 0;
    }

    /**
     * Retrieves the user associated with the resolved internal ID.
     *
     * @return User|null The user instance, or null if not found.
     */
    public function get() : ?User {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $params = [':id' => $this->id];
        $result = $this->dbConnector->fetchOne($query, $params);

        return ($result !== null) ? new User($result) : null;
    }

    /**
     * Creates a new user using the configured identifier type.
     *
     * @param string $language User's preferred language.
     * @param string|null $username Optional username.
     *
     * @return int ID of the newly created user.
     *
     * @throws UserAlreadyExistsException If a user with the identifier already exists.
     */
    public function create(string $language, ?string $username = null) : int {
        if ($this->exists()) {
            throw new UserAlreadyExistsException($this->identifier);
        }

        $query = "INSERT INTO users ({$this->userType->value}, language, username) VALUES (:identifier, :language, :username)";
        $params = [
            ':identifier' => $this->identifier,
            ':language' => $language,
            ':username' => $username
        ];

        return $this->dbConnector->insert($query, $params);
    }

    /**
     * Updates selected profile fields of a non-dropped user.
     *
     * Only non-null parameters are included in the update.
     * Returns false when no fields are provided.
     *
     * @param string|null $username New username.
     * @param string|null $email New email address.
     * @param string|null $language New preferred language.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
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

    /**
     * Updates the user's last payment date and subscription expiration date.
     *
     * Also attempts to refresh the user's last activity timestamp
     * before updating the payment information.
     *
     * @param \DateTimeImmutable $paymentDate Date and time of the payment.
     * @param \DateTimeImmutable $expiryDate Subscription expiration date and time.
     *
     * @return bool True if the payment update succeeds, false otherwise.
     */
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

    /**
     * Refreshes the user's last activity timestamp.
     *
     * Only non-dropped users can be updated.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function touch() : bool {
        $query = "UPDATE users SET last_activity_at = NOW() WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Disables a non-dropped user and records the disabling timestamp.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function block() : bool {
        $query = "UPDATE users SET is_active = 0, disabled_at = NOW() WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Enables a non-dropped user and clears the disabling timestamp.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function unblock() : bool {
        $query = "UPDATE users SET is_active = 1, disabled_at = NULL WHERE id = :id AND is_dropped = 0";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Soft-deletes a user by marking the record as dropped and inactive.
     *
     * Records the disabling timestamp without physically deleting
     * the user from the database.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function drop() : bool {
        $query = "UPDATE users SET is_active = 0, disabled_at = NOW(), is_dropped = 1 WHERE id = :id";
        $params = [':id' => $this->id];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Resolves the internal user ID using the specified identifier.
     *
     * The database column is selected according to UserType.
     *
     * @param int|string $identifier User identifier.
     * @param UserType $userType Type of identifier used for lookup.
     *
     * @return int Internal user ID, or zero if no matching user exists.
     */
    private function setIdByIdentifier(int|string $identifier, UserType $userType) {
        $query = "SELECT id FROM users WHERE $userType->value = :identifier LIMIT 1";

        $params = [':identifier' => $identifier];
        $result = $this->dbConnector->fetchOne($query, $params);

        return (int) ($result['id'] ?? 0);
    }
}