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

    //showDeleted:用于展示“回收站”的页面
    public function showDeletedNotes()
    {
        $deletedNotes = Note::onlyTrashed()->with('tags')->get();

        return view('deleted-notes', ['deletedNotes' => $deletedNotes]);
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

//     // 在执行查询前启用查询日志
//        DB::enableQueryLog();

        $tagIds = [];
        //foreach导致的潜在的N+1问题
        foreach ($request->tags as $tagName) {
            $tag = Tag::with('notes')->firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $note->tags()->attach($tagIds);

//        //查询日志查看
//        $queries = DB::getQueryLog();
//        dd($queries);

        return response()->json(['message' => '添加成功！'], 200);
    }


    // copyNote 复制笔记功能
    public function copyNote($id)
    {
        $originalNote = Note::with('tags')->findOrFail($id);
        $newNote = $originalNote->replicate(); // 复制 note
        $newNote->title = $this->generateNewTitle($originalNote->title); // 生成新标题
        $newNote->save(); // 保存新 note

        // 获取所有标签ID
        $tagsIds = $originalNote->tags->pluck('id')->toArray();

        // 一次性关联所有标签到新 note
        $newNote->tags()->attach($tagsIds);

        return response()->json(['message' => '复制成功！', 'newNote' => $newNote]);
    }

    // generateNewTitle 辅助笔记复制，用于标题生成的逻辑判断。
    public function generateNewTitle($title)
    {
        $maxNumber = 5; // 最大数字，测试时可以调整为3或5
        $number = 1;

        // 匹配标题中最后的数字序列 (如 "Title(2)(3)" 中的 "(2)(3)")
        if (preg_match_all('/\((\d+)\)/', $title, $matches)) {
            // $matches[1] 是所有匹配到的数字
            $lastNumber = (int)end($matches[1]); // 获取最后一个数字

            // 如果最后一个数字小于最大值，只递增这个数字
            if ($lastNumber < $maxNumber) {
                $number = $lastNumber + 1;
                $baseTitle = substr($title, 0, strrpos($title, '(')); // 移除标题中的最后一个数字序列
            } else {
                // 如果最后一个数字已经是最大值，在整个序列后面添加新的 "(1)"
                $baseTitle = $title;
            }
        } else {
            // 如果标题中没有数字序列，我们从 "(1)" 开始
            $baseTitle = $title;
        }

        $newTitle = $baseTitle . "($number)";

        // 检查新生成的标题是否已存在，并递增数字直到找到一个不存在的标题
        while (Note::where('title', $newTitle)->exists()) {
            $number++;
            // 如果超过最大数字，重置为 1 并在原基础上添加 "(1)"
            if ($number > $maxNumber) {
                $number = 1;
                $baseTitle = $newTitle; // 新的基础标题是上一个尝试过的标题
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

        return response()->json(['message' => '笔记已移至回收站'], 404);
    }

    // restoreNote 用于软删除的恢复

    public function restoreNote($id)
    {
        $note = Note::withTrashed()->where('id', $id)->first();
        if ($note) {
            $note->restore();
            return response()->json(['message' => 'Note restored successfully']);
        }

        return response()->json(['message' => '笔记已从回收站恢复'], 404);
    }

    // getDeletedNotes 用于获取回收站内容。
    public function getDeletedNotes()
    {
        $deletedNotes = Note::onlyTrashed()->with('tags')->get();
        return response()->json($deletedNotes);
    }
}
