<x-layout>
    <x-slot:title>
        {{ $user->name }}'s Profile
    </x-slot:title>

    <div class="max-w-2xl mx-auto mt-8 space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
                        <p class="text-sm text-base-content/70">{{ $user->email }}</p>

                        @if ($user->hashtags->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ($user->hashtags as $hashtag)
                                    <span class="rounded-full border border-base-200 bg-base-200/50 px-3 py-1 text-sm text-base-content">#{{ $hashtag->tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if (auth()->check() && auth()->id() === $user->id)
                            <div class="mt-4 flex gap-2">
                                <button type="button" id="hashtag-edit-toggle" class="btn btn-secondary btn-sm">
                                    Modifica hashtag
                                </button>
                                <span class="text-sm text-base-content/70 self-center">Usa il pulsante per aggiornare i tuoi hashtag.</span>
                            </div>

                            <div id="hashtag-form-wrapper" class="mt-4 hidden">
                                <form method="POST" action="{{ route('hashtags.update') }}" id="hashtag-form" class="space-y-3">
                                    @csrf

                                    <label class="block text-sm font-medium text-base-content/70" for="hashtags">
                                        Hashtag personali
                                    </label>

                                    <input
                                        id="hashtags"
                                        name="hashtags"
                                        type="text"
                                        value="{{ old('hashtags', $user->hashtags->pluck('tag')->map(fn ($tag) => '#'.$tag)->join(', ')) }}"
                                        class="input input-bordered w-full"
                                        placeholder="#laravel, #php, #developer"
                                    />

                                    <p class="text-xs text-base-content/60">
                                        Inserisci gli hashtag separati da virgola. Verranno salvati senza il simbolo #.
                                    </p>

                                    <div id="hashtag-form-status" class="text-sm text-success"></div>

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Salva hashtag
                                    </button>
                                </form>
                            </div>

                            <form method="POST" action="{{ route('visibility.toggle') }}" class="mt-4">
                                @csrf

                                <button type="submit" class="btn btn-outline btn-sm">
                                    Rendi il profilo {{ $user->is_public ? 'privato' : 'pubblico' }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center sm:w-auto">
                        <div class="rounded-lg border border-base-200 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/50">Chirps</p>
                            <p class="mt-1 text-xl font-semibold">{{ $user->chirps_count }}</p>
                        </div>
                        <div class="rounded-lg border border-base-200 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/50">Followers</p>
                            <p class="mt-1 text-xl font-semibold">{{ $user->followers_count }}</p>
                        </div>
                        <div class="rounded-lg border border-base-200 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-base-content/50">Following</p>
                            <p class="mt-1 text-xl font-semibold">{{ $user->following_count }}</p>
                        </div>
                    </div>
                </div>

                @if ($user->followers->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-base-content/70">Followers</h2>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($user->followers as $follower)
                                <div class="rounded-lg border border-base-200 p-3">
                                    <p class="font-medium">{{ $follower->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($chirps as $chirp)
                <x-chirp :chirp="$chirp" />
            @empty
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center">
                        <p class="text-base-content/60">No chirps yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
