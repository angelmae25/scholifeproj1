<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('organizer');
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->enum('type', ['on_campus', 'academic', 'organization', 'online'])->default('on_campus');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->integer('rsvp_count')->default(0);
            $table->integer('attendance_count')->default(0);
            $table->integer('reminders_sent')->default(0);
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('events'); }
};
