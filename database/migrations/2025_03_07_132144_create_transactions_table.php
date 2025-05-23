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
        Schema::create('transactions', function (Blueprint $table) {
            if (config('database.default') == 'pgsql')
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            else if (config('database.default') == 'mysql')
                $table->uuid('id')->primary()->default(DB::raw('uuid()'));
            $table->foreignUuid('user_id')->constrained('users'); 
            $table->foreignUuid('outlet_id')->constrained('outlets'); 
            $table->foreignUuid('table_id')->constrained('tables'); 
            $table->boolean('is_closed')->default(false);
            $table->decimal('tax', 10, 2);
            $table->decimal('service_charge', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
