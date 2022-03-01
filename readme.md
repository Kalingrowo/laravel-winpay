# Laravel-WinPay

A non-official package that help you to implements WinPay Payment Gateway (winpay.id) into your Laravel applications

### Installation

Install the package via composer :

```bash
$ composer require mu-hasan/laravel-winpay "^0.0.1"
```

Add this lines into your `.env` file and fill with your WinPay credentials :

```
# ...
WINPAY_HOST=putWinpayHostHere
WINPAY_PK1=putWinpayPrivateKey1Here
WINPAY_PK2=putWinpayPrivateKey2Here
WINPAY_MK=putWinpayMerchantKeyHere
WINPAY_LISTENER=putWinpayListenerPathHere
```


#### Laravel

Please register the service provider :

```php
// config/app.php
'Providers' => [
    // ...
    /*
     * Package Service Providers...
     */
    MuHasan\LaravelWinpay\WinpayServiceProvider::class,
    // ...
]
```

You could use the facade by add this line :

```php
// config/app.php
'aliases' => [
    // ...
    'Winpay' => MuHasan\LaravelWinpay\WinpayFacade::class,
];
```

Please publish the config file to define your WinPay credentials :

```bash
$ php artisan vendor:publish --provider="MuHasan\LaravelWinpay\WinpayServiceProvider"
```

#### Lumen

Please add this line to the `bootstrap/app.php` file
```php
$app->configure('laravel-winpay');
//...
$app->register(MuHasan\LaravelWinpay\WinpayServiceProvider::class);
```

You could get the config file from this [laravel-winpay.php](https://github.com/mu-hasan/laravel-winpay/blob/master/resources/config/laravel-winpay.php). Then copy it into `config/laravel-winpay.php`


### Send Request to WinPay

For now, this package only support `getToolbar` and `getPaymentCode`.

#### getToolbar
From the [documentation](https://winpayapi.docs.apiary.io/#reference/0/api-daftar-payment-channel), this function will be return list of payment channel. You can use like this:

```php
winpay()->getToolbar();
// OR
Winpay::getToolbar();
```

#### getPaymentCode
From the [documentation](https://winpayapi.docs.apiary.io/#reference/0/api-payment-code), this function will be return payment code of choosen payment channel and transaction details. You can use like this:

```php
winpay()->getPaymentCode($paymentChannel, $transaction, $user, $items);
// OR
Winpay::getToolbar($paymentChannel, $transaction, $user, $items);
```

You must passing `$paymentChannel` parameter from one of `getToolbar()` response.

The `$transaction` parameter you must passing the model that implements `MuHasan\LaravelWinpay\BillingTransaction` interface and define the `getBillTransactionEndAt()`, `getBillTransactionReff()`, and `getBillTransactionAmount()` functions into it.

```php
class FooTransaction extends Model implements BillingTransaction
{
    //...
    public function getBillTransactionEndAt(): \DateTime
    {
        return $this->reff;
    }

    public function getBillTransactionReff(): string
    {
        return $this->reff;
    }

    public function getBillTransactionAmount(): int
    {
        return $this->total;
    }
    //...
}
```

The `$user` parameter you must passing the model that implements `MuHasan\LaravelWinpay\BillingUser` interface and define the `getgetBillUserName()`, `getgetBillUserPhone()`, and `getgetBillUserEmail()` functions into it.

```php
class FooUser extends Model implements BillingUser
{
    //...
    public function getBillUserName(): string
    {
        return $this->name;
    }

    public function getBillUserPhone(): string
    {
        return $this->phone;
    }

    // nullable
    public function getBillUserEmail(): ?string
    {
        return $this->email;
        // OR
        // return null;
    }
    //...
}
```

The `$items` parameter you must passing array of the model that implements `MuHasan\LaravelWinpay\BillingItem` interface and define the `getBillItemName()`, `getBillItemQty()`, `getBillItemUnitPrice()`, `getBillItemSku()`, and `getBillItemDesc()` functions into it.

```php
class FooItem extends Model implements BillingItem
{
    //...
    public function getBillItemName(): string
    {
        return $this->name;
    }

    public function getBillItemQty(): int
    {
        return $this->qty;
    }

    public function getBillItemUnitPrice(): int
    {
        return $this->amount;
    }

    // nullable
    public function getBillItemSku(): ?string
    {
        return $this->short_id;
        // OR
        // return null;
    }

    // nullable
    public function getBillItemDesc(): ?string
    {
        return $this->note;
        // OR
        // return null;
    }
    //...
}
```
