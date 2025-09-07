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
        Schema::table('vans', function (Blueprint $table) {
            $table->enum('air_conditioning', ['yes', 'no'])->nullable()->after('fuel_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->dropColumn('air_conditioning');
        });
    }
};
