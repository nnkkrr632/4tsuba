<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            //外部キー付き
            $table->foreignId('thread_id')->comment('スレッドID')->default(1)
                ->constrained()->onDelete('cascade')->onUpdate('cascade');
            //外部キー付き
            $table->foreignId('post_id')->comment('ポストID')->default(1)
                ->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('image_name', 100)->comment('画像名')->default('example.jqg');
            $table->unsignedInteger('image_size')->comment('画像サイズ')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
