<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoteToRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table)
        {
            $table->string('playlist_id');
            $table->integer('vote_nb');
            $table->integer('max_vote');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table)
        {
            $table->dropColumn('playlist_id');
            $table->dropColumn('vote_nb');
            $table->dropColumn('max_vote');
        });
    }
}
