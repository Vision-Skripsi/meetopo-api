<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menus;
use Illuminate\Support\Facades\Storage;

class MenusController extends Controller
{
    /**
     * Handle bulk insertion of menus.
     */
    public function bulkInsert(Request $request)
    {
        $validated = $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|uuid',
            'menus.*.outlet_id' => 'required|uuid',
            'menus.*.name' => 'required|string|max:255',
            'menus.*.price' => 'required|numeric',
            'menus.*.category' => 'required|in:Appetizer,Dessert,Drinks,Food,Vegetarian',
            'menus.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $responseMenus = [];

            foreach ($validated['menus'] as $index => $menu) {
                $imageKey = "image_$index";
                $imageUrl = $menu['image'] ?? null;

                $existingMenu = Menus::where('id', $menu['id'])
                    ->where('outlet_id', $menu['outlet_id'])
                    ->first();

                if ($request->hasFile($imageKey)) {
                    if ($existingMenu && $existingMenu->image && str_contains($existingMenu->image, 'amazonaws.com')) {
                        $oldPath = parse_url($existingMenu->image, PHP_URL_PATH);
                        $oldKey = ltrim($oldPath, '/');
                        Storage::disk('s3')->delete($oldKey);
                    }

                    $file = $request->file($imageKey);
                    $path = $file->store('menus', 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');

                    /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
                    $s3 = Storage::disk('s3');
                    $imageUrl = $s3->url($path);
                }

                Menus::updateOrCreate(
                    [
                        'id' => $menu['id'],
                        'outlet_id' => $menu['outlet_id'],
                    ],
                    [
                        'name' => $menu['name'],
                        'price' => $menu['price'],
                        'category' => $menu['category'],
                        'image' => $imageUrl,
                        'updated_at' => now(),
                    ]
                );
            }

            $responseMenus[] = [
                'id' => $menu['id'],
                'outlet_id' => $menu['outlet_id'],
                'name' => $menu['name'],
                'price' => $menu['price'],
                'category' => $menu['category'],
                'image' => $imageUrl,
            ];

            return response()->json([
                'message' => 'Menus inserted or updated successfully.',
                'data' => $responseMenus,
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

    public function destroy($id)
    {
        try {
            $menu = Menus::find($id);

            if (!$menu) {
                return response()->json([
                    'message' => 'Menu not found, nothing to delete.',
                ], 204);
            }

            if ($menu->image && str_contains($menu->image, 'amazonaws.com')) {
                $oldPath = parse_url($menu->image, PHP_URL_PATH);
                $oldKey = ltrim($oldPath, '/');
                Storage::disk('s3')->delete($oldKey);
            }

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
