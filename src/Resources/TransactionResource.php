<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Transaction;
use CNS\BotToServer\Types\TransactionStatusType;
use DateTimeImmutable;

/**
 * Provides methods for managing payment transactions.
 *
 * Supports creating, retrieving, updating, and changing
 * transaction statuses through the database.
 */
final class TransactionResource
{
    /**
     * Initializes the transaction resource.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     */
    function __construct(private IDBConnector $dbConnector) {}

    /**
     * Creates a new payment transaction.
     *
     * Optional metadata is serialized to JSON before being stored.
     *
     * @param int $userId ID of the user associated with the transaction.
     * @param string $provider Payment provider identifier.
     * @param string $amount Transaction amount represented as a string.
     * @param string $currency Currency code (RUB by default).
     * @param string|null $providerPaymentId Payment identifier assigned by the provider.
     * @param int|null $durationDays Optional subscription duration in days.
     * @param string|null $description Optional transaction description.
     * @param array|null $metadata Optional transaction metadata.
     *
     * @return int ID of the newly created transaction.
     *
     * @throws \JsonException If metadata cannot be encoded as JSON.
     */
    public function create(
        int $userId,
        string $provider,
        string $amount,
        string $currency = 'RUB',
        ?string $providerPaymentId = null,
        ?int $durationDays = null,
        ?string $description = null,
        ?array $metadata = null
    ): int {
        return $this->dbConnector->insert(
            'INSERT INTO transactions (
                user_id,
                provider,
                provider_payment_id,
                amount,
                currency,
                duration_days,
                description,
                metadata
            ) VALUES (
                :user_id,
                :provider,
                :provider_payment_id,
                :amount,
                :currency,
                :duration_days,
                :description,
                :metadata
            )',
            [
                'user_id' => $userId,
                'provider' => $provider,
                'provider_payment_id' => $providerPaymentId,
                'amount' => $amount,
                'currency' => $currency,
                'duration_days' => $durationDays,
                'description' => $description,
                'metadata' => $metadata !== null
                    ? json_encode($metadata, JSON_THROW_ON_ERROR)
                    : null,
            ]
        );
    }

    /**
     * Retrieves a transaction by its ID.
     *
     * @param int $id Transaction ID.
     *
     * @return Transaction|null The transaction instance, or null if not found.
     */
    public function get(int $id) : ?Transaction{
        $resultTransaction = $this->dbConnector->fetchOne(
            "SELECT * FROM transactions WHERE id = :id",
            [":id" => $id]
        );

        return $resultTransaction ? new Transaction($resultTransaction) : null;
    }

    /**
     * Retrieves a transaction by its payment provider and payment identifier.
     *
     * @param string $provider Payment provider identifier.
     * @param string $provider_payment_id Payment identifier assigned by the provider.
     *
     * @return Transaction|null The matching transaction, or null if not found.
     */
    public function getByProviderPayment(string $provider, string $provider_payment_id) : ?Transaction {
        $resultTransaction = $this->dbConnector->fetchOne(
            "SELECT * FROM transactions WHERE provider = :provider AND provider_payment_id = :provider_payment_id",
            [":provider" => $provider, "provider_payment_id" => $provider_payment_id]
        );

        return $resultTransaction ? new Transaction($resultTransaction) : null;
    }

    /**
     * Retrieves all transactions associated with a user.
     *
     * @param int $user_id ID of the user.
     *
     * @return Transaction[] List of transaction instances.
     */
    public function getByUserId(int $user_id) : array {
        $resultTransactions = $this->dbConnector->fetchAll(
            "SELECT * FROM transactions WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );

        return array_map(fn($transaction) => new Transaction($transaction), $resultTransactions);
    }

    /**
     * Updates selected fields of an existing transaction.
     *
     * Only non-null parameters are included in the update.
     * Returns false when no fields are provided.
     * Metadata is serialized to JSON before being stored.
     *
     * @param int $id Transaction ID.
     * @param string|null $provider_payment_id New provider payment identifier.
     * @param string|null $description New transaction description.
     * @param array|null $metadata New transaction metadata.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     *
     * @throws \JsonException If metadata cannot be encoded as JSON.
     */
    public function update(
        int $id,
        ?string $provider_payment_id = null,
        ?string $description = null,
        ?array $metadata = null
    ): bool {
        $fields = [];
        $params = ['id' => $id];

        if ($provider_payment_id !== null) {
            $fields[] = 'provider_payment_id = :provider_payment_id';
            $params['provider_payment_id'] = $provider_payment_id;
        }

        if ($description !== null) {
            $fields[] = 'description = :description';
            $params['description'] = $description;
        }

        if ($metadata !== null) {
            $fields[] = 'metadata = :metadata';
            $params['metadata'] = json_encode(
                $metadata,
                JSON_THROW_ON_ERROR
            );
        }

        if ($fields === []) {
            return false;
        }

        return $this->dbConnector->execute(
            'UPDATE transactions SET '
            . implode(', ', $fields)
            . ' WHERE id = :id',
            $params
        );
    }

    /**
     * Marks a transaction as successfully completed.
     *
     * Sets the status to SUCCEEDED and records the payment timestamp.
     *
     * @param int $id Transaction ID.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function success(int $id) : bool {
        return $this->dbConnector->execute(
            "UPDATE transactions SET status = :status, paid_at = :paid_at WHERE id = :id",
            [":id" => $id, ":status" => TransactionStatusType::SUCCEEDED->value, ":paid_at" => (new DateTimeImmutable("now"))->format("Y-m-d H:i:s")]
        );
    }

    /**
     * Marks a transaction as canceled.
     *
     * @param int $id Transaction ID.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function cancel(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::CANCELED);
    }

    /**
     * Marks a transaction as failed.
     *
     * @param int $id Transaction ID.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function failed(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::FAILED);
    }

    /**
     * Marks a transaction as refunded.
     *
     * @param int $id Transaction ID.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function refunded(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::REFUNDED);
    }

    /**
     * Updates the status of a transaction.
     *
     * @param int $id Transaction ID.
     * @param TransactionStatusType $statusType New transaction status.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    private function updateStatus(int $id, TransactionStatusType $statusType) {
        return $this->dbConnector->execute(
            "UPDATE transactions SET status = :status WHERE id = :id",
            [":id" => $id, ":status" => $statusType->value]
        );
    }
}