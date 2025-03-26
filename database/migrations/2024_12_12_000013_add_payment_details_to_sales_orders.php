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
        Schema::table('sales_orders', function (Blueprint $table) {
            // Periksa apakah kolom payment_method sudah ada
            if (!Schema::hasColumn('sales_orders', 'payment_method')) {
                $table->string('payment_method')->after('grand_total');
            } else {
                // Jika kolom sudah ada, ubah tipenya menjadi string
                DB::statement('ALTER TABLE sales_orders MODIFY payment_method VARCHAR(255) NOT NULL');
            }
            
            // Tambahkan kolom payment_details jika belum ada
            if (!Schema::hasColumn('sales_orders', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'payment_details')) {
                $table->dropColumn('payment_details');
            }
        });
    }
};
