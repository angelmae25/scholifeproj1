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

            return;
        }

        Schema::table('pre_loved_items', function (Blueprint $table) {
            if (! Schema::hasColumn('pre_loved_items', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }

            if (! Schema::hasColumn('pre_loved_items', 'name')) {
                $table->string('name')->after('user_id');
            }

            if (! Schema::hasColumn('pre_loved_items', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('name');
            }

            if (! Schema::hasColumn('pre_loved_items', 'location')) {
                $table->string('location')->nullable()->after('price');
            }

            if (! Schema::hasColumn('pre_loved_items', 'description')) {
                $table->text('description')->nullable()->after('location');
            }

            if (! Schema::hasColumn('pre_loved_items', 'image')) {
                $table->string('image')->nullable()->after('description');
            }

            if (! Schema::hasColumn('pre_loved_items', 'status')) {
                $table->enum('status', ['available', 'sold'])->default('available')->after('image');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
