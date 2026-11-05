<?php

namespace CNS\BotToServer\Types;

/**
 * Defines the possible statuses of a payment transaction.
 */
enum TransactionStatusType: string
{
    /** Transaction has been created and is awaiting payment. */
    case PENDING = 'pending';

    /** Payment has been successfully completed. */
    case SUCCEEDED = 'succeeded';

    /** Payment processing has failed. */
    case FAILED = 'failed';

    /** Transaction has been canceled. */
    case CANCELED = 'canceled';

    /** Payment has been refunded. */
    case REFUNDED = 'refunded';
}