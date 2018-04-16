<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table_name', 200);
            $table->text('config');
            $table->longText('creating_o')->nullable();
            $table->longText('created_o')->nullable();
            $table->longText('updating_o')->nullable();
            $table->longText('updated_o')->nullable();
            $table->longText('deleting_o')->nullable();
            $table->longText('deleted_o')->nullable();
            $table->longText('restoring_o')->nullable();
            $table->longText('restored_o')->nullable();
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
        Schema::dropIfExists('api_config');
    }
}