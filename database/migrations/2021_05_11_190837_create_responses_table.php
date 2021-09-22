<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //外部キー付き
            $table->foreignId('thread_id')->comment('スレッドID')->default(1)
                ->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('origin_d_post_id')->comment('返信元表示上ポストID')->default(null);
            $table->unsignedBigInteger('dest_d_post_id')->comment('返信先表示上ポストID')->default(null);
            //複合uniqueキー
            $table->unique(['thread_id', 'origin_d_post_id', 'dest_d_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('responses');
    }
}
