<?php

use App\Model\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

//仅仅
class NoteControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_generate_new_title_without_number() {
        $controller = new \App\Http\Controllers\NoteController();
        $newTitle = $controller->generateNewTitle('Title');
        $this->assertEquals('Title(1)', $newTitle);
    }

    public function test_generate_new_title_with_number() {
        $controller = new \App\Http\Controllers\NoteController();
        $newTitle = $controller->generateNewTitle('Title(1)');
        $this->assertEquals('Title(2)', $newTitle);
    }

    public function test_generate_new_title_with_max_number() {
        $controller = new \App\Http\Controllers\NoteController();
        $newTitle = $controller->generateNewTitle('Title(5)');
        $this->assertEquals('Title(5)(1)', $newTitle);
    }

    public function test_delete_note() {
        $note = factory(Note::class)->create(); // 创建一个笔记
        $response = $this->json('DELETE', '/api/v1/notes/delete/' . $note->id);
        $response->assertStatus(200);
        $this->assertSoftDeleted('notes', ['id' => $note->id]); // 确保笔记已软删除
    }

    public function test_restore_note() {
        $note = factory(Note::class)->create(['deleted_at' => now()]); // 创建一个已软删除的笔记
        $response = $this->json('POST', '/api/v1/notes/restore/' . $note->id);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('notes', ['id' => $note->id, 'deleted_at' => null]); // 确保笔记已恢复
    }

}
