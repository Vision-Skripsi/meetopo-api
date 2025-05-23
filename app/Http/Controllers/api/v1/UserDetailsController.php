<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Storage;

class UserDetailsController extends Controller
{
    /**
     * Get user details by user ID
     */
    public function show($user_id)
    {
        $details = UserDetails::where('user_id', $user_id)->first();

        if (!$details) {
            $details = UserDetails::create([
                'user_id' => $user_id,
                'phone' => '',
                'address' => '',
                'id_card' => '',
                'photo' => '',
            ]);
        }

        return response()->json($details, 200);
    }

    /**
     * Update or create user details
     */
    public function update(Request $request, $user_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $details = UserDetails::where('user_id', $user_id)->first();

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
        $s3 = Storage::disk('s3');
        $photoUrl = $details->photo ?? null;
    
        if ($request->hasFile('photo')) {
            if ($photoUrl) {
                $oldPath = parse_url($photoUrl, PHP_URL_PATH);
                $oldKey = ltrim($oldPath, '/');
                if ($s3->exists($oldKey)) {
                    $s3->delete($oldKey);
                }
            }
    
            $photoPath = $request->file('photo')->store('users', 's3');
            $s3->setVisibility($photoPath, 'public');
            $photoUrl = $s3->url($photoPath);
        }

        $details = UserDetails::updateOrCreate(
            ['user_id' => $user_id],
            [
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoUrl,
            ]
        );

        if ($request->filled('name')) {
            $user = $details->user;
            if ($user) {
                $user->update(['name' => $request->name]);
            }
        }

        return response()->json($details, 200);
    }
}
