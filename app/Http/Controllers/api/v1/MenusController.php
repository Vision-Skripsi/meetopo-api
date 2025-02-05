<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menus;

class MenusController extends Controller
{
    /**
     * Handle bulk insertion of menus.
     */
    public function bulkInsert(Request $request)
    {
        
        // Validate incoming payload
        $validated = $request->validate([
            '*.id' => 'required|uuid',
            '*.outlet_id' => 'required|uuid',  // Ensure outlet_id is a valid UUID
            '*.name' => 'required|string|max:255',
            '*.image' => 'nullable|string',
            '*.price' => 'required|numeric', // Adjust validation based on your price format
            '*.category' => 'required|string|max:255',
        ]);

        try {
            foreach ($validated as $menu) {
                Menus::updateOrCreate(
                    [
                        'id' => $menu['id'],
                        'outlet_id' => $menu['outlet_id'],
                    ],
                    [
                        'name' => $menu['name'],
                        'image' => $menu['image'] ?? null,
                        'price' => $menu['price'],
                        'category' => $menu['category'],
                        'updated_at' => now()
                    ]
                );
            }
    
            return response()->json([
                'message' => 'Menus inserted or updated successfully.',
                'data' => $validated,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while inserting/updating menus.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMenusByOutletId($outlet_id)
    {
        try {
            // Fetch menus associated with the given outlet_id
            $menus = Menus::where('outlet_id', $outlet_id)->get();

            if ($menus->isEmpty()) {
                return response()->json([
                    'message' => 'No menus found for the given outlet ID.',
                ], 404);
            }

            return response()->json($menus, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching menus.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Menus $menu)
    {
        try {
            $menu->delete();
    
            return response()->json([
                'message' => 'Menu deleted successfully.',
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the menu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
