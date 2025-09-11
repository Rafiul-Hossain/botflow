<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    /** API URL */
    private $apiUrl = 'https://bulkfollows.com/api/v2';
    //https://global-smm.com/api/v2
    //https://bulkfollows.com/api/v2

    /** Your API key */
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.smm.key'); // Store in config/services.php
    }

    /** Add order */
    public function order(Request $request)
    {
        $data = array_merge([
            'key' => $this->apiKey,
            'action' => 'add',
        ], $request->all());

        return $this->sendRequest($data);
    }

    /** Get single order status */
    public function status($orderId)
    {
        return $this->sendRequest([
            'key'   => $this->apiKey,
            'action'=> 'status',
            'order' => $orderId
        ]);
    }

    /** Get multiple orders status */
    public function multiStatus(Request $request)
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'status',
            'orders' => implode(',', (array) $request->input('orders'))
        ]);
    }

    /** Get all services */
    public function services()
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'services'
        ]);
    }

    /** Refill order */
    public function refill($orderId)
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'refill',
            'order'  => $orderId
        ]);
    }

    /** Refill multiple orders */
    public function multiRefill(Request $request)
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'refill',
            'orders' => implode(',', (array) $request->input('orders'))
        ]);
    }

    /** Refill status */
    public function refillStatus($refillId)
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'refill_status',
            'refill' => $refillId
        ]);
    }

    /** Multiple refill statuses */
    public function multiRefillStatus(Request $request)
    {
        return $this->sendRequest([
            'key'     => $this->apiKey,
            'action'  => 'refill_status',
            'refills' => implode(',', (array) $request->input('refills'))
        ]);
    }

    /** Cancel multiple orders */
    public function cancel(Request $request)
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'cancel',
            'orders' => implode(',', (array) $request->input('orders'))
        ]);
    }

    /** Get balance */
    public function balance()
    {
        return $this->sendRequest([
            'key'    => $this->apiKey,
            'action' => 'balance'
        ]);
    }

    /** Reusable request method */
    private function sendRequest(array $postData)
    {
        $response = Http::asForm()->post($this->apiUrl, $postData);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => true,
            'message' => 'API request failed',
            'status' => $response->status()
        ], $response->status());
        
    }
}

