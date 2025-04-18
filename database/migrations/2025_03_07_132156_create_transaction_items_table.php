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
        Schema::create('transaction_items', function (Blueprint $table) {
            if (config('database.default') == 'pgsql')
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            else if (config('database.default') == 'mysql')
                $table->uuid('id')->primary()->default(DB::raw('uuid()'));
            $table->foreignUuid('transaction_id')->constrained('transactions'); 
            $table->uuid('menu_id');
            $table->string('menu_name');
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
