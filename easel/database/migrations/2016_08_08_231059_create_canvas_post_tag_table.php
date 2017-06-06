<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCanvasPostTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(CanvasHelper::TABLES['post_tag'], function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->timestamps();

            $table->primary(['post_id', 'tag_id']);
            $table->foreign('post_id')->references('id')->on(CanvasHelper::TABLES['posts'])->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on(CanvasHelper::TABLES['tags'])->onUpdate('cascade')->onDelete('cascade');
        });

        $now = Carbon\Carbon::now();

        // This is here to migrate any data someone might have into the new post-tag table.
        collect(DB::table('post_tag_pivot')->get())->each(function ($item) use ($now) {
            DB::table(CanvasHelper::TABLES['post_tag'])->insert([
                'post_id' => $item->post_id,
                'tag_id' => $item->tag_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(CanvasHelper::TABLES['post_tag']);
    }
}
