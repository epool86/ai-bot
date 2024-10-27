<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KnowledgeSetController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::resource('knowledge-sets', KnowledgeSetController::class);
    Route::resource('knowledge-sets.documents', DocumentController::class)->except(['edit', 'update']);

    Route::get('/knowledge-sets/{knowledgeSet}/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/knowledge-sets/{knowledgeSet}/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/{chatSession}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chatSession}/message', [ChatController::class, 'message'])->name('chat.message');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
