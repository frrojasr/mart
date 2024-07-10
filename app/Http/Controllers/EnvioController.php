<?php

namespace App\Http\Controllers;

use App\Cart\Cart;
use App\Models\Oca;
use App\Models\Product;
use App\Services\Actions\Facades\ProductActionFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SoapClient;

class EnvioController extends Controller
{
    public function index()
    {
        $data = [
            'oca' => Oca::find(1),
        ];
        //dd($data['oca']->sucDespachoSucursal);
        return view('admin.envios.oca',$data);
    }

    public function createOrUpdate(Request $request)
    {
        $post = new Oca();
        $post->name=$request->title;
        $post->detail=$request->detail;
        $post->user_id = Auth::id();
       // $post->slug=$request->price;



                $validatedData = $request->validate([
                    'title' => 'required|max:50',
                    //'locations' => 'required',
                    //'esOca' => 'required',
                    'peso' => 'required',
                    'volumen' => 'required',
                    'alto' => 'required',
                    'largo' => 'required',
                    'ancho' => 'required',
                    'cp' => 'required',
                    'cuit' => 'required',
                    'nroCuenta' => 'required',
                    'idCentroImposicionOrigen' => 'required',
                    'userOca' => 'required',
                    'passOca' => 'required',
                ]);
                //$post->esOca=$request->esOca;
                $post->peso=$request->peso;
                $post->volumen=$request->alto * $request->largo * $request->ancho;
                if(round($post->volumen, 7) == 0) {
                    $post->volumen= 0.000001;
                }
                $post->dimensiones=$request->alto." x ".$request->largo." x ".$request->ancho;
                $post->cp=$request->cp;
                $post->sucDespacho=$request->sucDespacho;
                $post->sucDespachoSucursal=$request->sucDespachoSucursal;
                $post->cuit=$request->cuit;
                $post->nroCuenta=$request->nroCuenta;
                $post->idCentroImposicionOrigen=$request->idCentroImposicionOrigen;
                $post->userOca=$request->userOca;
                $post->passOca=$request->passOca;


        //$post->type="method";

        $oca = Oca::where('id','>', 1)->first();

        if (!empty($oca)){
            //$oca = $post;
            $dat =  $post->toArray();
            //dd($dat);
            Oca::where('id', 1)->update($dat);

            //$post->save();
           // die('a');
        }else{
            $post->save();
        }



        $arr=[];
//        foreach ($request->locations as $key => $row) {
//            $data['category_id']=$post->id;
//            $data['relation_id']=$row;
//            array_push($arr, $data);
//        }
//        Categoryrelation::insert($arr);

        return redirect()->back();

    }

    public function obtenerPrecioDeEnvio(Request $request) {
        $cp = $request->cp;
        if(strlen($cp) < 4) {
            return;
        }
        //$shippingId =  $request->shippingMethod;
        //$user_id=domain_info('user_id');
        $success = true;
        $precio = -1;
        //$shippingMethod = Category::where('user_id',$user_id)->where('type','method')->where('id',$shippingId)->with('child_relation')->get();
        $shippingMethod = Oca::where('id',1)->first();//find($shippingId);
        //dd($shippingMethod);
        $metodo = $request->shippingMethod;

        $precio = $this->obtenerPrecioDeEnvioOca($cp, $shippingMethod, $metodo);
        if($precio == -1) {
            $success = false;
        }
        return response()->json(array('success' => $success, 'price'=>$precio));
    }

