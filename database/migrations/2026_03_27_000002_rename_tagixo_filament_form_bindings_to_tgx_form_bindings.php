<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('tagixo_filament_form_bindings') &&
            ! Schema::hasTable('tgx_form_bindings')
        ) {
            Schema::rename('tagixo_filament_form_bindings', 'tgx_form_bindings');
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('tgx_form_bindings') &&
            ! Schema::hasTable('tagixo_filament_form_bindings')
        ) {
            Schema::rename('tgx_form_bindings', 'tagixo_filament_form_bindings');
        }
    }
};
