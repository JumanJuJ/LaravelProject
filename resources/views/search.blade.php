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
                            <div class="mt-2 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p>Nome: <strong>{{ $user->name }}</strong></p>
                                    <p>Email: <strong>{{ $user->email }}</strong></p>
                                </div>
                                <button
                                    id="follow-button"
                                    type="button"
                                    data-user-id="{{ $user->id }}"
                                    class="btn btn-sm btn-primary"
                                >
                                    + Follow
                                </button>
                            </div>
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

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function bindFollowButton() {
                const followButton = document.getElementById('follow-button');

                if (!followButton) {
                    return;
                }

                followButton.addEventListener('click', async function () {
                    const userId = followButton.dataset.userId;

                    if (!userId || !csrfToken) {
                        return;
                    }

                    followButton.disabled = true;
                    followButton.textContent = 'Following...';

                    try {
                        const response = await fetch(`/users/${encodeURIComponent(userId)}/follow`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            followButton.disabled = false;
                            followButton.textContent = '+ Follow';
                            resultContainer.innerHTML = `
                                <div class="card bg-base-100 shadow mt-4" id="search-result-message">
                                    <div class="card-body text-error">
                                        ${data.message || 'Unable to follow user.'}
                                    </div>
                                </div>
                            `;
                            return;
                        }

                        followButton.textContent = 'Following';
                        followButton.classList.remove('btn-primary');
                        followButton.classList.add('btn-success');

                        resultContainer.innerHTML += `
                            <div class="card bg-base-100 shadow mt-4" id="search-result-message">
                                <div class="card-body text-success">
                                    ${data.message}
                                </div>
                            </div>
                        `;
                    } catch (error) {
                        followButton.disabled = false;
                        followButton.textContent = '+ Follow';
                        resultContainer.innerHTML = `
                            <div class="card bg-base-100 shadow mt-4" id="search-result-message">
                                <div class="card-body text-error">
                                    Qualcosa è andato storto. Riprova.
                                </div>
                            </div>
                        `;
                    }
                });
            }

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
                                    <div class="mt-2 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p>Nome: <strong>${data.user.name}</strong></p>
                                            <p>Email: <strong>${data.user.email}</strong></p>
                                        </div>
                                        <button
                                            id="follow-button"
                                            type="button"
                                            data-user-id="${data.user.id}"
                                            class="btn btn-sm btn-primary"
                                        >
                                            + Follow
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        bindFollowButton();
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

            bindFollowButton();
        });
    </script>
</x-layout>
