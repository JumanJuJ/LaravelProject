<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\Request;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chirps = Chirp::with('user')
            ->latest()
            ->take(50)  // Limit to 50 most recent chirps
            ->get();

        return view('home', ['chirps' => $chirps]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ], [
            'message.required' => 'Please write something to chirp!',
            'message.max' => 'Chirps must be 255 characters or less.',
        ]);

        auth()->user()->chirps()->create($validated);

        return redirect('/')->with('success', 'Your chirp has been posted!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        if ($chirp->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return view('chirps.edit', compact('chirp'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp)
    {
        if ($chirp->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Validate
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        // Update
        $chirp->update($validated);

        return redirect('/')->with('success', 'Chirp updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp)
    {
        if ($chirp->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $chirp->delete();

        return redirect('/')->with('success', 'Chirp deleted!');
    }

    public function search(Request $request)
    {
        $name = $request->query('name');
        $user = null;

        if ($name !== null) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user = User::where('name', $validated['name'])->first();

            if ($request->wantsJson()) {
                return $user
                    ? response()->json(['exists' => true, 'user' => $user])
                    : response()->json(['exists' => false], 404);
            }
        }

        return view('search', [
            'name' => $name,
            'user' => $user,
        ]);
    }

    public function follow(Request $request, User $userFollowed)
    {
        if ($userFollowed->id === $request->user()->id) {
            $message = 'You cannot follow yourself.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->back()->withErrors($message);
        }

        if ($userFollowed->followers()->where('user_id', $request->user()->id)->exists()) {
            $message = 'You are already following this user.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : redirect()->back()->withErrors($message);
        }

        $request->user()->following()->syncWithoutDetaching([$userFollowed->id]);
        $message = 'You are now following '.$userFollowed->name.'.';

        return $request->wantsJson()
            ? response()->json(['message' => $message, 'user' => $userFollowed])
            : redirect()->back()->with('success', $message);
    }
} 
