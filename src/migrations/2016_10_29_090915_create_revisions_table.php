<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revisions', function(Blueprint $table) {
            $table->increments('id');
            $table->string('revisionable_type')->index();
            $table->integer('revisionable_id')->unsigned()->index();
            $table->string('type')->index();
            $table->integer('user_id')->unsigned()->index();
            $table->string('field')->index()->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

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
        Schema::drop('revisions');
    }
}
