<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->date('sold_at')->index();
            $table->decimal('amount', 15, 2);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_interval')->nullable();
            $table->date('next_charge_at')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_recurring', 'next_charge_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
