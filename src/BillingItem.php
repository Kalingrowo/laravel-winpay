<?php

namespace MuHasan\LaravelWinpay;

interface BillingItem
{
    function getBillItemName(): string;
    function getBillItemQty(): int;
    function getBillItemUnitPrice(): int;
    function getBillItemSku(): ?string;
    function getBillItemDesc(): ?string;
}
