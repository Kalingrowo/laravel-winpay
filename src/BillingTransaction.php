<?php

namespace MuHasan\LaravelWinpay;

interface BillingTransaction
{
    function getReff(): string;
    function getAmount(): int;
}
