<?php

namespace MuHasan\LaravelWinpay;

interface BillingItem
{
    function getName(): string;
    function getQty(): int;
    function getUnitPrice(): int;
    function getSku(): ?string;
    function getDesc(): ?string;
}
