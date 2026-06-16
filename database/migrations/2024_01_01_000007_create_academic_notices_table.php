<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('academic_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('posted_by');
            $table->string('department');
            $table->enum('type', ['academic', 'office', 'memo'])->default('academic');
            $table->enum('status', ['published', 'pending', 'draft'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('academic_notices'); }
};
