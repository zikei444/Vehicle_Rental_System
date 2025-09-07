<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('maintenance', function (Blueprint $t) {
            $t->string('status')->default('Scheduled')->after('notes'); // Scheduled | Completed | Cancelled
            $t->timestamp('completed_at')->nullable()->after('status');
        });
    }
    public function down(): void {
        Schema::table('maintenance', function (Blueprint $t) {
            $t->dropColumn(['status','completed_at']);
        });
    }
};

