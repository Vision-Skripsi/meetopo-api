<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function bulkInsert(Request $request)
    {
        $validated = $request->validate([
            '*.id' => 'required|uuid',
            '*.user_id' => 'required|uuid',
            '*.outlet_id' => 'required|uuid',
            '*.table_id' => 'required|uuid',
            '*.is_closed' => 'required|boolean',
            '*.tax' => 'required|numeric',
            '*.service_charge' => 'required|numeric',
            '*.created_at' => 'nullable|date',
            '*.items' => 'required|array',
            '*.items.*.id' => 'required|uuid',
            '*.items.*.menu_id' => 'required|uuid',
            '*.items.*.menu_name' => 'required|string',
            '*.items.*.price' => 'required|numeric',
            '*.items.*.quantity' => 'required|integer',
        ]);

        try {
            foreach ($validated as $transactionData) {
                $transaction = Transaction::updateOrCreate(
                    [
                        'id' => $transactionData['id'],
                        'outlet_id' => $transactionData['outlet_id']
                    ],
                    [
                        'user_id' => $transactionData['user_id'],
                        'table_id' => $transactionData['table_id'],
                        'is_closed' => $transactionData['is_closed'],
                        'tax' => $transactionData['tax'],
                        'service_charge' => $transactionData['service_charge'],
                        'created_at' => $transactionData['created_at']
                    ]
                );

                foreach ($transactionData['items'] as $item) {
                    TransactionItem::updateOrCreate(
                        [
                            'id' => $item['id'],
                            'transaction_id' => $transaction->id
                        ],
                        [
                            'menu_id' => $item['menu_id'],
                            'menu_name' => $item['menu_name'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                        ]
                    );
                }
            }

            return response()->json(['message' => 'Transactions inserted or updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getTransactionsByOutletId($outlet_id)
    {
        try {
            $transactions = Transaction::where('outlet_id', $outlet_id)->with('items')->get();

            if ($transactions->isEmpty()) {
                return response()->json(['message' => 'No transactions found for this outlet.'], 404);
            }

            return response()->json($transactions, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching transactions.', 'error' => $e->getMessage()], 500);
        }
    }
}
