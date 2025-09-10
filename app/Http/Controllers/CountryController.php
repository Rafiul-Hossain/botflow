<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use Exception;

class CountryController extends Controller
{
    /**
     * Display a listing of the countries.
     */
    public function index()
    {
        try {
            $countries = Country::all();

            return response()->json([
                'success' => true,
                'data' => $countries
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created country.
     */
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'country_name' => 'required|string|max:255|unique:countries,country_name',
                'icon'         => 'nullable|file|max:20480'
            ]);

            // Handle file upload
            if ($request->hasFile('icon')) {
                // Store file in public/icons folder
                $fileName = time() . '_' . $request->file('icon')->getClientOriginalName();
                $filePath = $request->file('icon')->storeAs('icons', $fileName, 'public');
                
                // Save the path in validated data
                $validated['icon'] = 'storage/' . $filePath;
            }

            $country = Country::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Country created successfully',
                'data'    => $country
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create country',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified country.
     */
    public function show($id)
    {
        try {
            $country = Country::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $country
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified country.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'country_name' => 'required|string|max:255|unique:countries,country_name,' . $id,
                'icon' => 'nullable|file|max:20480'
            ]);

            if ($request->hasFile('icon')) {
                // Store file in public/icons folder
                $fileName = time() . '_' . $request->file('icon')->getClientOriginalName();
                $filePath = $request->file('icon')->storeAs('icons', $fileName, 'public');
                
                // Save the path in validated data
                $validated['icon'] = 'storage/' . $filePath;
            }

            $country = Country::findOrFail($id);
            $country->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Country updated successfully',
                'data' => $country
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update country',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified country.
     */
    public function destroy($id)
    {
        try {
            $country = Country::findOrFail($id);
            $country->delete();

            return response()->json([
                'success' => true,
                'message' => 'Country deleted successfully'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete country',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
