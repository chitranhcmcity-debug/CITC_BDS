<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nạp tiền
        Schema::create('nap_tien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->integer('so_tien');
            $table->integer('so_tien_khuyen_mai')->default(0);
            $table->integer('tong_cong');
            $table->string('phuong_thuc', 50)->default('chuyen_khoan');
            $table->string('ma_giao_dich', 100);
            $table->enum('trang_thai', ['cho_duyet', 'da_duyet', 'tu_choi'])->default('cho_duyet');
            $table->text('ghi_chu')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->index('ma_nguoi_dung');
            $table->index(['ma_nguoi_dung', 'trang_thai', 'ngay_tao'], 'idx_deposit_user_status_created');
        });

        // Chi tiêu
        Schema::create('chi_tieu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ma_nguoi_dung');
            $table->unsignedBigInteger('ma_du_an')->nullable();
            $table->string('loai', 50);
            $table->text('mo_ta')->nullable();
            $table->integer('so_tien');
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('ma_du_an')->references('id')->on('du_an')->nullOnDelete();
            $table->index('ma_nguoi_dung');
            $table->index('ma_du_an');
            $table->index(['ma_nguoi_dung', 'loai', 'ngay_tao'], 'idx_expense_user_type_created');
        });

        // Gói đẩy tin (VIP packages)
        Schema::create('goi_dich_vu', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 100);
            $table->text('mo_ta')->nullable();
            $table->bigInteger('gia');
            $table->integer('so_ngay')->default(30);
            $table->tinyInteger('cap_do')->default(1);
            $table->enum('trang_thai', ['hoat_dong', 'ngung_hoat_dong'])->default('hoat_dong');
            $table->timestamp('ngay_tao')->useCurrent();
        });

        // Conversations (chat)
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_one');
            $table->unsignedBigInteger('user_two');
            $table->unsignedBigInteger('ma_du_an')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('user_one')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('user_two')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('ma_du_an')->references('id')->on('du_an')->nullOnDelete();
            $table->unique(['user_one', 'user_two', 'ma_du_an']);
        });

        // Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamp('ngay_tao')->useCurrent();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->index(['conversation_id', 'ngay_tao']);
        });

        // Live Chat Conversations
        Schema::create('hoi_thoai_truc_tuyen', function (Blueprint $table) {
            $table->id();
            $table->string('guest_token', 64)->nullable()->index();
            $table->unsignedBigInteger('ma_nguoi_dung')->nullable()->index();
            $table->unsignedBigInteger('assigned_staff_id')->nullable()->index();
            $table->string('customer_name', 150)->nullable();
            $table->string('customer_email', 150)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('subject', 255)->nullable();
            $table->enum('status', ['waiting', 'open', 'closed'])->default('waiting');
            $table->boolean('priority')->default(false);
            $table->text('last_message')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->integer('unread_admin')->default(0);
            $table->integer('unread_customer')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('closed_at')->nullable();

            $table->index(['status', 'updated_at']);
            $table->foreign('ma_nguoi_dung')->references('id')->on('nguoi_dung')->nullOnDelete();
            $table->foreign('assigned_staff_id')->references('id')->on('nguoi_dung')->nullOnDelete();
        });

        // Live Chat Messages
        Schema::create('tin_nhan_truc_tuyen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->enum('sender_type', ['guest', 'customer', 'staff', 'admin', 'system']);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name', 150)->nullable();
            $table->text('message');
            $table->string('attachment_path', 255)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_customer')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->index(['conversation_id', 'id']);
            $table->foreign('conversation_id')->references('id')->on('live_chat_conversations')->cascadeOnDelete();
        });

        // OTP codes
        Schema::create('ma_otp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('otp_code', 64);
            $table->string('purpose', 50)->default('verify_phone');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->index(['user_id', 'purpose']);
        });

        // Token table (remember me, email verify, password reset)
        Schema::create('ma_truy_cap_nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('token', 255);
            $table->string('type', 50);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->index(['token', 'type']);
            $table->index('user_id');
        });

        // RBAC permissions
        Schema::create('quyen_han', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 150)->unique();
            $table->string('module', 80);
            $table->string('action', 40);
            $table->string('description', 255)->nullable();
            $table->boolean('is_system')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->index(['module', 'action']);
        });

        Schema::create('vai_tro_quyen_han', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->dateTime('created_at')->useCurrent();

            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('id')->on('vai_tro')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->index('permission_id');
        });

        Schema::create('vai_tro_nguoi_dung', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->dateTime('created_at')->useCurrent();

            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('vai_tro')->cascadeOnDelete();
            $table->index('role_id');
        });

        // Laravel session table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('vai_tro_nguoi_dung');
        Schema::dropIfExists('vai_tro_quyen_han');
        Schema::dropIfExists('quyen_han');
        Schema::dropIfExists('ma_truy_cap_nguoi_dung');
        Schema::dropIfExists('ma_otp');
        Schema::dropIfExists('tin_nhan_truc_tuyen');
        Schema::dropIfExists('hoi_thoai_truc_tuyen');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('goi_dich_vu');
        Schema::dropIfExists('chi_tieu');
        Schema::dropIfExists('nap_tien');
    }
};
