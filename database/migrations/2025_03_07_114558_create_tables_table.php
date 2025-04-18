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
        Schema::create('tables', function (Blueprint $table) {
            if(config('database.default') == 'pgsql')
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            else if(config('database.default') == 'mysql')
                $table->uuid('id')->primary()->default(DB::raw('uuid()'));
            $table->foreignUuid('outlet_id')->constrained('outlets'); 
            $table->string('number');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
