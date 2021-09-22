<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMuteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mute_users', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('muting_user_id')->comment('ミュートするユーザーID');
            $table->foreignId('user_id')->comment('ミュートされたユーザーID')
                ->constrained()->onDelete('cascade')->onUpdate('cascade');
            //外部キー古い書き方で なぜなら列名が参照先テーブル名+列名ではないから
            $table->foreign('muting_user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            //複合uniqueキー
            $table->unique(['muting_user_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mute_users');
    }
}
