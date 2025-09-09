<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('insurance_doc')->nullable()->after('availability_status');
            $table->string('registration_doc')->nullable()->after('insurance_doc');
            $table->string('roadtax_doc')->nullable()->after('registration_doc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['insurance_doc','registration_doc','roadtax_doc']);
        });
    }
};