    public function obtenerPrecioDeEnvioOca($cp, $shippingMethod, $destino = 1) {
        $precio = -1;
        $cantidad = 0;
        $valor = 0;
        Cart::checkCartData();
        $data['selectedTotal'] = Cart::totalPrice('selected');
        $hasCart = Cart::selectedCartCollection();



        //$p = ProductActionFacade:: execute('getProductWithAttributeAndVariations', $hasCart[2]['code']);
        $volumenTotal = 0;
        $pesoTotal = 0;
        foreach ($hasCart as $row) {

            $p = Product::with(['metadata', 'variations' => function ($q) {
                $q->with('metadata');
            }])->whereId($row['id'])->first();

            // pesos
            $peso = (float) ($p->weight ?? 0.200) * $row['quantity'];
            $pesoTotal += $peso;
            //alto
            //$alto = $p->height ??
            //largo

            //ancho

            //volumen

//            if(isset($p->height))
            $volumen = (float) array_val($p->dimension, 'length', 0.200) * (float) array_val($p->dimension, 'width', 0.200) * (float) array_val($p->dimension, 'height', 0.200);
            $volumenTotal += round($volumen,7);



//dd(  ,$p->height,$p->width);




            $cantidad +=$row['quantity'];
            $valor += $row['price'];

        }
        if($cantidad == 0) {
            $cantidad = 1;
        }

        //dd([$pesoTotal,$volumenTotal]);
        // 250 x 180 x 70 mm - 1 KG. // 0.00315
        //if(count($shippingMethod) == 1) {
        //$shippingMethod = $shippingMethod[0];
        $servicio = "http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx?wsdl";
        $parametros = array();
        $parametros['PesoTotal'] = (float) $pesoTotal;
        $parametros['VolumenTotal'] = rtrim(number_format($volumenTotal, 7), 0);
        $parametros['CodigoPostalOrigen'] = $shippingMethod->cp;
        $parametros['CodigoPostalDestino'] = $cp;
        $parametros['CantidadPaquetes'] = 1;
        $parametros['ValorDeclarado'] = intval($valor);
        $parametros['Cuit'] = $shippingMethod->cuit;
			if ($destino == 2) {
                $parametros['Operativa'] = intval($shippingMethod->sucDespachoSucursal);//$parametrosCargados['oca.ws.OperativaEnvioASucursal'];
			} else  {
                $parametros['Operativa'] = intval($shippingMethod->sucDespacho);	//$parametrosCargados['oca.ws.OperativaEnvioADomicilio'];
			}
        $client = new SoapClient($servicio, $parametros);
        $result = $client->Tarifar_Envio_Corporativo($parametros);
        if($result) {
            $result = $this->obj2array($result);
            $xml = simplexml_load_string($result['Tarifar_Envio_CorporativoResult']['any']);


            if(isset($xml->NewDataSet) && isset($xml->NewDataSet->Table) && isset($xml->NewDataSet->Table->Precio)) {
                $precio_calculado = (floatval($xml->NewDataSet->Table->Precio) + 7.20) * 1.21;
                $precio = round(floatval($precio_calculado), 2);
            }
        }
        //}
        return $precio;
    }

    public function obtenerSucursales(Request $request)
    {
        $cp = $request->get('cp');
        if($cp) {


            $url = "http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx/GetCentrosImposicionConServiciosByCP?CodigoPostal=$cp";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);


            //$result = $this->obj2array($data);
            $xml = simplexml_load_string($data);
            $sucursales = [];
            foreach ($xml as $x) {
                //if($x->IdTipoServicio ==2)
                $isDestino =false;
                //$count = count();
//                dd($x->Servicios->Servicio);
                foreach ($x->Servicios->Servicio as $s) {

                    if($s->IdTipoServicio == 2) {
                        $isDestino = true;

                    }

                }

                if($isDestino){
                    $sucursal = [
                        'calle' => $x->Calle,
                        'numero' => $x->Numero,
                        'id' => $x->IdCentroImposicion,
                        'localidad' => $x->Localidad,
                    ];
                    $sucursales[] = $sucursal;
                }

                //echo($x->Calle . ' ' . $x->Numero).'<br>';
            }
            //
            $data = [
                'status' => 'success',
                'code' => 200,
                'sucursales' => $sucursales,
            ];


            //print_r($xml);
            //dd($xml->centro[0]);
        }else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'messaje' => 'Codigo Postal es requerido',
            ];
        }


        return response()->json($data, $data['code']);  //json_encode($data);
    }

    public function etiquetaEnvioOca(Request $request) {
        $servicio = "http://webservice.oca.com.ar/ePak_tracking/Oep_TrackEPak.asmx?wsdl";
        $parametros = array();
        $parametros['idOrdenRetiro'] = $request->idOrdenRetiro;
        $parametros['nroEnvio'] = $request->nroEnvio;
        $parametros['logisticaInversa'] = false;//$request->logisticaInversa;
        $client = new SoapClient($servicio, $parametros);
        $result = $client->GetPdfDeEtiquetasPorOrdenOrNumeroEnvio($parametros);
        if($result && isset($result->GetPdfDeEtiquetasPorOrdenOrNumeroEnvioResult)) {
            return response()->json(array(
                    'success' => true,
                    'results'=>$result->GetPdfDeEtiquetasPorOrdenOrNumeroEnvioResult
                )
            );
        }
        return response()->json(array(
                'success' => true,
                'results'=>$result->GetPdfDeEtiquetasPorOrdenOrNumeroEnvioResult
            )
        );
    }



    private function obj2array($obj) {
        $out = array();
        foreach ($obj as $key => $val) {
            switch(true) {
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
}
