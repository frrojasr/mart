<?php

/**
 * Created by PhpStorm.
 * User: Miguel Leonice
 * Date: 13/03/2023
 * Time: 2:00
 */
namespace Modules\Mercadopago\Entities;

use Modules\Gateway\Entities\Gateway;
use Modules\Mercadopago\Scope\MpScope;


class MercadoPago extends Gateway
{
    protected $table = 'gateways';
    protected $appends = ['image_url'];

    protected static function booted()
    {
        static::addGlobalScope(new MpScope());
    }
}