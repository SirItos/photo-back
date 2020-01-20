<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('resources', function (Blueprint $table) {
            $table->integer('status')->after('activated')->default(0);
       });

       Schema::table('feedback', function (Blueprint $table) {
            $table->integer('status')->after('description')->default(0);
       });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });

         Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
}
