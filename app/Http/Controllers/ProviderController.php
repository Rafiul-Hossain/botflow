<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class ProviderController extends Controller
{
    public function index()
    {
        try {
            $providers = Provider::query()->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Providers retrieved successfully.',
                'data'    => $providers
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch providers.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'api_name'  => ['required','string','max:225'],
                'api_url'   => ['required','url'],
                'api_key'   => ['required','string','max:225'],
                'api_type'  => ['required','integer'],
                'api_limit' => ['nullable','numeric'],
                'currency'  => ['nullable','in:INR,USD'],
                'api_alert' => ['nullable','in:1,2'],
                'status'    => ['nullable','in:1,2'],
            ]);

            $provider = Provider::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Provider created successfully.',
                'data'    => $provider
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create provider.',
                'error'   => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        try {
            $provider = Provider::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Provider retrieved successfully.',
                'data'    => $provider
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found.',
                'error'   => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $provider = Provider::findOrFail($id);

            $data = $request->validate([
                'api_name'  => ['sometimes','string','max:225'],
                'api_url'   => ['sometimes','url'],
                'api_key'   => ['sometimes','string','max:225'],
                'api_type'  => ['sometimes','integer'],
                'api_limit' => ['sometimes','numeric'],
                'currency'  => ['nullable','in:INR,USD'],
                'api_alert' => ['nullable','in:1,2'],
                'status'    => ['nullable','in:1,2'],
            ]);

            $provider->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Provider updated successfully.',
                'data'    => $provider->fresh()
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update provider.',
                'error'   => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            $provider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Provider deleted successfully.'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete provider.',
                'error'   => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
