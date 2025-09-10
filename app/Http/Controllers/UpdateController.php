<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Update;
use Exception;
use Illuminate\Support\Facades\Http;


class UpdateController extends Controller
{
    /**
     * Display a listing of updates.
     */
    public function index()
    {
        try {
            $updates = Update::all();

            return response()->json([
                'success' => true,
                'data' => $updates
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch updates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created update.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'      => 'required|integer',
                'service_id'   => 'required|integer',
                'action'       => 'required|string|max:255',
                'date'         => 'required|date',
                'description'  => 'nullable|string',
                'price'        => 'required|numeric|min:0',
                'discount'     => 'nullable|numeric|min:0',
                'vat'          => 'nullable|numeric|min:0',
            ]);

            $vat = $validated['vat'] ?? 0;
            $discount = $validated['discount'] ?? 0;

            // Apply VAT first, then discount
            $priceWithVat = $validated['price'] + ($vat * $validated['price'] / 100);
            $priceAfterDiscount = $priceWithVat - ($discount * $validated['price'] / 100);

            $validated['update_price'] = $priceAfterDiscount;
            $update = Update::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Update created successfully',
                'data' => $update
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified update.
     */
    public function show($id)
    {
        try {
            $update = Update::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $update
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified update.
     */
    public function update(Request $request, $id)
 {
        try {
            $validated = $request->validate([
                'user_id'      => 'required|integer',
                'service_id'   => 'required|integer',
                'action'       => 'required|string|max:255',
                'date'         => 'required|date',
                'description'  => 'nullable|string',
                'price'        => 'required|numeric|min:0',
                'discount'     => 'nullable|numeric|min:0',
                'vat'          => 'nullable|numeric|min:0',
            ]);

            $vat = $validated['vat'] ?? 0;
            $discount = $validated['discount'] ?? 0;

            // Apply VAT first, then discount
            $priceWithVat = $validated['price'] + ($vat * $validated['price'] / 100);
            $priceAfterDiscount = $priceWithVat - ($discount * $validated['price'] / 100);

            $validated['update_price'] = $priceAfterDiscount;

            $update = Update::findOrFail($id);
            $update->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Update updated successfully',
                'data'    => $update
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified update.
     */
    public function destroy($id)
    {
        try {
            $update = Update::findOrFail($id);
            $update->delete();

            return response()->json([
                'success' => true,
                'message' => 'Update deleted successfully'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function test()
    {
        //dd("test");
        // Example external API call
       $data = Http::get('https://dummyjson.com/products?delay=10');

        return response()->json([
            'success' => true,
            'message' => 'UpdateController is working',
            'data' => $data->json(),
        ]);
    }
}
