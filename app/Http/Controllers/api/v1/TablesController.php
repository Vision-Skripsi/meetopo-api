<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use Illuminate\Support\Str;

class TablesController extends Controller
{
    /**
     * Handle bulk insertion of tables.
     */
    public function bulkInsert(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            '*.id' => 'required|uuid',
            '*.outlet_id' => 'required|uuid',
            '*.number' => 'required|string|max:10',
            '*.is_available' => 'required|boolean',
        ]);

        try {
            foreach ($validated as $table) {
                Table::updateOrCreate(
                    [
                        'id' => $table['id'],
                        'outlet_id' => $table['outlet_id'],
                    ],
                    [
                        'number' => $table['number'],
                        'is_available' => $table['is_available'],
                        'updated_at' => now()
                    ]
                );
            }

            return response()->json([
                'message' => 'Tables inserted or updated successfully.',
                'data' => $validated,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while inserting/updating tables.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tables by outlet_id.
     */
    public function getTablesByOutletId($outlet_id)
    {
        try {
            $tables = Table::where('outlet_id', $outlet_id)->get()->map(function ($table) {
                return [
                    'id' => $table->id,
                    'outlet_id' => $table->outlet_id,
                    'number' => $table->number,
                    'is_available' => $table->is_available,
                    'created_at' => $table->created_at,
                    'updated_at' => $table->updated_at,
                    'deleted_at' => $table->deleted_at,
                    'locked_by' => $table->locked_by,
                ];
            });

            if ($tables->isEmpty()) {
                return response()->json([
                    'message' => 'No tables found for the given outlet ID.',
                ], 404);
            }

            return response()->json($tables, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching tables.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function lockTable($id, Request $request)
    {
        try {
            $table = Table::findOrFail($id);

            if (!$table->is_available) {
                return response()->json([
                    'message' => 'Table is already in use.',
                ], 409);
            }

            $table->is_available = false;
            $table->locked_by = $request->user()->id;
            $table->save();

            return response()->json([
                'message' => 'Table successfully locked.',
                'data' => $table,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to lock the table.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function unlockTable($id, Request $request)
    {
        try {
            $table = Table::findOrFail($id);

            if ($table->is_available) {
                return response()->json([
                    'message' => 'Table is already available.',
                ], 409);
            }

            if ($table->locked_by !== $request->user()->id) {
                return response()->json([
                    'message' => 'You do not have permission to unlock this table.',
                ], 403);
            }

            $table->is_available = true;
            $table->locked_by = null;
            $table->save();

            return response()->json([
                'message' => 'Table successfully unlocked.',
                'data' => $table,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unlock the table.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}