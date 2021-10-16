<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//クエリビルダを使用する
use Illuminate\Support\Facades\DB;


class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //外部キー付き
            $table->foreignId('user_id')->nullable()->comment('ユーザーID')->default(5)
                ->constrained()->onDelete('set null')->onUpdate('cascade');
            $table->string('title', 100)->comment('スレッドタイトル');
            $table->boolean('is_edited')->comment('編集済みか')->storedAs('case when created_at = updated_at then 0 else 1 end');
            $table->unsignedSmallInteger('posts_count')->comment('総書込数')->default(0);
            $table->unsignedSmallInteger('likes_count')->comment('総わかる数')->default(0);

            //外部キー古い書き方
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            //bit型が用意されていないので型変換 公式が用意してないので無理にやらなくてもいいかな
            //DB::statement("ALTER TABLE threads MODIFY is_edited bit(1) NOT NULL DEFAULT 0 COMMENT '編集済みか';");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('threads');
    }
}
