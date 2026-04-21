<?php

namespace App\Http\Controllers;

use App\Models\ForumQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * Display a listing of forum questions.
     */
    public function index(Request $request)
    {
        $query = ForumQuestion::with(['user', 'answers'])
            ->withCount('answers');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $questions = $query->latest()->paginate(15);

        return view('forum.index', compact('questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        // Only authenticated users can create questions
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengajukan pertanyaan.');
        }

        return view('forum.create');
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        // Only authenticated users can create questions
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengajukan pertanyaan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:10',
            'is_anonymous' => 'nullable|boolean',
        ], [
            'title.required' => 'Judul pertanyaan wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'body.required' => 'Detail pertanyaan wajib diisi.',
            'body.min' => 'Detail pertanyaan minimal 10 karakter.',
        ]);

        $question = ForumQuestion::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => 'dibuka',
        ]);

        return redirect()->route('forum.show', $question->forum_question_id)
            ->with('success', 'Pertanyaan Anda berhasil diajukan! Dokter kami akan segera merespons.');
    }

    /**
     * Display the specified question with its answers.
     */
    public function show($id)
    {
        $question = ForumQuestion::with(['user', 'answers.dokter'])
            ->findOrFail($id);

        return view('forum.show', compact('question'));
    }
}