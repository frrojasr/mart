<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as RequestG;
use App\Cart\Cart;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;

use Illuminate\Support\Facades\Log;

use Carbon\Carbon;


class PickitController extends Controller
{
    //

    public function getPoints(Request $request)
    {

        $address = $request->address;


        $location = $address['city'];

  
        $queryParams = [
            'filter.location' => ucwords($location)
        ];


        $client = new Client();
        $headers = [
            'token' => ENV('TOKEN_PICKIT'),
            'apikey' => ENV('APIKEY_PICKIT'),

        ];
        $url = env('ENVIROMENT_PICKIT') . '/apiV2/map/point?' . http_build_query($queryParams);

        $request = new RequestG('GET', $url, $headers);
        $res = $client->send($request);
        echo $res->getBody();
    }


    public function obtenerPrecioPunto(Request $request)
    {

         $jsonData = $this->generateJsonPoint($request);

        // Log::info('jsonTest', ['message' => $jsonData]);
        $result = $this->cotizacion($jsonData);

        return response()->json(array(
            'success' => true,
            'results' => json_decode($result, true)
        ));
    }
    public function obtenerPrecioDomicilio(Request $request)
    {

         $jsonData = $this->generateJsonDomicilio($request);

        // Log::info('jsonTest', ['message' => $jsonData]);
        $result = $this->cotizacion($jsonData);

        return response()->json(array(
            'success' => true,
            'results' => json_decode($result, true)
        ));
    }


