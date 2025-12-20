<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-6xl mx-auto py-8 px-3 sm:px-4 lg:px-6 space-y-6">

         ALERTS
        @if(session('success'))
            <div class="rounded-xl bg-emerald-50/90 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-xl bg-blue-50/90 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl bg-red-50/90 border border-red-200 px-4 py-3 text-sm text-red-800 space-y-1">
                @foreach($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

         TOP HEADER CARD
        <div class="bg-slate-900 border border-slate-800 rounded-2xl px-5 py-4 sm:px-6 sm:py-5 shadow-lg shadow-slate-900/40 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 text-white shadow-lg">
                        <i class="fas fa-paper-plane text-sm"></i>
                    </span>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-white">
                            Send PDF via WhatsApp
                        </h1>
                    </div>
                </div>
            </div>

             STATUS + SETTINGS LINK
            <div class="flex flex-col items-end gap-2 text-[11px] sm:text-xs">
                @if($apiKey ?? false)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-200 border border-emerald-400/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2"></span>
                        WhatsApp API configured
                    </span>
                    <span class="text-slate-300/80">
                        Base URL: {{ \Illuminate\Support\Str::limit($apiKey->base_url, 34) }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-500/10 text-amber-200 border border-amber-400/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-2"></span>
                        WhatsApp API not set
                    </span>
                @endif

                <a href="{{ route('no-business.api-settings') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-medium border border-slate-700 text-slate-200 bg-slate-800 hover:bg-slate-700">
                    <i class="fas fa-cog mr-1 text-[10px]"></i>
                    Open API Settings
                </a>

                    <a href="{{ route('no-business.whatsapp.drop') }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-medium border border-slate-700 text-slate-200 bg-slate-800 hover:bg-slate-700">
                    <i class="fas fa-cog mr-1 text-[10px]"></i>
                    Drop
                </a>
            </div>
        </div>

         MAIN CONTENT (ONLY LEFT FORM NOW)
        <div class="grid md:grid-cols-1 gap-6">

             PDF + PHONE FORM
            <div class="bg-white/95 rounded-2xl border border-slate-200 shadow-md shadow-slate-900/5 p-4 sm:p-5 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-slate-900 text-white text-[11px] font-semibold">
                            1
                        </span>
                        <h2 class="font-semibold text-slate-900 text-sm sm:text-base">
                            Upload PDF & Phone Number
                        </h2>
                    </div>

                    @if($apiKey ?? false)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Ready to send
                        </span>
                    @else
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            API required
                        </span>
                    @endif
                </div>

                <p class="text-[11px] sm:text-xs text-slate-500">
                    PDF file choose karo, WhatsApp number (country code ke saath).
                    Submit ke baad same number par PDF chali jayegi.
                </p>

                <form action="{{ route('no-business.send-pdf') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="space-y-4">
                    @csrf

                     PDF INPUT
                    <div class="space-y-1">
                        <label for="pdf" class="block text-xs font-medium text-slate-700">PDF File</label>

                        <label for="pdf"
                               class="flex items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/80 px-3 py-3 cursor-pointer">
                            <div class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm border border-slate-200">
                                <i class="fas fa-file-pdf text-[15px] text-rose-500"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] sm:text-xs text-slate-700">Choose PDF file (max 5 MB)</p>
                                <p class="text-[10px] text-slate-400">Only .pdf allowed.</p>
                            </div>
                        </label>

                        <input id="pdf" type="file" name="pdf" accept="application/pdf" class="hidden"/>
                    </div>

                     PHONE INPUT
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-slate-700">WhatsApp Number</label>
                        <div class="flex gap-2">
                            <input type="text" name="phone"
                                   placeholder="e.g. 9198XXXXXXXX"
                                   class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('phone') }}">
                        </div>
                        <p class="text-[10px] text-slate-400">
                            Number without <code>+</code>, full with country code (e.g. <b>919876543210</b>).
                        </p>
                    </div>

                     SUBMIT BTN
                    <div class="pt-1">
                        <button type="submit"
                                class="inline-flex items-center justify-center w-full md:w-auto px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md shadow-indigo-500/30 hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 focus:ring-offset-slate-100">
                            <i class="fas fa-paper-plane mr-2 text-xs"></i>
                            Send Invoice on WhatsApp
                        </button>

                        @if(!($apiKey ?? false))
                            <p class="mt-2 text-[11px] text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                WhatsApp API set nahi hai. Pehle “Open API Settings” se set karo.
                            </p>
                        @endif
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.app>





