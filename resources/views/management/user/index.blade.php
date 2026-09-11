{{--
    Catatan pembelajaran:
    View Blade ini menampilkan tampilan halaman aplikasi. Komponen utama seperti form, tabel, dan modal dipasang di sini, lalu diberi data dari controller melalui compact() atau session().
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">
            {{ __('Kelola User') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        modalOpen: false,
        modalMode: 'create',
        userId: null,
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: 'user'
        },
        openCreate() {
            this.modalMode = 'create';
            this.userId = null;
            this.form = {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                role: 'user'
            };
            this.modalOpen = true;
        },
        openEdit(user) {
            this.modalMode = 'edit';
            this.userId = user.id;
            this.form = {
                name: user.name,
                email: user.email,
                password: '',
                password_confirmation: '',
                role: user.role
            };
            this.modalOpen = true;
        },
        submit() {
            const form = document.getElementById('user-form');
            const methodInput = document.getElementById('_method');
    
            if (this.modalMode === 'edit') {
                methodInput.value = 'PUT';
                form.action = '/user-management/' + this.userId;
            } else {
                methodInput.value = 'POST';
                form.action = '{{ route('user-management.store') }}';
            }
    
            form.submit();
        }
    }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex w-full max-w-xl flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('user-management.index') }}" class="flex w-full gap-2">
                        <input type="search" name="search" value="{{ $search }}"
                            placeholder="Cari nama atau email"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        <button type="submit"
                            class="rounded-lg border border-slate-200 bg-indigo-600 px-3 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-500">
                            Cari
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('user-management.index') }}"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Total user: {{ $users->count() }}</p>
                    <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8h-16" />
                        </svg>
                        Tambah User
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-800">
                        <thead class="bg-gradient-to-r from-sky-300 via-indigo-500 to-teal-500 shadow-lg shadow-indigo-500/20">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Dibuat</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                            {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button type="button"
                                                @click="openEdit({ id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}' })"
                                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 font-medium text-slate-700 transition hover:bg-slate-100">
                                                Edit
                                            </button>

                                            @if ($user->role !== 'super_admin' && auth()->id() !== $user->id)
                                                <form action="{{ route('user-management.destroy', $user) }}"
                                                    method="POST" onsubmit="return confirm('Yakin hapus user ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 font-medium text-rose-700 transition hover:bg-rose-100">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                        {{ $search !== '' ? 'Tidak ada user yang cocok dengan pencarian.' : 'Belum ada user.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="modalOpen = false"></div>

            <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-slate-800"
                        x-text="modalMode === 'create' ? 'Tambah User' : 'Edit User'"></h3>
                    <button type="button" @click="modalOpen = false"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="user-form" method="POST" action="{{ route('user-management.store') }}">
                    @csrf
                    <input type="hidden" id="_method" name="_method" value="POST">

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                            <input id="name" x-model="form.name" type="text" name="name" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div class="md:col-span-2">
                            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input id="email" x-model="form.email" type="email" name="email" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div>
                            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
                                {{ __('Password') }}
                            </label>
                            <input id="password" x-model="form.password" type="password" name="password"
                                :required="modalMode === 'create'"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">
                                Konfirmasi Password
                            </label>
                            <input id="password_confirmation" x-model="form.password_confirmation" type="password"
                                name="password_confirmation"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                        </div>

                        <div class="md:col-span-2">
                            <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                            <select id="role" x-model="form.role" name="role"
                                :disabled="form.role === 'super_admin'"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:bg-slate-100">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="super_admin" x-show="false">Super Admin</option>
                            </select>
                            <p x-show="form.role === 'super_admin'" class="mt-2 text-xs text-slate-500">Role super
                                admin tidak bisa diubah.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="modalOpen = false"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="button" @click="submit()"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>