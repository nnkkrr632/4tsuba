<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMuteWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mute_words', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //外部キー付き
            $table->foreignId('user_id')->comment('ユーザーID')->default(5)
                ->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('mute_word', 10)->comment('ミュートワード');
            //複合uniqueキー
            $table->unique(['user_id', 'mute_word']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mute_words');
    }
}
