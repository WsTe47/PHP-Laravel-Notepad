<?php
use App\Http\Controllers\NoteController;

// 显示所有笔记
Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');

// 添加笔记及其标签
Route::post('/notes/add', [NoteController::class, 'addNoteWithTags'])->name('notes.add');

// 复制笔记
Route::post('/notes/copy/{id}', [NoteController::class, 'copyNote'])->name('notes.copy');

// 删除笔记
Route::delete('/notes/delete/{id}', [NoteController::class, 'deleteNote'])->name('notes.delete');

// 恢复笔记
Route::post('/notes/restore/{id}', [NoteController::class, 'restoreNote'])->name('notes.restore');
