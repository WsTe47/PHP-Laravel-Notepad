<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteTagTable extends Migration
{
    public function up()
    {
        Schema::create('note_tag', function (Blueprint $table) {
            $table->foreignId('note_id')->constrained('notes')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['note_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('note_tag');
    }
}
