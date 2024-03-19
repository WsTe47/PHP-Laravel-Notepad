<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

// API 路由
Route::get('/v1/notes', [NoteController::class, 'index'])->name('notes.index');
Route::post('/v1/notes/add', [NoteController::class, 'addNoteWithTags'])->name('notes.add');
Route::post('/v1/notes/copy/{id}', [NoteController::class, 'copyNote'])->name('notes.copy');
Route::delete('/v1/notes/delete/{id}', [NoteController::class, 'deleteNote'])->name('notes.delete');
Route::post('/v1/notes/restore/{id}', [NoteController::class, 'restoreNote'])->name('notes.restore');
Route::get('/v1/notes/deleted', [NoteController::class, 'getDeletedNotes'])->name('notes.getDeleted');
