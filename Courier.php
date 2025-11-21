<?php

declare(strict_types=1);

class Courier
{
    private string $apiUrl = 'https://developers.baselinker.com/recruitment/api';
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function newPackage(array $order, array $params): string
    {
        $service = $params['service'] ?? 'PPTT';
        $limits = $this->getFieldLimits($service);

        $shipment = [
            'Service' => $service,
            'ShipperReference' => $this->truncate($params['shipper_reference'] ?? 'REF' . time(), 15),
            'Weight' => (float) ($params['weight'] ?? 1.0),
            'ConsignorAddress' => [
                'FullName' => $this->truncate($order['sender_fullname'] ?? '', $limits['Name']),
                'Company' => $this->truncate($order['sender_company'] ?? '', $limits['Company']),
                'AddressLine1' => $this->truncate($order['sender_address'] ?? '', $limits['AddressLine1']),
                'City' => $this->truncate($order['sender_city'] ?? '', $limits['City']),
                'Zip' => $this->truncate($order['sender_postalcode'] ?? '', $limits['Zip']),
                'Country' => $order['sender_country'] ?? 'PL',
                'Phone' => $this->truncate($order['sender_phone'] ?? '', $limits['Phone']),
                'Email' => $order['sender_email'] ?? '',
            ],
            'ConsigneeAddress' => [
                'FullName' => $this->truncate($order['delivery_fullname'] ?? '', $limits['Name']),
                'Company' => $this->truncate($order['delivery_company'] ?? '', $limits['Company']),
                'AddressLine1' => $this->truncate($order['delivery_address'] ?? '', $limits['AddressLine1']),
                'City' => $this->truncate($order['delivery_city'] ?? '', $limits['City']),
                'Zip' => $this->truncate($order['delivery_postalcode'] ?? '', $limits['Zip']),
                'Country' => $order['delivery_country'] ?? '',
                'Phone' => $this->truncate($order['delivery_phone'] ?? '', $limits['Phone']),
                'Email' => $order['delivery_email'] ?? '',
            ],
            'Value' => (float)($params['value'] ?? 10.0),
            'Currency' => $params['currency'] ?? 'EUR',
            'Products' => $params['products'] ?? [
                    [
                        'Description' => 'Shipment',
                        'Quantity' => 1,
                        'Weight' => (float) ($params['weight'] ?? 1.0),
                        'Value' => 10.0,
                        'HsCode' => '8471.30.00',
                    ]
                ],
            'LabelFormat' => $params['label_format'] ?? 'PDF',
        ];

        $response = $this->request('OrderShipment', ['Shipment' => $shipment]);

        return $response['Shipment']['TrackingNumber'];
    }

    public function packagePDF(string $trackingNumber): void
    {
        $response = $this->request('GetShipmentLabel', [
            'Shipment' => [
                'TrackingNumber' => $trackingNumber,
                'LabelFormat' => 'PDF',
            ]
        ]);

        $labelBase64 = $response['Shipment']['LabelImage'] ?? '';
        if (empty($labelBase64)) {
            $this->error('Label not found in API response');
        }

        $pdf = base64_decode($labelBase64);
        if ($pdf === false) {
            $this->error('Failed to decode label (invalid base64)');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="label_' . $trackingNumber . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function request(string $command, array $data = []): array
    {
        $payload = array_merge(['Apikey' => $this->apiKey, 'Command' => $command], $data);
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->error("Connection error: {$curlError}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->error("HTTP error: status code {$httpCode}");
        }

        $decoded = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid API response (malformed JSON)');
        }

        if (($decoded['ErrorLevel'] ?? 0) > 0) {
            $this->error('API error: ' . ($decoded['Error'] ?? 'Unknown error'));
        }

        return $decoded;
    }

    private function truncate(string $value, int $limit): string
    {
        return mb_substr($value, 0, $limit);
    }

    private function getFieldLimits(string $service): array
    {
        $limits = [
            'PPTT' => ['Name' => 30, 'Company' => 30, 'AddressLine1' => 30, 'City' => 30, 'Zip' => 20, 'Phone' => 15],
            'PPLEU' => ['Name' => 35, 'Company' => 35, 'AddressLine1' => 35, 'City' => 35, 'Zip' => 20, 'Phone' => 15],
            'PPLGE/GU' => ['Name' => 50, 'Company' => 30, 'AddressLine1' => 50, 'City' => 50, 'Zip' => 20, 'Phone' => 15],
            'RM24/48(S)' => ['Name' => 30, 'Company' => 30, 'AddressLine1' => 30, 'City' => 30, 'Zip' => 20, 'Phone' => 15],
            'PPTR/NT' => ['Name' => 30, 'Company' => 30, 'AddressLine1' => 30, 'City' => 30, 'Zip' => 20, 'Phone' => 15],
            'SEND(2)' => ['Name' => 20, 'Company' => 35, 'AddressLine1' => 35, 'City' => 35, 'Zip' => 20, 'Phone' => 15],
        ];

        return $limits[$service] ?? $limits['PPTT'];
    }

    private function error(string $message): void
    {
        http_response_code(400);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h3 style="color:#c00;">Error</h3>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        exit;
    }
}
