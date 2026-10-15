<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Transaction;
use CNS\BotToServer\Types\TransactionStatusType;
use DateTimeImmutable;

final class TransactionResource {
    function __construct(private IDBConnector $dbConnector) {}

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

    public function get(int $id) : ?Transaction{
        $resultTransaction = $this->dbConnector->fetchOne(
            "SELECT * FROM transactions WHERE id = :id",
            [":id" => $id]
        );

        return $resultTransaction ? new Transaction($resultTransaction) : null;
    }

    public function getByProviderPayment(string $provider, string $provider_payment_id) : ?Transaction {
        $resultTransaction = $this->dbConnector->fetchOne(
            "SELECT * FROM transactions WHERE provider = :provider AND provider_payment_id = :provider_payment_id",
            [":provider" => $provider, "provider_payment_id" => $provider_payment_id]
        );

        return $resultTransaction ? new Transaction($resultTransaction) : null;
    }

    public function getByUserId(int $user_id) : array {
        $resultTransactions = $this->dbConnector->fetchAll(
            "SELECT * FROM transactions WHERE user_id = :user_id",
            [":user_id" => $user_id]
        );

        return array_map(fn($transaction) => new Transaction($transaction), $resultTransactions);
    }

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

    public function success(int $id) : bool {
        return $this->dbConnector->execute(
            "UPDATE transactions SET status = :status, paid_at = :paid_at WHERE id = :id",
            [":id" => $id, ":status" => TransactionStatusType::SUCCEEDED->value, ":paid_at" => (new DateTimeImmutable("now"))->format("Y-m-d H:i:s")]
        );
    }

    public function cancel(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::CANCELED);
    }

    public function failed(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::FAILED);
    }

    public function refunded(int $id) : bool {
        return $this->updateStatus($id, TransactionStatusType::REFUNDED);
    }

    private function updateStatus(int $id, TransactionStatusType $statusType) {
        return $this->dbConnector->execute(
            "UPDATE transactions SET status = :status WHERE id = :id",
            [":id" => $id, ":status" => $statusType->value]
        );
    }
}