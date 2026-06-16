<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // Marketplace, Lost & Found, etc.
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->integer('reporter_count')->default(1);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reports'); }
};
