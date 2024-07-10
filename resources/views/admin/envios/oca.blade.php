<?php
/**
 * Created by PhpStorm.
 * User: Miguel Leonice
 * Date: 16/03/2023
 * Time: 16:53
 */?>

@extends('admin.layouts.app')
@section('page_title', __('Order Setting'))

@section('css')
    {{-- Select2  --}}
    <link rel="stylesheet" href="{{ asset('public/datta-able/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <!-- Main content -->
    <div class="col-sm-12" id="order-settings-container">
        <div class="card">
            <div class="card-body row">

                <div class="col-lg-9 col-12 ps-0">
                    <div class="card card-info shadow-none mb-0">
                        <div class="card-header p-t-0 border-bottom">
                            <h5>{{ __('OCA') }}</h5>
                        </div>
                        <div class="card-block table-border-style">
                            <form action="{{ route('envio.creaOrUp') }}" method="post" class="form-horizontal" id="order_setting_form">
                                @csrf
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Title') }}</label>
                                    <div class="col-sm-12 col-md-7">
                                        <input type="text" class="form-control" required="" name="title" value="{{$oca->name ?? ''}}">
                                    </div>
                                </div>

                                    {{--<div class="form-group row mb-4">--}}
                                        {{--<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>--}}
                                        {{--<div class="col-sm-12 col-md-7">--}}
                                            {{--<label>--}}
                                                {{--<input type="checkbox" name="esOca" class="custom-switch-input sm" value="1">--}}
                                                {{--<span class="custom-switch-indicator"></span>--}}
                                                {{--{{ __('Envío por oca') }}--}}
                                            {{--</label>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                    <div id="datos-oca" class="">
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Usuario oca') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="text" class="form-control" name="userOca" value="{{$oca->userOca ?? ''}}">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Contraseña oca') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="text" class="form-control" name="passOca" value="{{$oca->passOca ?? ''}}" >
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Cuit') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="text" class="form-control" name="cuit" value="{{$oca->cuit}}" placeholder="Formato 00-00000000-00">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Numero de cuenta') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="text" class="form-control" name="nroCuenta" value="{{$oca->nroCuenta ?? ''}}" >
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Peso promedio del paquete en KG') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="peso"  value="{{$oca->peso ?? ''}}" >
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-2 col-lg-3">{{ __('Dimensiones promedio de los paquetes en mts') }}</label>
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Volumen') }}</label>
                                            <div class="col-sm-12 col-md-4">
                                                <input type="number" class="form-control" name="volumen"  value="{{$oca->volumen ?? ''}}" step="0.00001" readonly placeholder="Se va a calcular automaticamente">
                                            </div>
                                        </div>
                                    <!-- <div class="form-group row mb-4">
							<label class="col-form-label text-md-right col-12 col-md-2 col-lg-1">{{ __('Alto') }}</label>
							<div class="col-sm-12 col-md-2">
								<input type="number" class="form-control" name="alto" step="0.00001" placeholder="Alto en metros, Ej: 1.2">
							</div>
							<label class="col-form-label text-md-right col-12 col-md-2 col-lg-1">{{ __('Ancho') }}</label>
							<div class="col-sm-12 col-md-2">
								<input type="number" class="form-control" name="largo" step="0.00001" placeholder="Ancho en metros, Ej: 0.4">
							</div>
							<label class="col-form-label text-md-right col-12 col-md-2 col-lg-2">{{ __('Largo') }}</label>
							<div class="col-sm-12 col-md-2">
								<input type="number" class="form-control" name="ancho" step="0.00001" placeholder="Largo en metros Ej: 1.4">
							</div>
						</div> -->
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Alto promedio del paquete en metros') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="alto"  value="{{$oca->alto ?? ''}}" step="0.00001" placeholder="Ejemplo: 1.02 metros">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Largo promedio del paquete en metros') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="largo"  value="{{$oca->largo ?? ''}}" step="0.00001" placeholder="Ejemplo: 0.5 metros">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Ancho promedio del paquete en metros') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="ancho"  value="{{$oca->ancho ?? ''}}" step="0.00001" placeholder="Ejemplo: 2.5 metros">
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Codigo postal') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="cp" value="{{$oca->cp ?? ''}}" >
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Sucursal de despacho') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <!-- <select class="form-control select2" name="sucDespacho">
                                                    <option value="">Complete el codigo postal</option>
                                                </select> -->
                                                <input type="number" class="form-control" name="sucDespacho" value="{{$oca->sucDespacho ?? ''}}" >
                                            </div>
                                        </div>

                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Sucursal de despacho envio a sucursal') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <!-- <select class="form-control select2" name="sucDespacho">
                                                    <option value="">Complete el codigo postal</option>
                                                </select> -->
                                                <input type="number" class="form-control" name="sucDespachoSucursal" value="{{ $oca->sucDespachoSucursal ?? '' }}" >
                                            </div>
                                        </div>
                                        <div class="form-group row mb-4">
                                            <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Id centro imposicion de origen') }}</label>
                                            <div class="col-sm-12 col-md-7">
                                                <input type="number" class="form-control" name="idCentroImposicionOrigen" value="{{$oca->idCentroImposicionOrigen ?? ''}}" >
                                            </div>
                                        </div>
                                    </div>

                                {{--<div class="form-group row mb-4">--}}
                                    {{--<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Price') }}</label>--}}
                                    {{--<div class="col-sm-12 col-md-7">--}}
                                        {{--<input type="number" step="any" class="form-control" required="" name="price">--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="form-group row mb-4">--}}
                                    {{--<label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Locations') }}</label>--}}
                                    {{--<div class="col-sm-12 col-md-7">--}}
                                        {{--<select multiple class="form-control select2" name="locations[]" required="">--}}
                                            {{--{{ ConfigCategoryMulti('city') }}--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">{{ __('Detalle') }}</label>
                                    <div class="col-sm-12 col-md-7">
                                        <input type="text" class="form-control" name="detail" placeholder="" value="{{$oca->detail ?? ''}}">
                                    </div>
                                </div>
                                <div class="form-group row mb-4">
                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                                    <div class="col-sm-12 col-md-7">
                                        <button class="btn btn-primary basicbtn" type="submit">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('public/datta-able/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('public/dist/js/custom/preference.min.js') }}"></script>
    <script src="{{ asset('public/dist/js/custom/validation.min.js') }}"></script>

    <script>
        function calculaVolumen(){
            let alto = $('[name="alto"]').val() == "" || isNaN($('[name="alto"]').val()) ? "" : $('[name="alto"]').val();
            let ancho = $('[name="ancho"]').val() == "" || isNaN($('[name="ancho"]').val()) ? "" : $('[name="ancho"]').val();
            let largo = $('[name="largo"]').val() == "" || isNaN($('[name="largo"]').val()) ? "" : $('[name="largo"]').val();
            if(alto != "" && ancho != "" && largo != "") {
                let volumen = alto * ancho * largo;
                volumen = volumen.toFixed(7);
                if(volumen == 0) {
                    volumen = 0.000001;
                }
                $('[name="volumen"]').val(volumen);
            }
        }

        $('[name="alto"]').on("change", calculaVolumen);
        $('[name="ancho"]').on("change", calculaVolumen);
        $('[name="largo"]').on("change", calculaVolumen);
    </script>
@endsection
