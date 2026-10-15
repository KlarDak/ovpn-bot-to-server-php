<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

class Transaction {
    public int $id;
    public int $user_id;
    public string $provider;
    public ?string $provider_payment_id;
    public string $amount;
    public ?int $duration_days;
    public string $currency;
    public string $status;
    public ?string $description;
    public ?array $metadata;
    public DateTimeImmutable $created_at;
    public ?DateTimeImmutable $updated_at;
    public ?DateTimeImmutable $paid_at;

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