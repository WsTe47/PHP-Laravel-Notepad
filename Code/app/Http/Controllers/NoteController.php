<?php

namespace App\Http\Controllers;


use App\Model\Note;
use App\Model\Tag;
use Illuminate\Http\Request;


class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::all();
        return view('notes.index', compact('notes'));
//        return response()->json($notes);
//返回view是网页，json在测试时候先用的。
    }

    public function addNoteWithTags(Request $request)
    {
        $note = new Note();
        $note->title = $request->input('title'); // 使用 input 方法获取 title
        $note->content = $request->input('content'); // 使用 input 方法获取 content

        $note->save();

        $tagIds = [];
        foreach ($request->tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $note->tags()->attach($tagIds);

        return response()->json(['message' => '添加成功！'], 200);
    }

    public function copyNote($id)
    {
        $originalNote = Note::findOrFail($id);
        $newNote = $originalNote->replicate();
        $newTitle = $this->generateNewTitle($originalNote->title);
        $newNote->title = $newTitle;
        $newNote->push();

        return response()->json(['message' => 'Note copied successfully', 'newNote' => $newNote]);
    }

    protected function generateNewTitle($title)
    {
        $pattern = '/^(.*?)(\((\d+)\))?$/';
        preg_match($pattern, $title, $matches);
        $baseTitle = $matches[1];
        $number = isset($matches[3]) ? (int)$matches[3] + 1 : 1;

        while (Note::whereTitle($baseTitle . '(' . $number . ')')->exists()) {
            $number++;
        }

        return $baseTitle . '(' . $number . ')';
    }


    public function deleteNote($id)
    {
        $note = Note::find($id);
        if ($note) {
            $note->delete();
            return response()->json(['message' => 'Note deleted successfully']);
        }

        return response()->json(['message' => 'Note not found'], 404);
    }

    public function restoreNote($id)
    {
        $note = Note::withTrashed()->where('id', $id)->first();
        if ($note) {
            $note->restore();
            return response()->json(['message' => 'Note restored successfully']);
        }

        return response()->json(['message' => 'Note not found'], 404);
    }
}
