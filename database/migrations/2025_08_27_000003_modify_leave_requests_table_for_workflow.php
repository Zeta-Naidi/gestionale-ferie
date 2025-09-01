<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('approver_id')->nullable()->after('manager_id');
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            
            // Update status enum to include cancelled
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->change();
            
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['status', 'approver_id']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approver_id']);
            $table->dropIndex(['status', 'approver_id']);
            $table->dropColumn(['approver_id', 'cancelled_at', 'cancellation_reason']);
            
            // Revert status enum
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