    public function generateJsonPoint(Request $request){

        $point_id = (int) $request->point_id;
   
        $products = [];

        $address = $request->address;


        $messurementWeight = preference('measurement_weight');
        $messurementDimension = preference('measurement_dimension');

        $retailerAlternativeAddress = [
            'address' => preference('company_street'),
            'streetNumber' => '',
            'province' => 'CABA',
            'postalCode' => preference('company_zip_code'),
            'city' => preference('company_city'),
            'department' => '',
            'neighborhood' => '',
            'floor' => '',
            'apartment' => '',
            'country' => 'Argentina',
            'latitude' => '',
            'longitude' => '',
            'observations' => '',
        ];
        $addressShiping = Address::where('id', $address['address_id'])->first();

        $addressCustomer = [
            'address' => $addressShiping->address_1,
            'streetNumber' => '',
            'province' => $addressShiping->state,
            'postalCode' => $addressShiping->zip,
            'city' => $addressShiping->city,
            'department' => '',
            'neighborhood' => '',
            'floor' => '',
            'apartment' => '',
            'country' => $addressShiping->country,
            'latitude' => '',
            'longitude' => '',
            'observations' => $addressShiping->type_of_place,
        ];

        $customer = [
            'name' => $addressShiping->first_name,
            'lastName' => $addressShiping->last_name ? $addressShiping->last_name: $addressShiping->first_name,
            'pid' => '11111111',
            'email' => $addressShiping->email,
            'phone' => $addressShiping->phone,
            'address' => $addressCustomer,
        ];


        Cart::checkCartData();
     
        $hasCart = Cart::selectedCartCollection();

        if (is_array($hasCart) && count($hasCart) > 0) {

            foreach ($hasCart as $key => $cart) {
                $item = Product::where('id', $cart['id'])->published()->first();

                $products[] = [
                    'name' => $item->name,
                    'weight' => [
                        'amount' => (float) ($item->weight ?? 0.200),
                        'unit' => $messurementWeight,
                    ],
                    'length' => [
                        'amount' => (float) array_val($item->dimension, 'length', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'height' => [
                        'amount' => (float) array_val($item->dimension, 'height', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'width' => [
                        'amount' => (float) array_val($item->dimension, 'width', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'price' => (float) $item->sale_price,
                    'sku' => $item->sku,
                    'amount' => (int) $cart['quantity'],
                ];
            }
        }

        $json = [
            'serviceType' => 'PP',
            'workflowTag' => 'dispatch',
            'operationType' => 1,
            'retailer' => [
                'tokenId' =>  env('TOKEN_PICKIT'),
            ],
            'products' => $products,
            'retailerAlternativeAddress' => $retailerAlternativeAddress,
            'sla' => [
                'id' => 1,
            ],
            'pointId' => $point_id,
            'customer' => $customer,
        ];

        return  $json;

    }

    public function generateJsonDomicilio(Request $request){

        $point_id = (int) $request->point_id;
   
        $products = [];

        $address = $request->address;


        $messurementWeight = preference('measurement_weight');
        $messurementDimension = preference('measurement_dimension');

        $retailerAlternativeAddress = [
            'address' => preference('company_street'),
            'streetNumber' => '',
            'province' => 'CABA',
            'postalCode' => preference('company_zip_code'),
            'city' => preference('company_city'),
            'department' => '',
            'neighborhood' => '',
            'floor' => '',
            'apartment' => '',
            'country' => 'Argentina',
            'latitude' => '',
            'longitude' => '',
            'observations' => '',
        ];
        $addressShiping = Address::where('id', $address['address_id'])->first();

        $addressCustomer = [
            'address' => $addressShiping->address_1,
            'streetNumber' => '',
            'province' => $addressShiping->state,
            'postalCode' => $addressShiping->zip,
            'city' => $addressShiping->city,
            'department' => '',
            'neighborhood' => '',
            'floor' => '',
            'apartment' => '',
            'country' => $addressShiping->country,
            'latitude' => '',
            'longitude' => '',
            'observations' => $addressShiping->type_of_place,
        ];

        $customer = [
            'name' => $addressShiping->first_name,
            'lastName' => $addressShiping->last_name ? $addressShiping->last_name: $addressShiping->first_name,
            'pid' => '11111111',
            'email' => $addressShiping->email,
            'phone' => $addressShiping->phone,
            'address' => $addressCustomer,
        ];


        Cart::checkCartData();
     
        $hasCart = Cart::selectedCartCollection();

        if (is_array($hasCart) && count($hasCart) > 0) {

            foreach ($hasCart as $key => $cart) {
                $item = Product::where('id', $cart['id'])->published()->first();

                $products[] = [
                    'name' => $item->name,
                    'weight' => [
                        'amount' => (float) ($item->weight ?? 0.200),
                        'unit' => $messurementWeight,
                    ],
                    'length' => [
                        'amount' => (float) array_val($item->dimension, 'length', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'height' => [
                        'amount' => (float) array_val($item->dimension, 'height', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'width' => [
                        'amount' => (float) array_val($item->dimension, 'width', 0.200),
                        'unit' => $messurementDimension,
                    ],
                    'price' => (float) $item->sale_price,
                    'sku' => $item->sku,
                    'amount' => (int) $cart['quantity'],
                ];
            }
        }

        $json = [
            'serviceType' => 'PP',
            'workflowTag' => 'dispatch',
            'operationType' => 2,
            'retailer' => [
                'tokenId' =>  env('TOKEN_PICKIT'),
            ],
            'products' => $products,
            'retailerAlternativeAddress' => $retailerAlternativeAddress,
            'sla' => [
                'id' => 1,
            ],
            'pointId' => $point_id,
            'customer' => $customer,
        ];

        return  $json;

    }

    public function cotizacion($json)
    {
        try {
            $client = new Client([
                // 'http_errors' => true,  // Asegura que Guzzle lance excepciones en errores HTTP
                // 'debug' => true,        // Activa el modo de depuración para obtener más detalles
            ]);

            $headers = [
                'token' => env('TOKEN_PICKIT'),
                'apikey' => env('APIKEY_PICKIT'),
                'Content-Type' => 'application/json',
            ];

            $url = env('ENVIROMENT_PICKIT') . '/apiV2.1/budget';

            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $json,
            ]);

            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Client error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Client error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Server error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Server error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ConnectException $e) {
            Log::error('Connection error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Connection error: ' . $e->getMessage()], 500);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response ? $response->getBody()->getContents() : 'No response';
            Log::error('Request error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Request error: ' . $responseBodyAsString], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            Log::error('General error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }


    public function generarTransaccion($pickit)
    {
        try {

            $json = [
                "firstState" => 1,
                "trakingInfo" => [
                    "order" => (string) $pickit->order_id,
                    //"order" => 56,
                    "shipment" => ""
                ],
                "packageAmount" => 1,
                "deliveryTimeRange" => [
                    "start" => Carbon::now()->addDay()->setTime(9, 0),
                    "end" => Carbon::now()->addDay()->setTime(17, 0)
                ],
                "refoundProbableCause" => "",
                "observations" => ""
            ];

            $client = new Client([
                // 'http_errors' => true,  // Asegura que Guzzle lance excepciones en errores HTTP
                // 'debug' => true,        // Activa el modo de depuración para obtener más detalles
            ]);

            $headers = [
                'token' => env('TOKEN_PICKIT'),
                'apikey' => env('APIKEY_PICKIT'),
                'Content-Type' => 'application/json',
            ];

            $url = env('ENVIROMENT_PICKIT') . '/apiV2/transaction/'. $pickit->uuid;
           // $url = env('ENVIROMENT_PICKIT') . '/apiV2/transaction/'. 'af3724bf-15f9-4fc5-971a-014f47590367';


            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $json,
            ]);

            
            return response()->json(array(
                'success' => true,
                'results'=>$response->getBody()->getContents()
            )
        );

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Client error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Client error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Server error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Server error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ConnectException $e) {
            Log::error('Connection error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Connection error: ' . $e->getMessage()], 500);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response ? $response->getBody()->getContents() : 'No response';
            Log::error('Request error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Request error: ' . $responseBodyAsString], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            Log::error('General error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }

    public function generarEtiqueta($transaction_id)
    {
       
        try {

            $json = [
                "arrayTransactionId" => [$transaction_id],
                
            ];

            $client = new Client([
                // 'http_errors' => true,  // Asegura que Guzzle lance excepciones en errores HTTP
                // 'debug' => true,        // Activa el modo de depuración para obtener más detalles
            ]);

            $headers = [
                'token' => env('TOKEN_PICKIT'),
                'apikey' => env('APIKEY_PICKIT'),
                'Content-Type' => 'application/json',
            ];

            $url = env('ENVIROMENT_PICKIT') . '/apiV2/retailer/'.env('TOKEN_PICKIT').'/labels';
           // $url = env('ENVIROMENT_PICKIT') . '/apiV2/transaction/'. 'af3724bf-15f9-4fc5-971a-014f47590367';


            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $json,
            ]);

            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Client error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Client error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::error('Server error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Server error: ' . $responseBodyAsString], $response->getStatusCode());
        } catch (ConnectException $e) {
            Log::error('Connection error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Connection error: ' . $e->getMessage()], 500);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response ? $response->getBody()->getContents() : 'No response';
            Log::error('Request error', ['response' => $responseBodyAsString]);
            return response()->json(['error' => 'Request error: ' . $responseBodyAsString], $response ? $response->getStatusCode() : 500);
        } catch (\Exception $e) {
            Log::error('General error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }

 
}
