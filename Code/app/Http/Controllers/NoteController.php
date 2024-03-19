<?php

namespace App\Http\Controllers;


use App\Model\Note;
use App\Model\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class NoteController extends Controller
{
    // index 单纯的一个展示页面
    public function index()
    {
        $notes = Note::with('tags')->get();
//        return view('notes.index', compact('notes'));
        return response()->json($notes);

    }

    // addNoteWithTags 正常的新增笔记功能。服务端再次校验了一下title是否含有（）
    public function addNoteWithTags(Request $request)
    {
        // 校验（）
        $title = $request->input('title');
        if (strpos($title, '(') !== false || strpos($title, ')') !== false) {
            return response()->json(['message' => '标题中不能包含括号！'], 400);
        }

        $note = new Note();
        $note->title = $title;
        $note->content = $request->input('content');

        $note->save();

        // 在执行查询前启用查询日志
        DB::enableQueryLog();

        $tagIds = [];
        //foreach导致的潜在的N+1问题
        foreach ($request->tags as $tagName) {
            $tag = Tag::with('notes')->firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $note->tags()->attach($tagIds);

        //查询日志查看
        $queries = DB::getQueryLog();
        dd($queries);

        return response()->json(['message' => '添加成功！'], 200);
    }


    // copyNote 复制笔记功能
    public function copyNote($id)
    {
        $originalNote = Note::with('tags')->findOrFail($id); // 确保加载了 tags 关系
        $newNote = $originalNote->replicate(); // 复制 note
        $newNote->title = $this->generateNewTitle($originalNote->title); // 生成新标题
        $newNote->save(); // 保存新 note

        // 复制每个 tag 并关联到新 note
        foreach ($originalNote->tags as $tag) {
            // 此处无需复制 Tag，因为 Tag 对象可能被多个 Note 共享
            // 直接将现有的 Tag 对象与新的 Note 对象关联即可
            $newNote->tags()->attach($tag->id);
        }

        return response()->json(['message' => '复制成功！', 'newNote' => $newNote]);
    }

    // generateNewTitle 辅助笔记复制，用于标题生成的逻辑判断。
    public function generateNewTitle($title)
    {
        $maxNumber = 99; // 最大数字，题目要求99，这里改为5便于测试。
        $number = 1;
        $baseTitle = $title;
        $newTitle = $baseTitle . "($number)";

        while (Note::where('title', $newTitle)->exists()) {
            if (preg_match('/^(.*?)(\((\d+)\))+$/', $baseTitle, $matches)) {
                $baseTitle = $matches[1];
                $number = (int)$matches[3];

                if ($number < $maxNumber) {
                    // 没有最大值，加数字
                    $number++;
                } else {
                    // 最大值，除了最后一个括号，其余均为base。之后再加。
                    $baseTitle = $baseTitle . $matches[2];
                    $number = 1;
                }
            } else {
                // 没数字就直接递增
                $number++;
            }
            $newTitle = $baseTitle . "($number)";
        }

        return $newTitle;
    }


    // deleteNote 用于软删除一条笔记
    public function deleteNote($id)
    {
        $note = Note::find($id);
        if ($note) {
            $note->delete();
            return response()->json(['message' => 'Note deleted successfully']);
        }

        return response()->json(['message' => 'Note not found'], 404);
    }

    // restoreNote 用于软删除的恢复
    // TODO：目前还没有完成测试、前端编写。
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
