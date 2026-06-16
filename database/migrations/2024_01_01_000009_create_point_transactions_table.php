<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('point_rule_id')->nullable()->constrained('point_rules')->onDelete('set null');
            $table->integer('points');
            $table->string('reason')->nullable();
            $table->boolean('is_reward_claim')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('point_transactions'); }
};
