<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('kind');
            $table->date('for_charge_on');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['sale_id', 'kind', 'for_charge_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_reminders');
    }
};
