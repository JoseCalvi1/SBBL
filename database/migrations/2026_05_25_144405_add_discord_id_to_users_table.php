<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscordIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'discord_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('discord_id')->nullable()->unique('discord_id')->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'discord_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('discord_id');
                $table->dropColumn('discord_id');
            });
        }
    }
}
