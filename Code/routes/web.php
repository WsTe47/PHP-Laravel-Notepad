<?php
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

// 加载前端页面的路由
Route::get('/notes', function () {
    return view('notes'); // 假设您的 HTML 页面保存在 resources/views/notes.blade.php
});
