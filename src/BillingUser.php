<?php

namespace MuHasan\LaravelWinpay;

interface BillingUser
{
    function getBillUserPhone(): string;
    function getBillUserName(): string;
    function getBillUserEmail(): ?string;
}
