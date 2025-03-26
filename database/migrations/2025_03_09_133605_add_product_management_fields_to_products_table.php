<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('status')->default('active')->after('requires_prescription');
            $table->string('sku')->nullable()->after('status');
            $table->string('barcode')->nullable()->after('sku');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->boolean('requires_prescription')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['status', 'sku', 'barcode', 'cost_price']);
            $table->boolean('requires_prescription')->default(null)->change();
        });
    }
};
