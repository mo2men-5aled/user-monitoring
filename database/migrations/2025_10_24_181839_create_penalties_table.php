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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reason'); // reason for penalty (idle session, etc.)
            $table->integer('penalty_count')->default(1); // number of penalties
            $table->boolean('active')->default(true); // whether penalty is currently active
            $table->timestamp('expires_at')->nullable(); // when penalty expires
            $table->timestamps();
            
            $table->index(['user_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
