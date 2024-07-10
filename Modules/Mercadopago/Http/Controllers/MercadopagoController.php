<?php

namespace Modules\Mercadopago\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Addons\Entities\Addon;
use Modules\Mercadopago\Entities\MercadoPago;
use Modules\Mercadopago\Entities\MpBody;
//use Modules\Mercadopago as MP;
use MercadoPago as MP;

class MercadopagoController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('mercadopago::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('mercadopago::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $paypalBody = new MpBody($request);

        MercadoPago::updateOrCreate(
            ['alias' => config('mercadopago.alias')],
            [
                'name' => config('mercadopago.name'),
                'instruction' => $request->instruction,
                'status' => $request->status,
                //'sandbox' => $request->sandbox,
                'image' => 'thumbnail.png',
                'data' => json_encode($paypalBody)
            ]
        );

        return back()->with(['AddonStatus' => 'success', 'AddonMessage' => __('Mercadopago settings updated.')]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('mercadopago::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Request $request)
    {
        //dd('as');
        //return view('mercadopago::edit');

        try {
            $module = MercadoPago::first()->data;
        } catch (\Exception $e) {
            $module = null;
        }

        $addon = Addon::findOrFail('mercadopago');

        return response()->json(
            [
                'html' => view('gateway::partial.form', compact('module', 'addon'))->render(),
                'status' => true
            ],
            200
        );
    }

    public function ipn(Request $request)
    {
        \Log::info('ipn');
        $mp = MercadoPago::firstWhere('alias', 'mercadopago')->data;
        MP\SDK::setAccessToken($mp->token);

        $merchant_order = null;
        \Log::info($request->get("id"));
        \Log::info($request->get("topic"));
        switch($request->get("topic")) {
            case "payment":
                $payment = MP\Payment::find_by_id($request->get("id"));
                //\Log::info($payment);
                // Get the payment and the corresponding merchant_order reported by the IPN.
                $merchant_order = MP\MerchantOrder::find_by_id($payment->order->id);
                break;
            case "merchant_order":
                $merchant_order = MP\MerchantOrder::find_by_id($request->get("id"));
                break;
        }

        if (!empty($merchant_order)) {
            \Log::info($merchant_order->payments);
            $paid_amount = 0;
            foreach ($merchant_order->payments as $payment) {
                $payment = (array)$payment;
                \Log::info($payment['status']);
                if ($payment['status'] == 'approved'){
                    $paid_amount += $payment['transaction_amount'];
                }
            }

            //\Log::info((array)$merchant_order);

            \Log::info('reference = '.$merchant_order->external_reference);
            \Log::info('Id mert = '.$merchant_order->id);
            // If the payment's transaction amount is equal (or bigger) than the merchant_order's amount you can release your items
            if($paid_amount >= $merchant_order->total_amount){

                // The merchant_order don't has any shipments
                //print_r("Totally paid. Release your item.");
                \Log::info('Totally paid. Release your item');

                //$ord = Orders::where('id', $merchant_order->external_reference)->first();
                $ord = Order::getOrderReference($merchant_order->external_reference);

                if(!empty($ord) ){
                    $datComp = $ord->toArray();
                    if($datComp['order_status_id'] == 4){
                        \Log::info("Procesada anteriormente esta Orden de Mercado Pago");
                        return response()->json(['status' => 'ok'],200);
                    }

                    $data = [
                        'order_status_id' => 4,
                        //'idMp' =>$merchant_order->id
                    ];

                    $order = Orders::find($datComp['id'])->update($data);

                }else{
                    \Log::debug("external reference no encontrada en ella bd orders");
                }
                //return redirect(route('thanks'));


                //}
            } else {
                \Log::info("Not paid yet. Do not release your item.");

            }
        }
    }



}
