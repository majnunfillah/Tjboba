<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToDbperusahaan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DBPERUSAHAAN', function (Blueprint $table) {
            $table->string('TTD_PATH')->nullable();
            $table->string('LOGO_PATH')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DBPERUSAHAAN', function (Blueprint $table) {
            $table->dropColumn('TTD_PATH');
            $table->dropColumn('LOGO_PATH');
        });
    }
}
