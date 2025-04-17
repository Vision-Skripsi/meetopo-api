<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Outlet;

class UserOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'Owner')->get();
        foreach($users as $user){
            Outlet::create([
                'name' => fake()->name . ' - Outlet',
                'user_id' => $user->id,
                'address_one' => fake()->address,
                'address_two' => null,
                'phone_one' => fake()->phoneNumber,
                'phone_two' => null,
                'email' => fake()->safeEmail,
                'photo' => null,
                'latitude' => fake()->latitude,
                'longitude' => fake()->longitude,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
