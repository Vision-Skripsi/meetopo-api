<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Outlet; // Assuming you have an Outlet model
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Facades\Storage;

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
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('outlets', 's3');
            
            /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
            $s3 = Storage::disk('s3');
            
            $s3->setVisibility($photoPath, 'public');
            $photoUrl = $s3->url($photoPath);
        }

        // Handle photo upload if provided (you'll need to implement this logic)

        $outlet = new Outlet($request->all());
        $outlet->user_id = auth()->user()->id; // Associate the outlet with the authenticated user
        if ($photoUrl) {
            $outlet->photo = $photoUrl;
        }
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
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'address_one' => 'nullable|string|max:255',
            'address_two' => 'nullable|string|max:255',
            'phone_one' => 'nullable|string|max:20',
            'phone_two' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
            $s3 = Storage::disk('s3');
    
            if ($outlet->photo) {
                $oldPath = parse_url($outlet->photo, PHP_URL_PATH);
                $oldKey = ltrim($oldPath, '/');
                if ($s3->exists($oldKey)) {
                    $s3->delete($oldKey);
                }
            }
    
            $photoPath = $request->file('photo')->store('outlets', 's3');
            $s3->setVisibility($photoPath, 'public');
            $photoUrl = $s3->url($photoPath);
        }
    
        // Only update fields that are present in the request
        $outlet->fill($request->only([
            'name', 'address_one', 'address_two',
            'phone_one', 'phone_two', 'email',
            'latitude', 'longitude'
        ]));
    
        if ($photoUrl) {
            $outlet->photo = $photoUrl;
        }
    
        $outlet->save();
    
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