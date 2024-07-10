<?php

/**
 * Created by PhpStorm.
 * User: Miguel Leonice
 * Date: 13/03/2023
 * Time: 3:33
 */

namespace Modules\Mercadopago\Views;
use App\Models\Order;
use Modules\Gateway\Contracts\PaymentViewInterface;
use Modules\Gateway\Facades\GatewayHelper;
use Modules\Mercadopago\Entities\MercadoPago;


class MercadopagoView implements PaymentViewInterface
{
    public static function paymentView($key)
    {


        $helper = GatewayHelper::getInstance();
        $datos = $helper->getPurchaseData($key);
        $order = Order::where('id', $datos->id)->first();
        $items = $order->orderDetails->groupBy('vendor_id');


        try {
            $mp = MercadoPago::firstWhere('alias', 'mercadopago')->data;
            //dd($helper->getPurchaseData($key));
            return view('mercadopago::pay', [
                'instruction' => $mp->instruction,
                'purchaseData' => $datos,
                'dataMp' => $mp,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            //dd($e);
            return back()->withErrors(['error' => __('Purchase data not found.')]);
        }
    }
}