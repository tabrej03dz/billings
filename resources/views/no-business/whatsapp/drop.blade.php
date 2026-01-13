<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-6xl mx-auto py-8 px-3 sm:px-4 lg:px-6 space-y-6">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Dropzone CSS & JS --}}
        {{-- NOTE: Dropzone script must be loaded somewhere globally (layout/app.js) or uncomment below --}}
        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css"> --}}
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script> --}}
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

        {{-- TOP HEADER (SLIM, LESS FOCUS) --}}
        <div class="bg-white/90 border border-slate-200 rounded-2xl px-5 py-4 sm:px-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-sm">
                    <i class="fas fa-paper-plane text-sm"></i>
                </span>
                <div>
                    <h1 class="text-lg sm:text-xl font-semibold text-slate-900 leading-tight">
                        Send PDF via WhatsApp
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-500">
                        Drop PDF → auto upload + WhatsApp send
                    </p>
                </div>
            </div>

            <div class="flex items-end gap-2 sm:gap-3">
                @if($apiKey ?? false)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                        API Ready
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span>
                        API Not Set
                    </span>
                @endif

                <a href="{{ route('no-business.api-settings') }}"
                   class="inline-flex items-center px-3 py-2 rounded-xl text-[11px] font-medium border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
                    <i class="fas fa-cog mr-1 text-[10px]"></i>
                    API Settings
                </a>
            </div>
        </div>

        <style>
            .dz-message { margin:0!important; }

            /* HERO DROPZONE */
            .dropzone{
                border:2px dashed #cbd5e1!important;
                border-radius:1.25rem!important;
                background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(248,250,252,.88))!important;
                padding:22px!important;
                transition: all .15s ease;
            }
            .dropzone:hover{
                border-color:#818cf8!important;
                box-shadow: 0 10px 30px rgba(79,70,229,.10);
                transform: translateY(-1px);
            }
            .dropzone.dz-drag-hover{
                border-color:#4f46e5!important;
                background:rgba(238,242,255,.65)!important;
            }

            .dropzone .dz-preview{
                margin:10px!important;
                width:260px;
                min-height:120px;
                border-radius:16px;
                border:1px solid #e2e8f0;
                background:#fff;
                box-shadow:0 6px 22px rgba(15,23,42,.08);
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
                width:44px;height:44px;border-radius:14px;
                display:flex;align-items:center;justify-content:center;
                background:#fff;border:1px solid #e2e8f0;
                box-shadow:0 4px 14px rgba(15,23,42,.08);
            }
        </style>

        {{-- MAIN CONTENT --}}
        <div class="grid md:grid-cols-1 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-lg shadow-slate-900/5 p-5 sm:p-6 space-y-4">

{{--                <div class="flex items-center justify-between gap-2">--}}
{{--                    <div class="flex items-center gap-2">--}}
{{--                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-indigo-600 text-white text-[11px] font-semibold">1</span>--}}
{{--                        <h2 class="font-semibold text-slate-900 text-sm sm:text-base">Upload PDF & Send (Auto)</h2>--}}
{{--                    </div>--}}

{{--                    <button type="button" id="retry-all-btn"--}}
{{--                            class="hidden text-[11px] px-3 py-1 rounded-full bg-yellow-50 text-yellow-800 border border-yellow-200">--}}
{{--                        🔁 Retry All Failed--}}
{{--                    </button>--}}
{{--                </div>--}}

{{--                <p class="text-[11px] sm:text-xs text-slate-500 -mt-2">--}}
{{--                    PDFs drop karo ya click karo — system one-by-one upload karke WhatsApp par send karega.--}}
{{--                    Phone blank chhodo to file name se number uth jayega.--}}
{{--                </p>--}}

                {{-- PHONE INPUT (optional) --}}
                <div class="space-y-1">
                    <label class="block text-xs font-medium text-slate-700">WhatsApp Number (optional)</label>
                    <input type="text" id="phoneInput"
                           placeholder="e.g. 9198XXXXXXXX (optional)"
                           class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white
                                  text-slate-900 placeholder:text-slate-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-[10px] text-slate-400">
                        Agar blank hai to PDF file name se number niklega (10 digit => 91 add).
                    </p>
                </div>

                {{-- DROPZONE (HERO) --}}
                <div id="pdfDropzone" class="dropzone rounded-2xl">
                    <div class="dz-message" data-dz-message>
                        <div class="flex items-center gap-3">
                            <div class="pdf-icon"><i class="fas fa-file-pdf text-rose-500"></i></div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-slate-800">Drop PDFs here or click to upload</div>
                                <div class="text-[11px] text-slate-500">Only .pdf, max 5 MB each</div>
                            </div>
                            <div class="hidden sm:block">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100 text-[11px] font-medium">
                                    Auto Send Enabled
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!($apiKey ?? false))
                    <p class="text-[11px] text-red-500 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle text-[10px]"></i>
                        WhatsApp API set nahi hai. Pehle “API Settings” se set karo.
                    </p>
                @endif

            </div>
        </div>
    </div>

    <script>
        // IMPORTANT: Dropzone must be loaded globally OR uncomment CDN in head section above.
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
