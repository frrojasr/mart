<?php

/**
 * @package OrderController
 * @author TechVillage <support@techvill.org>
 * @contributor Sakawat Hossain Rony <[sakawat.techvill@gmail.com]>
 * @created 14-12-2021
 */

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Oca;
use App\Services\Actions\Facades\OrderActionFacade as OrderAction;
use App\Services\Product\AddToCartService;
use Illuminate\Http\Request;
use Modules\Gateway\Facades\GatewayHelper;
use Modules\Gateway\Redirect\GatewayRedirect;

use App\Models\{
    Address,
    Country,
    Currency,
    Product,
    Order,
    OrderDetail,
    OrderMeta,
    OrderStatusHistory,
    Preference,
    OrderStatus,
    Pickit
};
use App\Services\Actions\OrderAction as ActionsOrderAction;
use Modules\Commission\Http\Models\{
    Commission,
    OrderCommission
};
use Cart, Auth, DB, Session;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Route;
use App\Services\Mail\{
    UserInvoiceMailService,
    VendorInvoiceMailService
};

use Modules\Shipping\Entities\ShippingClass;

use App\Http\Controllers\PickitController;

class OrderController extends Controller
{
    /**
     * Address view page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $orders = Auth::user()->orders();
        $data['statuses'] = OrderStatus::getAll()->sortBy('order_by');
        $data['filterStatus'] = false;
        $filterDay = ['today' => today(), 'last_week' => now()->subWeek(), 'last_month' => now()->subMonth(), 'last_year' => now()->subYear()];
        if (isset($request->filter_day) && array_key_exists($request->filter_day, $filterDay)) {
            $orders->whereDate('order_date', '>=', $filterDay[$request->filter_day]);
        }
        if (isset($request->filter_status)) {
            $orders->where('order_status_id', $request->filter_status);
            $data['filterStatus'] = true;
        }
        $preference = Preference::getAll()->pluck('value', 'field')->toArray();
        $data['orders'] = $orders->paginate($preference['row_per_page']);
        return view('site.order.index', $data);
    }

    /**
     * Order Checkout
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkOut(Request $request)
    {
        Cart::checkCartData();
        $data['selectedTotal'] = Cart::totalPrice('selected');
        $hasCart = Cart::selectedCartCollection();
        $shipping = 0;
        $tax = 0;
        $cartService = new AddToCartService();

        if (is_array($hasCart) && count($hasCart) > 0) {

            if (pageReload()) {
                $cartService->destroySessionAddress();
            }

            $taxShipping = $cartService->getTaxShipping();
            //dd($taxShipping['shipping']);
            $data['addresses'] = Address::getAll()->where('user_id', Auth::user()->id);
            //dd($data['addresses']);
            $data['defaultAddresses'] = Address::getAll()->where('user_id', Auth::user()->id)->where('is_default', 1)->first();
            $data['countries'] = Country::getAll();
            $data['tax'] = $taxShipping['tax'];
            $data['shipping'] = $taxShipping['shipping'];
            $data['shippingIndex'] = $cartService->getShippingIndex();

            if (isActive('Coupon')) {
                $data['coupon'] = Cart::getCouponData();
            }
            $cartService->destroySessionAddress();

            if (isActive('Shipping')) {
                $shippings = ShippingClass::getAll();
                $data['shippingClass'] = $shippings;
            }

            return view('site.order.checkout', $data);
        }

        return redirect()->route('site.cart');
    }

    /**
     * order store
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {


        if ($this->c_p_c()) {
            Session::flush();
            return view('errors.installer-error', ['message' => __("This product is facing license validation issue.<br>Please contact admin to fix the issue.")]);
        }
        $order = [];
        $detailData = [];
        $cartData = Cart::selectedCartCollection();
        $cartService = new AddToCartService();

        if (is_array($cartData) && count($cartData) > 0) {
            $coupon = 0;
            if (isActive('Coupon')) {
                $coupon = Cart::getCouponData();
            }
            $defaultCurrency = Currency::getDefault();

            if (isset($request->selected_tab) && $request->selected_tab == 'new') {
                $request['user_id'] = Auth::user()->id;
                $request['is_default'] = isset($request->default_future) && $request->default_future == 'on' ? 1 : 0;
                $validator = Address::storeValidation($request->all());
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }
                $existsAddressId = (new Address)->store($request->only('user_id', 'first_name', 'last_name', 'phone', 'address_1', 'address_2', 'state', 'country', 'city', 'zip', 'is_default', 'type_of_place', 'email'));
                $addressId = $existsAddressId;



                if (isset($request->ship_different) && $request->ship_different == 'on') {
                    $shipDiffAddress = ['country' => $request->shipping_address_country, 'state' => $request->shipping_address_state, 'city' => $request->shipping_address_city, 'post_code' => $request->shipping_address_zip];
                    $addressId = (object)$shipDiffAddress;
                }
            } elseif (isset($request->address_id) && isset($request->selected_tab) && $request->selected_tab == 'old') {
                $defAddress = Address::where('user_id', Auth::user()->id)->where('id', $request->address_id)->first();
                if (!empty($defAddress)) {
                    $existsAddressId = $defAddress->id;
                    $addressId = $existsAddressId;
                } else {
                    return back()->withErrors(['error' => __('Address not found.')])->withInput();
                }
            }


            $taxShipping = $cartService->getTaxShipping($addressId ?? null, 'order');
            $totalTax = $taxShipping['tax'];
            $totalShipping = $taxShipping['shipping'];

            if ($request->post('selectMetodoEnvio') == 'oca') {

                if (!empty($request->post('oca'))) {
                    $totalShipping = $request->post('priceoca');
                    $taxShipping['key'] = 'OCA';
                }
            }

            if ($request->post('selectMetodoEnvio') == 'Pickit') {

                $metodoPickit = $request->post('opcionPickit');

                if (!empty($request->post('pickit_price'))) {

                    $totalShipping = $request->post('pickit_price');
                    $taxShipping['key'] = $metodoPickit;
                    $selectedPoint = $request->post('selectedPoint');
                }
            }


            $cartService->destroySessionAddress();
            $cartService->destroyShippingIndex();
            $orderStatus = OrderStatus::getAll()->where('slug', 'pending-payment')->first();
            $userId = Auth::user()->id;
            $order['user_id'] = $userId;
            $order['order_date'] = DbDateFormat(date('Y-m-d'));
            $order['currency_id'] = $defaultCurrency->id;
            $order['shipping_charge'] = $totalShipping;
            $order['shipping_title'] = $taxShipping['key'] ?? null;
            $order['tax_charge'] = $totalTax;
            $order['total'] = (Cart::totalPrice('selected') + $totalShipping + $totalTax) - $coupon;
            $order['total_quantity'] = Cart::totalQuantity('selected');
            $order['paid'] = 0;
            $order['amount_received'] = 0;
            $order['other_discount_amount'] = $coupon;
            $order['order_status_id'] = $orderStatus->id;

            $preference = Preference::getAll()->pluck('value', 'field')->toArray();
            $reference = Order::getOrderReference($preference['order_prefix'] ?? null);

            $order['reference'] = $reference;

            try {
                DB::beginTransaction();
                $orderId = (new Order)->store($order);
                //envio oca
                $shippingInfo = Address::where('user_id', Auth::user()->id)->where('id', $addressId)->first();

                $shippingMethod = Oca::where('id', 1)->first();

                //dd($orderId);
                $idc = $request->idc ?? 0;

                if ($request->post('selectMetodoEnvio') == 'oca') {
                    $metodo = $request->oca ?? 0;

                    if ($metodo != 0) {
                        $zip = $request->cpoca;
                        $crearEnvio = $this->generarEnvioOca($shippingInfo, $shippingMethod, $order['reference'], $idc, $metodo);
                        //dd($oca);
                        if ($crearEnvio != "" && $crearEnvio > 0) {
                            Order::where('id', $orderId)->update(['orden_oca' => $crearEnvio]);
                            // $order->orden_oca = $crearEnvio;
                        }
                    }
                }

                if ($request->post('selectMetodoEnvio') == 'Pickit') {

                    $dataPickit = [
                        'uuid' => $request->post('uuid'),
                        'order_id' => $orderId,
                        'service_type' => $request->post('opcionPickit'),
                        'point_id' => $request->post('selectedPoint'),
                        'pickit_price' => $request->post('pickit_price'),
                        'status' => 'pending-payment', // 'processing' // 'completed'
                    ];
                    $attributes = ['order_id' => $orderId];
                    Pickit::updateOrCreate($attributes, $dataPickit);
                       
                }

                ///
                /* initial history add */
                $history['order_id'] = $orderId;
                $history['order_status_id'] = $orderStatus->id;
                (new OrderStatusHistory)->store($history);
                /* initial history end */
                if (!empty($orderId)) {
                    $downloadable = [];

                    foreach ($cartData as $key => $cart) {
                        $item = Product::where('id', $cart['id'])->published()->first();

                        if ($item->meta_downloadable == 1) {
                            $idCount = 1;
                            foreach ($item->meta_downloadable_files as $files) {
                                if (isset($files['url']) && !empty($files['url'])) {
                                    $url = urlSlashReplace($files['url'], ['\/', '\\']);
                                    $downloadable[] = [
                                        'id' => $idCount++,
                                        'download_limit' => !is_null($item->meta_download_limit) && $item->meta_download_limit != '' && $item->meta_download_limit != '-1' ? $item->meta_download_limit * $cart['quantity'] : $item->meta_download_limit,
                                        'download_expiry' => $item->meta_download_expiry,
                                        'link' => $url,
                                        'download_times' => 0,
                                        'is_accessible' => 1,
                                        'vendor_id' => $item->vendor_id,
                                        'name' => $item->name,
                                        'f_name' => $files['name'],
                                    ];
                                }
                            }
                        }

                        $variationMeta = null;
                        if ($cart['type'] == 'Variable Product') {
                            $variationMeta = $cart['variation_meta'];
                        }
                        /*Check Inventory & update*/
                        if (!$item->checkInventory($cart['quantity'], $item->meta_backorder, $orderStatus->slug)) {
                            $response = $this->messageArray(__('Invalid Order!'), 'fail');
                            $this->setSessionValue($response);
                            return redirect()->back();
                        }
                        /*End Inventory & update*/
                        $shipping = 0;
                        $tax = 0;
                        if (!empty($item)) {
                            $offerFlag = $item->offerCheck();
                            $tax = $offerFlag ? $item->priceWithTax('including tax', 'sale', false, true, false, $addressId) * $cart['quantity'] : $item->priceWithTax('including tax', 'regular', false, true, false, $addressId) * $cart['quantity'];

                            if (isActive('Shipping')) {
                                $shipping = $item->shipping(['qty' => $cart['quantity'], 'price' => $cart['price'], 'address' => $addressId, 'from' => 'order']);
                                if (is_array($shipping) && count($shipping) > 0) {
                                    $shipping = $shipping[($taxShipping['key'])];
                                } else {
                                    $shipping = 0;
                                }
                            }
                        }
                        $detailData[] = [
                            'product_id' => $cart['id'],
                            'parent_id' => $cart['parent_id'],
                            'order_id' => $orderId,
                            'vendor_id' => $cart['vendor_id'],
                            'shop_id' => $cart['shop_id'],
                            'product_name' => $cart['name'],
                            'price' => $cart['price'],
                            'quantity_sent' => 0,
                            'quantity' => $cart['quantity'],
                            'order_status_id' => $orderStatus->id,
                            'payloads' => $variationMeta,
                            'order_by' => $key,
                            'shipping_charge' => $shipping,
                            'tax_charge' => $tax,
                            'is_stock_reduce' => $item->isStockReduce($orderStatus->slug),
                            'estimate_delivery' => $item->type == 'Variation' ? $item->parentDetail->estimated_delivery : $item->estimated_delivery,
                        ];

                        if ($item->type == 'Variation') {
                            $item->parentDetail->updateCategorySalesCount();
                        } else {
                            $item->updateCategorySalesCount();
                        }
                    }
                    (new OrderDetail)->store($detailData);
                    OrderAction::store($existsAddressId, auth()->user()->id, $orderId, $downloadable, $request);

                    //commission
                    $commission = Commission::getAll()->first();
                    if (!empty($commission) && $commission->is_active == 1) {
                        $orderDetails = OrderDetail::where('order_id', $orderId)->get();
                        $orderCommission = [];
                        foreach ($orderDetails as $details) {
                            if (isset($details->vendor->sell_commissions) && optional($details->vendor)->sell_commissions > 0) {
                                $orderCommission[] = [
                                    'order_details_id' => $details->id,
                                    'category_id' => null,
                                    'vendor_id' => $details->vendor_id,
                                    'amount' => $details->vendor->sell_commissions,
                                    'status' => 'Pending',
                                ];
                            } elseif ($commission->is_category_based == 1 && isset($details->productCategory->category->sell_commissions) && !empty($details->productCategory->category->sell_commissions) && $details->productCategory->category->sell_commissions > 0) {
                                $orderCommission[] = [
                                    'order_details_id' => $details->id,
                                    'category_id' => $details->productCategory->category_id,
                                    'vendor_id' => null,
                                    'amount' => $details->productCategory->category->sell_commissions,
                                    'status' => 'Pending',
                                ];
                            } else {
                                $orderCommission[] = [
                                    'order_details_id' => $details->id,
                                    'category_id' => $details->productCategory->category_id ?? null,
                                    'vendor_id' => $details->vendor_id ?? null,
                                    'amount' => $commission->amount,
                                    'status' => 'Pending',
                                ];
                            }
                        }
                        if (is_array($orderCommission) && count($orderCommission) > 0) {
                            (new OrderCommission)->store($orderCommission);
                        }
                    }

                    $latestOrder = Order::where('id', $orderId)->first();

                    //end commission
                    if (isActive('Coupon')) {
                        $coupons = Cart::getCouponData(false);
                        $couponRedem = [];
                        if (is_array($coupons) && count($coupons) > 0) {
                            foreach ($coupons as $coupon) {
                                $couponRedem[] = [
                                    'coupon_id' => $coupon['id'],
                                    'coupon_code' => $coupon['code'],
                                    'user_id' => Auth::user()->id,
                                    'user_name' => Auth::user()->name,
                                    'order_id' => $orderId,
                                    'order_code' => $latestOrder->reference,
                                    'discount_amount' => $coupon['calculated_discount']
                                ];
                            }
                            (new \Modules\Coupon\Http\Models\CouponRedeem)->store($couponRedem);
                        }
                    }

                    DB::commit();
                    Cart::selectedCartProductDestroy();

                    if ($latestOrder->total <= 0) {
                        $route = $this->orderWithoutPayment($latestOrder->reference);

                        return redirect($route);
                    } else {
                        request()->query->add(['payer' => 'user', 'to' => techEncrypt('site.orderpaid')]);

                        $route = GatewayRedirect::paymentRoute($latestOrder, $latestOrder->total, $latestOrder->currency->name, $latestOrder->reference, $request);

                        return redirect($route);
                    }
                }
            } catch (Exception $e) {
                DB::rollBack();
                return redirect()->back();
            }
        }
        //dd(count($cartData) > 0);
        return redirect()->route('site.cart');
    }


    public function generarEnvioPickit(){

    }

    private function obj2array($obj)
    {
        $out = array();
        foreach ($obj as $key => $val) {
            switch (true) {
                case is_object($val):
                    $out[$key] = $this->obj2array($val);
                    break;
                case is_array($val):
                    $out[$key] = $this->obj2array($val);
                    break;
                default:
                    $out[$key] = $val;
            }
        }
        return $out;
    }

    public function generarEnvioOca($shippingInfo, $shippingMethod, $nroRemito, $idcp = 0, $metodo)
    {
        //dd($shippingInfo);
        $ordenRetiro = "";
        $cantidad = 0;
        Cart::checkCartData();
        $data['selectedTotal'] = Cart::totalPrice('selected');
        $hasCart = Cart::selectedCartCollection();
        //dd($hasCart);
        $pesoTotal = 0;
        $volumenTotal = 0;

        $altoT = 0;
        $anchoT = 0;
        $largoT = 0;
        foreach ($hasCart as $row) {
            $cantidad += $row['quantity'];
            //$valor += $row['price'];

            $p = Product::with(['metadata', 'variations' => function ($q) {
                $q->with('metadata');
            }])->whereId($row['id'])->first();

            // pesos
            $peso = (float) ($p->weight ?? 0.200) * $row['quantity'];
            $pesoTotal += $peso;
            //alto
            $altoT += (float) array_val($p->dimension, 'height', 0.200);

            //largo
            $largoT += (float) array_val($p->dimension, 'length', 0.200);
            //ancho
            $anchoT += (float) array_val($p->dimension, 'width', 0.200);
            //volumen

            //            if(isset($p->height))
            $volumen = (float) array_val($p->dimension, 'length', 0.200) * (float) array_val($p->dimension, 'width', 0.200) * (float) array_val($p->dimension, 'height', 0.200);
            $volumenTotal += round($volumen, 7);
        }
        if ($cantidad == 0) {
            $cantidad = 1;
        }
        $servicio = "http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx?wsdl";
        //$servicio = "http://webservice.oca.com.ar/ePak_Tracking_TEST.asmx?wsdl";
        $parametros = array();
        $peso = $pesoTotal;
        $parametros['usr'] = $shippingMethod->userOca;
        $parametros['psw'] = $shippingMethod->passOca;
        //$pos = strrpos(trim($shippingInfo->name), " ");
        $nombreDestinatario = $shippingInfo->first_name;
        $apellidoDestinatario = $shippingInfo->last_name;
        //        if ($pos !== false) {
        //            $nombreDestinatario = trim(substr($shippingInfo->name, 0, $pos));
        //            $apellidoDestinatario = trim(substr($shippingInfo->name, $pos));
        //        }
        $calle = $shippingInfo->address_1;
        $altura = "";
        $piso = "";
        $dpto = "";
        $alto = 0.0001;
        $ancho = 0.0001;
        $largo = 0.0001;
        $dimensiones = explode(" x ", $shippingMethod->dimensiones);
        if (count($dimensiones) > 0) {
            $alto = trim($dimensiones[0]) * 100;
        }

        if (count($dimensiones) > 1) {
            $largo = trim($dimensiones[1]) * 100;
        }

        if (count($dimensiones) > 2) {
            $ancho = trim($dimensiones[2]) * 100;
        }
        if (isset($shippingInfo->delivery_altura)) {
            $altura = $shippingInfo->delivery_altura;
            $piso = $shippingInfo->delivery_piso;
            $dpto = $shippingInfo->delivery_dpto;
        }
        $provincia = "Capital Federal";
        $localidad = "Capital Federal";
        //$user_id = domain_info('user_id');
        //$locations= Category::where('user_id',$user_id)->where('type','city')->with('child_relation')->get();
        //        foreach($locations as $location) {
        //            if($location->id == $shippingInfo->location) {
        //                $provincia = $location->name;
        //                $localidad = $location->name;
        //                break;
        //            }
        //        }

        $pos = strrpos(trim($shippingInfo->delivery_address), " | ");
        if ($pos === false) {
            if ($altura == "") {
                $direccion_separada = explode(" ", $calle);
                if (count($direccion_separada) > 1) {
                    for ($i = 1; $i < count($direccion_separada); $i++) {
                        if (is_numeric($direccion_separada[$i]) && $altura == "") {
                            $altura = $direccion_separada[$i];
                        }
                        if (is_numeric($direccion_separada[$i]) && $altura != "" && $piso == "") {
                            $piso = $direccion_separada[$i];
                        } elseif ($dpto != "") {
                            $dpto = $direccion_separada[$i];
                        }
                    }
                }
            }
        } else {
            if ($altura == "") {
                $direccion_separada = explode(" | ", $calle);
                if (count($direccion_separada) == 2) {
                    $calle_nro = explode(" ", $direccion_separada[0]);
                    $piso_dpto = explode(" ", $direccion_separada[1]);
                    if (count($calle_nro) > 1) {
                        for ($i = 0; $i < count($calle_nro) - 1; $i++) {
                            $calle .= $calle_nro[$i];
                        }
                        $altura = $calle_nro[count($calle_nro) - 1];
                    }
                    if (count($piso_dpto) > 1) {
                        for ($i = 0; $i < count($piso_dpto) - 1; $i++) {
                            $piso .= $piso_dpto[$i];
                        }
                        $dpto = $piso_dpto[count($piso_dpto) - 1];
                    }
                }
            }
        }
        $calle = trim($calle);
        $altura = trim($altura);
        $piso = trim($piso);
        $dpto = trim($dpto);

        if ($metodo == 2) {
            $operativa = $shippingMethod->sucDespachoSucursal;
        } else {
            $operativa = $shippingMethod->sucDespacho;
        }
        $parametros['xml_Datos'] = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>
										<ROWS>
											<cabecera ver="2.0" nrocuenta="' . $shippingMethod->nroCuenta . '" />
											<origenes>
												<origen calle="-" nro="-" piso="" depto="" cp="' . $shippingMethod->cp . '"
												localidad="-" provincia="-" contacto=""
												email="" solicitante="" observaciones="" centrocosto=""
												idfranjahoraria="1" idcentroimposicionorigen="' . $shippingMethod->idCentroImposicionOrigen . '" fecha="' . date("Ymd") . '">
													<envios>
														<envio idoperativa="' . $operativa . '" nroremito="Envio ' . $nroRemito . '">
															<destinatario apellido="' . $apellidoDestinatario . '" nombre="' . $nombreDestinatario . '" calle="' . $calle . '" nro="' . $altura . '"
															piso="' . $piso . '" depto="' . $dpto . '" localidad="' . $provincia . '" provincia="' . $localidad . '"
															cp="' . $shippingInfo->zip . '" telefono="' . $shippingInfo->phone . '" email="' . $shippingInfo->email . '"  idci="' . $idcp . '"
															celular="' . $shippingInfo->phone . '" observaciones="" />
															<paquetes>
																<paquete alto="' . $altoT . '" ancho="' . $anchoT . '" largo="' . $largoT . '" peso="' . $pesoTotal . '" valor="0" cant="1" />
															</paquetes>
														</envio>
													</envios>
												</origen>
											</origenes>
										</ROWS>';
        $parametros['ConfirmarRetiro'] = true;
        $client = new \SoapClient($servicio, $parametros);
        $result = $client->IngresoORMultiplesRetiros($parametros);
        if ($result) {
            $result = $this->obj2array($result);
            $xml = simplexml_load_string($result['IngresoORMultiplesRetirosResult']['any']);

            if (isset($xml->Resultado) && isset($xml->Resultado->DetalleIngresos) && isset($xml->Resultado->DetalleIngresos->OrdenRetiro) && isset($xml->Resultado->DetalleIngresos->OrdenRetiro)) {
                $ordenRetiro = $xml->Resultado->DetalleIngresos->OrdenRetiro;
            }
        }
        return $ordenRetiro;
    }

    /**
     * order confirmation
     *
     * @param $reference
     * @return void
     */
    public function confirmation($reference)
    {
        $order = Order::where('reference', $reference)->first();
        if (
            !empty($order) && Auth::user() && isset(Auth::user()->role()->type) && Auth::user()->role()->type == 'global' && Auth::user()->role()->slug == 'super-admin' ||
            !empty($order) && $order->user_id == Auth::id() ||
            !empty($order)  && request()->payer == 'guest'
        ) {
            $data['order'] = $order;
            $data['orderDetails'] = collect($order->orderDetails);
            if (request()->payer == 'guest' || request()->redirect == 'confirmation') {
                return redirect(GatewayRedirect::confirmationRedirect());
            }
            return view('site.order.confirmation', $data);
        }
        if (request()->payer == 'guest') {
            return redirect(GatewayRedirect::failedRedirect('error'));
        }
        return redirect()->back();
    }

    /**
     * order details
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function orderDetails($reference)
    {
        $order = Order::where('reference', $reference)->first();
        $data['orderStatus'] = OrderStatus::getAll()->sortBy('order_by');
        if (!empty($order) && isset(Auth::user()->role()->type) && Auth::user()->role()->type == 'global' && Auth::user()->role()->slug == 'super-admin' || !empty($order) && $order->user_id == Auth::user()->id) {
            $data['order'] = $order;
            $data['orderDetails'] = collect($order->orderDetails);
            $data['orderHistories'] = collect($order->orderHistories);
            $data['detailGroups'] = $data['orderDetails']->groupBy('vendor_id');
            $data['orderAction'] = new ActionsOrderAction;
            return view('site.order.order-details', $data);
        }
        return redirect()->back();
    }

    /**
     * payment process again if payment status is unpaid
     *
     * @param $reference
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function payment(Request $request)
    {
        $order = Order::where('reference', $request->reference)->first();
        if (!empty($order)) {
            if (optional($order->paymentMethod)->status == 'pending') {
                if (Route::currentRouteName() == 'site.orderPayment.guest') {
                    request()->query->add(['payer' => 'guest', 'to' => techEncrypt('site.orderpaid.guest'), 'integrity' => getIntegrityKey()]);
                } else {
                    request()->query->add(['to' => techEncrypt('site.orderpaid')]);
                }
                $route = GatewayRedirect::paymentRoute($order, $order->total, $order->currency->name, $order->reference, null, optional($order->paymentMethod)->id);
                return redirect($route);
            }
        }

        if (Route::currentRouteName() == 'site.orderPayment.guest') {
            return redirect(GatewayRedirect::failedRedirect());
        }

        return redirect()->back();
    }

    /**
     * order track
     *
     * @param Request $request
     * @param string $code
     * @return \Illuminate\Contracts\View\View
     */
    public function track(Request $request)
    {
        if (!$request->filled('code')) {
            return view('site.order.order-track');
        }

        if (!OrderMeta::where(['key' => 'track_code', 'value' => $request->code])->count()) {
            return redirect()->route('site.trackOrder')->withErrors(['code' => $request->code, 'message' => __('Track code is invalid.')]);
        }

        $data['order'] = Order::with(OrderAction::relationsWith())
            ->join('orders_meta', 'orders.id', 'orders_meta.order_id')
            ->where(['orders_meta.key' => 'track_code', 'orders_meta.value' => $request->code])
            ->selectRaw('orders.*, orders_meta.value as track_code')
            ->first();
        $data['statuses'] = OrderStatus::select('id', 'name')->orderBy('order_by')->get();

        return view('site.order.order-track-details', $data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function orderPaid(Request $request)
    {
        if (!checkRequestIntegrity()) {
            return redirect(GatewayRedirect::failedRedirect('integrity'));
        }

        $isGuest = $request->payer == 'guest';

        try {
            $code = techDecrypt($request->code);
            $order = Order::where('reference', $code)->first();
            $orderStatusInfo = OrderStatus::getAll()->where('slug', 'processing')->first();

            if (!$order) {
                if ($isGuest) {
                    return redirect(GatewayRedirect::failedRedirect())->withErrors(__("Invalid order data."));
                }
                return redirect()->route('site.cart')->withErrors(__('Order not found.'));
            }

            $log = GatewayHelper::getPaymentLog($code);

            if (!$log) {
                if ($isGuest) {
                    return redirect(GatewayRedirect::failedRedirect())->withErrors(__("Payment data not found."));
                }
                return redirect()->route('site.cart')->withErrors(__('Payment data not found.'));
            }

            if (!FacadesAuth::id()) {
                FacadesAuth::onceUsingId($order->user_id);
            }

            if ($log->status == 'completed') {
                $data = json_decode($log->response);
                $order->paid = $data->amount;
                $order->amount_received = $data->amount;
                $order->payment_status = "Paid";
                $order->order_status_id = $orderStatusInfo->id;
                //order transaction
                $order->transactionStore();
                $this->pickitUpdate($order);
                

                foreach ($order->orderDetails as $detail) {
                    (new OrderDetail)->updateOrder(['order_status_id' => $orderStatusInfo->id], $detail->id);
                }
           }

            $order->checkOrderStatus();
            $order->save();

            // Send invoice to user and vendor
            (new UserInvoiceMailService)->send($order);
            (new VendorInvoiceMailService)->send($order);

            return redirect()
                ->route($isGuest ? 'site.orderConfirm.guest' : 'site.orderConfirm', withOldQueryString(['reference' => $order->reference]));
        } catch (\Exception $e) {
            if ($isGuest) {
                return redirect(GatewayRedirect::failedRedirect('error'))->withErrors($e->getMessage());
            }
            return redirect()->route('site.cart')->withErrors($e->getMessage());
        }
    }

    /**
     * if order balance will zero then this function will be used
     *
     * @param $reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function orderWithoutPayment($reference = null)
    {
        $orderStatusInfo = OrderStatus::getAll()->where('slug', 'processing')->first();
        $order = Order::where('reference', $reference)->first();

        if (!$order) {
            return redirect()->route('site.cart')->withErrors(__('Order not found.'));
        }

        try {
            $order->payment_status = "Paid";
            $order->order_status_id = $orderStatusInfo->id;
            //order transaction
            $order->transactionStore();
            $this->pickitUpdate($order);
            foreach ($order->orderDetails as $detail) {
                (new OrderDetail)->updateOrder(['order_status_id' => $orderStatusInfo->id], $detail->id);
            }

            $order->checkOrderStatus();
            
            $order->save();


            // Send invoice to user and vendor
            (new UserInvoiceMailService)->send($order);
            (new VendorInvoiceMailService)->send($order);

            return route('site.orderConfirm', ['reference' => $order->reference]);
        } catch (\Exception $e) {
            return route('site.cart')->withErrors($e->getMessage());
        }
    }

    public function orderManage()
    {
        return view('site.order.order-manage');
    }

    public function getShippingTax(Request $request)
    {
        $response = ['status' => 0];
        $cartService = new AddToCartService();
        $address = $request->address['address_id'] ?? null;

        if (is_null($address)) {
            $address = ['country' => $request->address['country'], 'state' => $request->address['state'], 'city' => $request->address['city'], 'post_code' => $request->address['zip']];
            $address = (object)$address;
        }
        $cartService->destroyShippingIndex();
        $getTaxShipping = $cartService->getTaxShipping($address, null, true);

        if ($getTaxShipping) {
            $response = ['status' => 1, 'tax' => $getTaxShipping['tax'], 'displayTaxTotal' => $getTaxShipping['displayTaxTotal'], 'shipping' => $getTaxShipping['shipping'], 'totalPrice' => Cart::totalPrice('selected'), 'shippingIndex' => $cartService->getShippingIndex()];
        }

        return $response;
    }

    /**
     * order invoice print
     *
     * @param Request $request
     * @param $id
     * @return
     */
    public function invoicePrint($id)
    {
        $order = Order::where('id', $id)->first();
        if (!empty($order)) {
            $data['orderStatus'] = OrderStatus::getAll()->sortBy('order_by');
            $data['order'] = $order;
            $data['logo'] = Preference::getAll()->where('field', 'company_logo')->first()->fileUrl();
            $data['orderAction'] = new ActionsOrderAction;
            $data['user'] = $order->user;
            $data['orderDetails'] = $order->orderDetails;
            $data['type'] = request()->get('type') == 'print' || request()->get('type') == 'pdf' ? request()->get('type') : 'print';
            if ($data['type'] == 'pdf') {
                return printPDF($data, $order->reference . '.pdf', 'site.order.invoice_print', view('site.order.invoice_print', $data), $data['type']);
            } else {
                return view('site.order.invoice_print', $data);
            }
        }
        return redirect()->route('site.order');
    }

    /**
     * Check Verification
     *
     * @return bool
     */
    public function c_p_c()
    {
        p_c_v();
        return false;
    }


    public function pickitUpdate( $order) {
        $is_pickit = Pickit::where('order_id', $order->id)->first();

        
        $orderStatusInfo = OrderStatus::where('id', $order->order_status_id)->first();
      
        if ( $is_pickit){
            if ($order->payment_status=='paid'){
                
                if ($is_pickit->status=='pending-payment'){
                    //generar transaccion pickit
                    $pickitController = new PickitController();
                    $transaccionPickit = $pickitController->generarTransaccion($is_pickit);
                    $transaccionPickit = json_decode($transaccionPickit,true);
                   
                    $is_pickit->transaction_id = $transaccionPickit['transactionId'];
                    $is_pickit->pickit_code = $transaccionPickit['pickitCode'];
                    $is_pickit->url_tracking = $transaccionPickit['urlTracking'];
                   
                }
                $is_pickit->status= $orderStatusInfo->slug;
                $is_pickit->save();
            }

        }
        
    }


    public function pickitTest()
    {
        $order = Order::where('id',60)->first();
        $order->payment_status='paid';
        $order->order_status_id = 3;
        $order->save();
        if ($order){
            $this->pickitUpdate($order);
        };

        echo 'hola';

    }
}
