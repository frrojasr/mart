<?php

/**
 * Created by PhpStorm.
 * User: Miguel Leonice
 * Date: 13/03/2023
 * Time: 2:43
 */

namespace Modules\Mercadopago\Scope;


use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;



class MpScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('alias', 'mercadopago');
    }
}