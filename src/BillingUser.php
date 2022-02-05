<?php

namespace MuHasan\LaravelWinpay;

interface BillingUser
{
    function getPhone(): string;
    function getName(): string;
    function getEmail(): ?string;
}
