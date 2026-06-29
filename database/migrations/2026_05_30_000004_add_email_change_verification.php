<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['email' => 'customer-'.$user->id.'@ashvalian.local']);
            });

        if ($this->indexExists('users', 'users_phone_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_phone_unique');
            });
        }

        if (Schema::hasColumn('pending_registrations', 'phone')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }

        if ($this->driver() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable(false)->change();
            });
        }

        if (! Schema::hasColumn('users', 'email_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_changed_at')->nullable();
            });
        }

        Schema::create('pending_email_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('new_email')->index();
            $table->string('otp_code', 6);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_email_changes');

        if (Schema::hasColumn('users', 'email_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_changed_at');
            });
        }

        if (! Schema::hasColumn('pending_registrations', 'phone')) {
            Schema::table('pending_registrations', function (Blueprint $table) {
                $table->string('phone')->nullable()->index();
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        if ($this->driver() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        if ($this->driver() === 'mysql') {
            return DB::select('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', [$index]) !== [];
        }

        return false;
    }

    private function driver(): string
    {
        return Schema::getConnection()->getDriverName();
    }
};
