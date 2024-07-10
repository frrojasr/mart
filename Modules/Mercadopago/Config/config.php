<?php

return [
    'name' => 'Mercadopago',

    'alias' => 'mercadopago',

    'logo' => 'Modules/Mercadopago/Resources/assets/mercadopago.png',

    // Stripe addon settings

    'options' => [
    ['label' => __('Settings'), 'type' => 'modal', 'url' => 'mercadopago.edit'],
    ['label' => __('mercadopago Documentation'), 'target' => '_blank', 'url' => 'https://developer.mercadopago.com/api/rest/']
],

    'validation' => [
    'rules' => [
        'secretKey' => 'required',
        'clientId' => 'required',
        'sandbox' => 'required',
    ],
    'attributes' => [
        'secretKey' => __('Client Secret Key'),
        'clientId' => __('Client Id'),
        'sandbox' => 'Please specify sandbox enabled/disabled.'
    ]
],
    'fields' => [
    'public_key' => [
        'label' => __('Public Key'),
        'type' => 'text',
        'required' => true
    ],
    'token' => [
        'label' => __('Token'),
        'type' => 'text',
        'required' => true
    ],
    'instruction' => [
        'label' => __('Instruction'),
        'type' => 'textarea',
    ],
//    'sandbox' => [
//        'label' => __('Sandbox'),
//        'type' => 'select',
//        'required' => true,
//        'options' => [
//            'Enabled' => 1,
//            'Disabled' =>  0
//        ]
//    ],
    'status' => [
        'label' => __('Status'),
        'type' => 'select',
        'required' => true,
        'options' => [
            'Active' => 1,
            'Inactive' =>  0
        ]
    ]
],

    'store_route' => 'mercadopago.store',


    /**
     * mercadopago payment routes for returning and canceling payment
     */
    'return_url' => 'mercadopago.capture',

    'cancel_url' => 'mercadopago.cancel'
];
