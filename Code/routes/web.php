<?php
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

// web路由
Route::get('/notes', function () {
    return view('notes');
});

Route::get('/deleted-notes', [NoteController::class, 'showDeletedNotes'])->name('notes.showDeleted');
