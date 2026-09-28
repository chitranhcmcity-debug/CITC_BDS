<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Post Analytics
        Schema::create('post_analytics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Owner of the listing');
            $table->unsignedBigInteger('actor_user_id')->nullable()->comment('Signed-in visitor');
            $table->enum('type', ['view', 'call', 'chat', 'save', 'share', 'phone', 'zalo', 'contact']);
            $table->unsignedInteger('value')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->char('visitor_hash', 64)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->enum('source', ['google', 'facebook', 'zalo', 'telegram', 'direct', 'other'])->default('direct');
            $table->json('metadata')->nullable();
            $table->bigInteger('dedupe_window')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['post_id', 'type', 'visitor_hash', 'dedupe_window'], 'uq_analytics_view_window');
            $table->index(['post_id', 'created_at'], 'idx_analytics_post_created');
            $table->index(['user_id', 'created_at'], 'idx_analytics_user_created');
            $table->index(['type', 'created_at'], 'idx_analytics_type_created');
            $table->index(['post_id', 'type', 'created_at'], 'idx_analytics_post_type_created');
            $table->index(['user_id', 'type', 'created_at'], 'idx_analytics_user_type_created');

            $table->foreign('post_id')->references('id')->on('du_an')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('module', 80);
            $table->string('target_type', 80)->nullable();
            $table->bigInteger('target_id')->nullable();
            $table->string('description', 1000)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('ip_address');
            $table->foreign('user_id')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // Admin Logs
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('action', 100);
            $table->string('module', 80);
            $table->string('target_type', 80)->nullable();
            $table->bigInteger('target_id')->nullable();
            $table->string('description', 1000)->nullable();
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['admin_id', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['target_type', 'target_id']);
            $table->index('ip_address');
            $table->foreign('admin_id')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // Login History
        Schema::create('login_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email', 190)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->string('device', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->enum('status', ['success', 'failed', 'blocked', 'logout']);
            $table->string('fail_reason', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->foreign('user_id')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // Error Logs
        Schema::create('error_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module', 80)->default('system');
            $table->enum('level', ['info', 'warning', 'error', 'critical'])->default('error');
            $table->string('error_type', 190)->nullable();
            $table->text('message');
            $table->longText('stack_trace')->nullable();
            $table->string('request_url', 1500)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();
            $table->boolean('resolved')->default(false);
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['level', 'created_at']);
            $table->index(['resolved', 'created_at']);
            $table->foreign('user_id')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // System Log Permissions
        Schema::create('system_log_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->enum('log_type', ['activity', 'admin', 'login', 'error']);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_manage')->default(false);

            $table->primary(['role_id', 'log_type']);
            $table->foreign('role_id')->references('id')->on('vai_tro')->cascadeOnDelete();
        });

        // Seed log permissions for Admin
        DB::table('system_log_permissions')->insert([
            ['role_id' => 1, 'log_type' => 'activity', 'can_view' => 1, 'can_export' => 1, 'can_manage' => 1],
            ['role_id' => 1, 'log_type' => 'admin', 'can_view' => 1, 'can_export' => 1, 'can_manage' => 1],
            ['role_id' => 1, 'log_type' => 'login', 'can_view' => 1, 'can_export' => 1, 'can_manage' => 1],
            ['role_id' => 1, 'log_type' => 'error', 'can_view' => 1, 'can_export' => 1, 'can_manage' => 1],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_log_permissions');
        Schema::dropIfExists('error_logs');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('post_analytics');
    }
};
