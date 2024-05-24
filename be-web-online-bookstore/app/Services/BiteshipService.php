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

    public function addAddress($data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.biteship.com/v1/locations', $data);

        return $response->json();
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
}
