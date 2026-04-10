<x-layout>
    <x-slot:title>
        Search Users
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold">Search Users</h1>
                <p class="text-sm text-base-content/60">Type a username and find out whether it exists.</p>
            </div>
            <a href="/" class="btn btn-ghost btn-sm">Back to Feed</a>
        </div>

        <div class="card bg-base-100 shadow mt-8">
            <div class="card-body">
                <form id="search-form" method="GET" action="{{ route('search') }}">
                    <div class="form-control w-full">
                        <div class="flex flex-col gap-2 md:flex-row">
                            <input
                                id="search-name"
                                type="text"
                                name="name"
                                value="{{ old('name', $name) }}"
                                placeholder="Search by username"
                                class="input input-bordered w-full"
                                autofocus
                            />
                            <button type="submit" class="btn btn-primary btn-sm md:w-auto">
                                Search
                            </button>
                        </div>

                        <div id="search-error" class="label mt-2">
                            @error('name')
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="search-result-container">
            @if ($name !== null)
                <div class="card bg-base-100 shadow mt-6" id="search-result">
                    <div class="card-body">
                        @if ($user)
                            <h2 class="text-xl font-semibold">User found</h2>
                            <p class="mt-2">Nome: <strong>{{ $user->name }}</strong></p>
                            <p>Email: <strong>{{ $user->email }}</strong></p>
                        @else
                            <h2 class="text-xl font-semibold">No user found</h2>
                            <p class="mt-2 text-base-content/70">Non esiste alcun utente con questo nome.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('search-form');
            const nameInput = document.getElementById('search-name');
            const resultContainer = document.getElementById('search-result-container');
            const errorContainer = document.getElementById('search-error');

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                errorContainer.innerHTML = '';
                resultContainer.innerHTML = '';

                const name = nameInput.value.trim();

                if (!name) {
                    errorContainer.innerHTML = '<span class="label-text-alt text-error">Il campo nome è obbligatorio.</span>';
                    return;
                }

                try {
                    const response = await fetch(`${form.action}?name=${encodeURIComponent(name)}`, {
                        headers: {
                            Accept: 'application/json',
                        },
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        if (data.errors && data.errors.name) {
                            errorContainer.innerHTML = `<span class="label-text-alt text-error">${data.errors.name[0]}</span>`;
                        }
                        return;
                    }

                    if (response.status === 404) {
                        resultContainer.innerHTML = `
                            <div class="card bg-base-100 shadow mt-6" id="search-result">
                                <div class="card-body">
                                    <h2 class="text-xl font-semibold">No user found</h2>
                                    <p class="mt-2 text-base-content/70">Non esiste alcun utente con questo nome.</p>
                                </div>
                            </div>
                        `;
                        return;
                    }

                    const data = await response.json();

                    if (data.exists) {
                        resultContainer.innerHTML = `
                            <div class="card bg-base-100 shadow mt-6" id="search-result">
                                <div class="card-body">
                                    <h2 class="text-xl font-semibold">User found</h2>
                                    <p class="mt-2">Nome: <strong>${data.user.name}</strong></p>
                                    <p>Email: <strong>${data.user.email}</strong></p>
                                </div>
                            </div>
                        `;
                    } else {
                        resultContainer.innerHTML = `
                            <div class="card bg-base-100 shadow mt-6" id="search-result">
                                <div class="card-body">
                                    <h2 class="text-xl font-semibold">No user found</h2>
                                    <p class="mt-2 text-base-content/70">Non esiste alcun utente con questo nome.</p>
                                </div>
                            </div>
                        `;
                    }
                } catch (error) {
                    resultContainer.innerHTML = `
                        <div class="card bg-base-100 shadow mt-6" id="search-result">
                            <div class="card-body">
                                <h2 class="text-xl font-semibold">Errore</h2>
                                <p class="mt-2 text-base-content/70">Qualcosa è andato storto. Riprova.</p>
                            </div>
                        </div>
                    `;
                }
            });
        });
    </script>
</x-layout>
