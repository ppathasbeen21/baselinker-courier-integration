<?php

require_once 'Courier.php';

$order = [
    'sender_company' => 'BaseLinker',
    'sender_fullname' => 'Jan Kowalski',
    'sender_address' => 'Kopernika 10',
    'sender_city' => 'Gdansk',
    'sender_postalcode' => '80208',
    'sender_country' => 'PL',
    'sender_email' => '',
    'sender_phone' => '666666666',

    'delivery_company' => 'Spring GDS',
    'delivery_fullname' => 'Patrick Bandeira',
    'delivery_address' => 'Strada Foisorului, Nr. 16, Bl. F11C, Sc. 1, Ap. 10',
    'delivery_city' => 'Bucuresti, Sector 3',
    'delivery_postalcode' => '031179',
    'delivery_country' => 'RO',
    'delivery_email' => 'patrick010509@gmail.com',
    'delivery_phone' => '555555555',
];

$params = [
    'api_key' => 'X5du5ZT4A8wT76JcFOMP',
    'label_format' => 'PDF',
    'service' => 'PPTT',
    'weight' => 1.0,
    'ShipmentValue' => 10.0,
    'products' => [
        [
            'Description' => 'Shipment',
            'Quantity' => 3,
            'Weight' => 1.0,
            'Value' => 10.0,
            'HsCode' => '8471.30.00',
        ]
    ],
];


$courier = new Courier($params['api_key']);

$trackingNumber = $courier->newPackage($order, $params);

$courier->packagePDF($trackingNumber);
