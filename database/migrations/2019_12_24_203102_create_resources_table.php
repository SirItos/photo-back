<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('address')->nullable();
            $table->decimal('long',10,7)->default(0.0); 
            $table->decimal('lat',10,7)->default(0.0);
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('resource_type')->default(0);
           
            $table->integer('min_cost')->default(0);
            $table->integer('max_cost')->default(2);
            $table->boolean('activated')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
