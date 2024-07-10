@extends('gateway::layouts.payment')

@php
// SDK de Mercado Pago

require base_path('Modules/Mercadopago/vendor/autoload.php');



//dd($items->toArray());
  MercadoPago\SDK::setAccessToken($dataMp->token);
$nameProducts = '';
foreach ($items as $itemsP) {
    foreach ($itemsP as $item) {
        $nameProducts .= $item ->product_name.', ';
    }

}
$nameProducts = substr($nameProducts,0, -2);



  $preference = new MercadoPago\Preference();


  $item = new MercadoPago\Item();
  $item->title = $nameProducts;
  $item->quantity = 1;
  $item->unit_price = round($purchaseData->total,2);
  $preference->items = array($item);
  //$preference->auto_return = "approved";
  $preference->notification_url = route('mercadopago.ipnMp');
  $preference->external_reference= $purchaseData->code;
  $preference->save();

@endphp

@section('logo', asset(config('mercadopago.logo')))
@section('gateway', config('mercadopago.name'))

@section('content')
    <div class="straight-line"></div>
    @include('gateway::partial.instruction')
    {{--<form action="{{ route('gateway.complete', withOldQueryIntegrity(['gateway' => config('mercadopago.alias')])) }}"--}}
        {{--method="post" id="payment-form" class="pay-form">--}}
        @csrf
        {{--<button type="submit" class="pay-button sub-btn">--}}
            {{--<span>{{ __('Pay With Mercadopago') }}--}}
        {{--</button>--}}
<div class="">
    <div class="cho-container mx-auto">

    </div>
</div>

    {{--</form>--}}

    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <script>
        const mp = new MercadoPago('{{$dataMp->public_key}}', {
            locale: 'es-AR'
        });

        mp.checkout({
            preference: {
                id: '{{$preference->id}}'
            },
            render: {
                container: '.cho-container',
                label: 'Pay With Mercadopago'
            }
        });
    </script>
@endsection
