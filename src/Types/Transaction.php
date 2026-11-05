<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

/**
 * Represents a payment transaction record.
 *
 * Contains payment provider information, transaction amount,
 * currency, status, optional subscription duration, metadata,
 * and transaction timestamps.
 */
class Transaction
{
    /** @var int Internal transaction ID. */
    public int $id;

    /** @var int ID of the user associated with the transaction. */
    public int $user_id;

    /** @var string Payment provider identifier. */
    public string $provider;

    /** @var string|null Payment identifier assigned by the provider. */
    public ?string $provider_payment_id;

    /** @var string Transaction amount represented as a string. */
    public string $amount;

    /** @var int|null Subscription duration in days, if applicable. */
    public ?int $duration_days;

    /** @var string Transaction currency code. */
    public string $currency;

    /** @var string Current transaction status. */
    public string $status;

    /** @var string|null Optional transaction description. */
    public ?string $description;

    /** @var array|null Optional transaction metadata decoded from JSON. */
    public ?array $metadata;

    /** @var DateTimeImmutable Transaction creation date and time. */
    public DateTimeImmutable $created_at;

    /** @var DateTimeImmutable|null Last transaction update date and time. */
    public ?DateTimeImmutable $updated_at;

    /** @var DateTimeImmutable|null Date and time when the payment was completed. */
    public ?DateTimeImmutable $paid_at;

    /**
     * Initializes a transaction instance from a database record.
     *
     * Maps database fields to object properties, decodes JSON
     * metadata into an associative array, and converts timestamps
     * into DateTimeImmutable instances.
     *
     * @param array<string, mixed> $transactionData Associative array containing transaction data.
     *
     * @throws \Exception If a timestamp cannot be parsed.
     */
    function __construct(array $transactionData)
    {
        $this->id = $transactionData["id"];
        $this->user_id = $transactionData["user_id"];
        $this->provider = $transactionData["provider"];
        $this->provider_payment_id = isset($transactionData["provider_payment_id"]) ? $transactionData["provider_payment_id"] : null;
        $this->amount = $transactionData["amount"];
        $this->duration_days = isset($transactionData["duration_days"]) ? (int) $transactionData["duration_days"] : null;
        $this->currency = $transactionData["currency"];
        $this->status = $transactionData["status"];
        $this->description = isset($transactionData["description"]) ? $transactionData["description"] : null;
        $this->metadata = isset($transactionData["metadata"]) ? json_decode($transactionData["metadata"], true) : null;
        $this->created_at = new DateTimeImmutable($transactionData["created_at"]);
        $this->updated_at = isset($transactionData["updated_at"]) ? new DateTimeImmutable($transactionData["updated_at"]) : null;
        $this->paid_at = isset($transactionData["paid_at"]) ? new DateTimeImmutable($transactionData["paid_at"]) : null;
    }
}