<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            if(config('database.default') == 'pgsql')
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            else if(config('database.default') == 'mysql')
                $table->uuid('id')->primary()->default(DB::raw('uuid()'));
            $table->foreignUuid('user_id')->constrained('users'); 
            $table->string('name')->unique();;
            $table->string('address_one');
            $table->string('address_two')->nullable(); 
            $table->string('phone_one');
            $table->string('phone_two')->nullable();
            $table->string('email');
            $table->decimal('tax', 10, 2);
            $table->decimal('service_charge', 10, 2);
            $table->string('photo'); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};