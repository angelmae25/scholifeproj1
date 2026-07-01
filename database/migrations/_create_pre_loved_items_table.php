<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pre_loved_items')) {
            Schema::create('pre_loved_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0);
                $table->string('location')->nullable();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->enum('status', ['available', 'sold'])->default('available');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_loved_items');
    }
};
