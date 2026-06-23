<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tgx_form_bindings')) {
            return;
        }

        Schema::create('tgx_form_bindings', function (Blueprint $table) {
            $table->id();

            // Reference to host app form schema record.
            $table->unsignedBigInteger('form_schema_id');

            // Target owner in Filament SDK.
            $table->string('target_type', 50)->default('resource'); // resource|livewire|class
            $table->string('target_class');
            $table->string('target_operation', 50)->nullable(); // create|edit|view|...

            // Multiple bindings can coexist; highest priority wins.
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['target_type', 'target_class'], 'tgx_ffb_target_idx');
            $table->index(['target_type', 'target_class', 'target_operation'], 'tgx_ffb_target_operation_idx');
            $table->index(['form_schema_id'], 'tgx_ffb_form_schema_idx');
            $table->index(['is_active', 'priority'], 'tgx_ffb_active_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tgx_form_bindings');
    }
};
