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
