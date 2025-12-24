<x-layouts.app :title="__('WhatsApp API Settings')">
    <div class="max-w-4xl mx-auto py-8 px-3 sm:px-4 lg:px-6 space-y-6">

        {{-- header --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 sm:px-6 sm:py-5 shadow-lg shadow-black/30 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500 via-teal-500 to-sky-500 text-white shadow-lg">
                    <i class="fas fa-cog text-sm"></i>
                </span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-white">WhatsApp API Settings</h1>
                    <p class="text-xs text-slate-300/80">Base URL, API Key aur Secret/Token yahin se set/edit karo.</p>
                </div>
            </div>

            <a href="{{ route('no-business.whatsapp') }}"
               class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-100 border border-slate-700 hover:bg-slate-700">
                <i class="fas fa-arrow-left mr-2 text-[11px]"></i>
                Back to Dashboard
            </a>
        </div>

        {{-- alerts --}}
        @if(session('success'))
            <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl bg-rose-500/10 border border-rose-500/30 px-4 py-3 text-sm text-rose-200 space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- form card --}}
        <div class="bg-slate-900/60 backdrop-blur rounded-2xl border border-slate-800 shadow-md shadow-black/30 p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-white">Configure WhatsApp Provider</h2>
                    <p class="text-xs text-slate-400">Aapki settings user level par save hongi (no business).</p>
                </div>

                @if($apiKey ?? false)
                    <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-200 border border-emerald-500/25">
                        Already configured
                    </span>
                @else
                    <span class="text-[11px] px-2 py-1 rounded-full bg-amber-500/10 text-amber-200 border border-amber-500/25">
                        Not set
                    </span>
                @endif
            </div>

            <form action="{{ route('no-business.save-api') }}" method="POST" class="space-y-4">
                @csrf

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-slate-200">
                        Base URL <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="base_url"
                           placeholder="https://your-whatsapp-provider.com/api"
                           class="w-full text-xs border border-slate-700 rounded-xl px-3 py-2 bg-slate-950/60 text-slate-100 placeholder:text-slate-500
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('base_url', $apiKey->base_url ?? '') }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    {{-- Wishes API --}}
                    <div class="sm:col-span-4 space-y-1">
                        <label class="block text-xs font-medium text-slate-200">Wishes API</label>
                        <input type="text" name="wishes_api"
                               placeholder="https://your-whatsapp-provider.com/api"
                               class="w-full text-xs border border-slate-700 rounded-xl px-3 py-2 bg-slate-950/60 text-slate-100 placeholder:text-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('wishes_api', $apiKey->wishes_api ?? '') }}">
                    </div>

                    {{-- Wishes Time --}}
                    <div class="sm:col-span-1 space-y-1">
                        <label class="block text-xs font-medium text-slate-200">Wishes Time</label>
                        <input type="time" name="wish_at"
                               class="w-full text-xs border border-slate-700 rounded-xl px-3 py-2 bg-slate-950/60 text-slate-100
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('wish_at', $apiKey->wish_at ?? '') }}">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-200">API Key</label>
                        <input type="text" name="key"
                               class="w-full text-xs border border-slate-700 rounded-xl px-3 py-2 bg-slate-950/60 text-slate-100 placeholder:text-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('key', $apiKey->key ?? '') }}">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-200">API Secret / Token</label>
                        <input type="text" name="secret"
                               class="w-full text-xs border border-slate-700 rounded-xl px-3 py-2 bg-slate-950/60 text-slate-100 placeholder:text-slate-500
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('secret', $apiKey->secret ?? '') }}">
                        <p class="text-[10px] text-slate-400">
                            Agar provider sirf ek token deta hai to use yahan daal sakte ho.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 focus:ring-offset-slate-950">
                        <i class="fas fa-save mr-2 text-xs"></i>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
