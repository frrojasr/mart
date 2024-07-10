<?php

/**
 * Created by PhpStorm.
 * User: Miguel Leonice
 * Date: 13/03/2023
 * Time: 2:48
 */

namespace Modules\Mercadopago\Entities;

use Modules\Gateway\Entities\GatewayBody;

class MpBody extends GatewayBody
{
    public $public_key;
    public $token;
    public $instruction;
    public $status;



    public function __construct($request)
    {
        $this->public_key = $request->public_key;
        $this->token = $request->token;
        $this->instruction = $request->instruction;
        $this->status = $request->status;
    }
}