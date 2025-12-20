<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-6xl mx-auto py-8 px-3 sm:px-4 lg:px-6 space-y-6">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Dropzone CSS & JS --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        {{-- ALERTS --}}
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

        {{-- TOP HEADER CARD --}}
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

            {{-- STATUS + SETTINGS LINK --}}
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
            </div>
        </div>

        <style>
            .dz-message{ margin:0!important; }
            .dropzone{
                border:2px dashed #cbd5e1!important;
                border-radius:1rem!important;
                background:rgba(248,250,252,.85)!important;
                padding:14px!important;
            }
            .dropzone .dz-preview{
                margin:10px!important;
                width:240px;
                min-height:120px;
                border-radius:14px;
                border:1px solid #e2e8f0;
                background:#fff;
                box-shadow:0 4px 18px rgba(15,23,42,.06);
                overflow:hidden;
                position:relative;
            }
            .dropzone .dz-details{ padding:10px 12px!important; }
            .dropzone .dz-image{ display:none!important; } /* PDF */
            .dropzone .dz-filename, .dropzone .dz-size{ font-size:12px!important; color:#0f172a!important; }
            .dropzone .dz-progress{
                height:8px!important;
                border-radius:999px!important;
                margin:10px 12px!important;
                background:#e2e8f0!important;
            }
            .dropzone .dz-upload{ background:linear-gradient(90deg,#4f46e5,#7c3aed)!important; }
            .dropzone .dz-remove{
                display:inline-flex!important;
                align-items:center;
                justify-content:center;
                margin:10px 12px 12px!important;
                padding:6px 10px!important;
                border-radius:12px!important;
                border:1px solid #e2e8f0!important;
                font-size:12px!important;
                color:#ef4444!important;
                text-decoration:none!important;
            }
            .dz-retry{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                margin:10px 12px 12px;
                padding:6px 10px;
                border-radius:12px;
                border:1px solid #cbd5e1;
                font-size:12px;
                color:#2563eb;
                background:#fff;
            }
            .pdf-icon{
                width:40px;height:40px;border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                background:#fff;border:1px solid #e2e8f0;
                box-shadow:0 3px 10px rgba(15,23,42,.06);
            }
        </style>

        {{-- MAIN CONTENT --}}
        <div class="grid md:grid-cols-1 gap-6">
            <div class="bg-white/95 rounded-2xl border border-slate-200 shadow-md shadow-slate-900/5 p-4 sm:p-5 space-y-4">

                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-slate-900 text-white text-[11px] font-semibold">1</span>
                        <h2 class="font-semibold text-slate-900 text-sm sm:text-base">Upload PDF & Send (Auto)</h2>
                    </div>

                    <button type="button" id="retry-all-btn"
                            class="hidden text-[11px] px-3 py-1 rounded-full bg-yellow-50 text-yellow-800 border border-yellow-200">
                        🔁 Retry All Failed
                    </button>
                </div>

                <p class="text-[11px] sm:text-xs text-slate-500">
                    PDF drop karo — system ek-ek karke upload karega aur WhatsApp par send karega (dropStore jaise).
                    Phone blank chhodo to file name se number uth jayega.
                </p>

                {{-- PHONE INPUT (optional) --}}
{{--                <div class="space-y-1">--}}
{{--                    <label class="block text-xs font-medium text-slate-700">WhatsApp Number (optional)</label>--}}
{{--                    <input type="text" id="phoneInput"--}}
{{--                           placeholder="e.g. 9198XXXXXXXX (optional)"--}}
{{--                           class="w-full text-xs border border-slate-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">--}}
{{--                    <p class="text-[10px] text-slate-400">--}}
{{--                        Agar blank rakho to PDF file name se number niklega (10 digit => 91 add).--}}
{{--                    </p>--}}
{{--                </div>--}}

                {{-- DROPZONE --}}
                <div id="pdfDropzone" class="dropzone rounded-xl">
                    <div class="dz-message" data-dz-message>
                        <div class="flex items-center gap-3">
                            <div class="pdf-icon"><i class="fas fa-file-pdf text-rose-500"></i></div>
                            <div class="flex-1">
                                <div class="text-xs font-semibold text-slate-800">Drop PDFs here or click to upload</div>
                                <div class="text-[11px] text-slate-500">Only .pdf, max 5 MB each</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!($apiKey ?? false))
                    <p class="text-[11px] text-red-500 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle text-[10px]"></i>
                        WhatsApp API set nahi hai. Pehle “Open API Settings” se set karo.
                    </p>
                @endif

            </div>
        </div>
    </div>

    <script>
        Dropzone.autoDiscover = false;

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const retryAllBtn = document.getElementById('retry-all-btn');
        const failedFileMap = new Map(); // name => File

        const dz = new Dropzone("#pdfDropzone", {
            url: "{{ route('no-business.send-pdf-dropzone') }}",
            method: "post",
            paramName: "pdf",

            acceptedFiles: "application/pdf,.pdf",
            maxFilesize: 5,
            uploadMultiple: false,
            parallelUploads: 1,      // ✅ one by one
            autoProcessQueue: true,  // ✅ drop => auto upload+send

            addRemoveLinks: true,
            dictRemoveFile: "Remove",
            createImageThumbnails: false,

            timeout: 300000,
            headers: { 'X-CSRF-TOKEN': csrf },

            init: function () {

                this.on("sending", function(file, xhr, formData) {
                    const phone = (document.getElementById('phoneInput').value || '').trim();
                    formData.append('phone', phone);
                });

                this.on("success", function(file, res) {
                    failedFileMap.delete(file.name);
                    if (failedFileMap.size === 0) retryAllBtn.classList.add("hidden");

                    file._sent_ok = true;

                    Swal.fire({
                        icon: 'success',
                        title: '✅ Sent!',
                        text: `${file.name} → ${res.phone ?? ''}`,
                        timer: 1600,
                        showConfirmButton: false
                    });
                });

                this.on("error", function(file, errorMessage, xhr) {
                    failedFileMap.set(file.name, file);
                    retryAllBtn.classList.remove("hidden");

                    let msg = "Send failed.";
                    if (xhr && xhr.responseText) {
                        try {
                            const j = JSON.parse(xhr.responseText);
                            msg = j.message || msg;
                        } catch(e){}
                    }

                    // add retry btn (avoid duplicates)
                    if (!file.previewElement.querySelector('.dz-retry')) {
                        const retryBtn = Dropzone.createElement("<button type='button' class='dz-retry'>🔁 Retry</button>");
                        file.previewElement.appendChild(retryBtn);

                        retryBtn.addEventListener("click", function(e){
                            e.preventDefault(); e.stopPropagation();
                            retryBtn.remove();
                            dz.removeFile(file);
                            dz.addFile(file); // re-upload
                        });
                    }

                    Swal.fire({ icon:'error', title:'❌ Failed', text: msg });
                });

                this.on("removedfile", function(file){
                    failedFileMap.delete(file.name);
                    if (failedFileMap.size === 0) retryAllBtn.classList.add("hidden");
                });

                retryAllBtn.addEventListener("click", function(){
                    if (failedFileMap.size === 0) return;

                    const files = Array.from(failedFileMap.values());
                    failedFileMap.clear();
                    retryAllBtn.classList.add("hidden");

                    files.forEach(f => {
                        dz.removeFile(f);
                        dz.addFile(f);
                    });
                });
            }
        });
    </script>
</x-layouts.app>
