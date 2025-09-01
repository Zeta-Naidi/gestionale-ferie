<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ADMIN_IT', 'HR', 'MANAGER', 'EMPLOYEE'])->default('EMPLOYEE');
            $table->unsignedBigInteger('organizational_unit_id')->nullable();
            
            $table->foreign('organizational_unit_id')->references('id')->on('organizational_units')->onDelete('set null');
            $table->index(['role', 'organizational_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organizational_unit_id']);
            $table->dropIndex(['role', 'organizational_unit_id']);
            $table->dropColumn(['role', 'organizational_unit_id']);
        });
    }
};
