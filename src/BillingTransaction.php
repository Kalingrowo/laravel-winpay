<?php

namespace MuHasan\LaravelWinpay;

use DateTime;

interface BillingTransaction
{
    function getBillTransactionEndAt(): \DateTime;
    function getBillTransactionReff(): string;
    function getBillTransactionAmount(): int;
}
