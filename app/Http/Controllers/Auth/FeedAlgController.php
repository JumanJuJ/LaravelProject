<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ChirpController;
use App\Models\Chirp;
use Illuminate\Http\Request;

class FeedAlgController
{
    public function feed(Request $request)
    {
        $user = auth()->user();

        $followingIds = $user->following()->pluck('id');
        $followingHashtags = $this->getFollowingHashtags($request);
        $profileHashtags = $this->createHashtagsPool($request);
        $scores = $this->ratingSystem($followingHashtags, $profileHashtags);

        $chirps = Chirp::whereIn('user_id', $followingIds)
            ->orWhere('user_id', $user->id)
            ->with('user')
            ->get()
            ->sortByDesc(fn ($chirp) => $scores[$chirp->user_id] ?? 0)
            ->values();

        return view('feed', compact('chirps'));
    }

    public function createHashtagsPool(Request $request): array
    {
        $userId = auth()->user()->id;
        $controller = app(ChirpController::class);
        $response = $controller->getHashtags($request, $userId);

        $data = $response->getData(true);

        return array_column($data['hashtags'] ?? [], 'tag');
    }

    public function getFollowingHashtags(Request $request): array
    {
        $user = auth()->user();
        $following = $user->following()->with('hashtags')->get();

        $followingHashtags = [];

        foreach ($following as $followedUser) {
            $followingHashtags[$followedUser->id] = $followedUser->hashtags->pluck('tag')->toArray();
        }

        return $followingHashtags;
    }

    public function stringComparison(string $str1, string $str2): float
    {
        similar_text($str1, $str2, $percent);

        return $percent;
    }

    public function ratingSystem(array $followersHashtags, array $profileHashtags): array
    {
        $scores = [];

        foreach ($followersHashtags as $followerId => $hashtags) {
            foreach ($profileHashtags as $pf) {
                foreach ($hashtags as $fh) {
                    $similarity = $this->stringComparison($pf, $fh);

                    if ($similarity > 80) {
                        $scores[$followerId] = ($scores[$followerId] ?? 0) + 10;
                    }
                }
            }
        }

        arsort($scores);

        return $scores;
    }
}
