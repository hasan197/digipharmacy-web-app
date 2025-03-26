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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('type'); // stock_in, stock_out, adjustment, sales, return, expired
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->nullableMorphs('reference'); // For polymorphic relations (e.g., sales_orders, purchase_orders)
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
}; 