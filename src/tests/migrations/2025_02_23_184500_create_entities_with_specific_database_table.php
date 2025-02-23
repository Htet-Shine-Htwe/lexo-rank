<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEntitiesWithSpecificDatabaseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lexo_rank_entities', function (Blueprint $table) {
            $table->id();
            $table->string('position')->default('a'); // Assuming position is your sortable field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('lexo_rank_entities');
    }
}
