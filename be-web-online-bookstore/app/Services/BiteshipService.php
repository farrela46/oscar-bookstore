<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class BiteshipService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('BITESHIP_API_KEY');
    }

    public function getAreas($input)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get('https://api.biteship.com/v1/maps/areas', [
                    'countries' => 'ID',
                    'input' => $input,
                    'type' => 'single',
                ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error fetching areas from Biteship: ' . $response->body());
    }

    public function getShippingRates($payload)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.biteship.com/v1/rates/couriers', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error fetching shipping rates from Biteship: ' . $response->body());
    }

    public function createOrder($payload)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.biteship.com/v1/orders', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error creating order in Biteship: ' . $response->body());
    }

    public function retrieveOrder($orderId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->get("https://api.biteship.com/v1/orders/{$orderId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error retrieving order from Biteship: ' . $response->body());
    }
}


