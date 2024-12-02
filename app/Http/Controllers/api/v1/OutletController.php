<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Outlet; // Assuming you have an Outlet model
use Illuminate\Support\Facades\Validator;
use Auth;

class OutletController extends Controller
{
    // ... other controller methods ...

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()){
            return Outlet::where('user_id', Auth::user()->id)->get();
        }
        $outlets = Outlet::all(); // Fetch all outlets
        return response()->json($outlets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address_one' => 'required|string|max:255',
            'address_two' => 'nullable|string|max:255',
            'phone_one' => 'required|string|max:20',
            'phone_two' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|max:2048', // Adjust validation as needed
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle photo upload if provided (you'll need to implement this logic)

        $outlet = new Outlet($request->all());
        $outlet->user_id = auth()->user()->id; // Associate the outlet with the authenticated user
        $outlet->save();

        return response()->json($outlet, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Outlet $outlet)
    {
        return response()->json($outlet);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Outlet $outlet)
    {
        // Similar validation as in store()

        // Handle photo update if provided

        $outlet->update($request->all());
        return response()->json($outlet);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outlet $outlet)
    {
        $outlet->delete();
        return response()->json(null, 204);
    }
}