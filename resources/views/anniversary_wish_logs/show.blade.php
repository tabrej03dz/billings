<x-layouts.app :title="__('Anniversary Wish Log Details')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('anniversary-wish-logs.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                    Back
                </a>
            </div>
        </div>

        @php
            $badge = match($log->status) {
                'success' => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
                'failed'  => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                default   => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800',
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Wish Date</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ optional($log->wish_date)->format('d M, Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Wish Year</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $log->wish_year ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Phone</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $log->phone }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Anniversary ID</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $log->anniversary_id ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Business ID</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $log->business_id ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs border {{ $badge }}">
                            {{ strtoupper($log->status) }}
                        </span>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Created At</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ optional($log->created_at)->format('d M, Y h:i A') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Updated At</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ optional($log->updated_at)->format('d M, Y h:i A') }}
                        </div>
                    </div>

                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Quick Actions</div>

                <div class="flex flex-col gap-2">
                    <form method="POST" action="{{ route('anniversary-wish-logs.success', $log->id) }}">
                        @csrf
                        <button class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Mark Success
                        </button>
                    </form>

                    <form method="POST" action="{{ route('anniversary-wish-logs.failed', $log->id) }}">
                        @csrf
                        <button class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Mark Failed
                        </button>
                    </form>

                    <form method="POST" action="{{ route('anniversary-wish-logs.resend', $log->id) }}"
                          onsubmit="return confirm('Resend anniversary wish to {{ $log->phone }}?');">
                        @csrf
                        <button class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Resend Wish
                        </button>
                    </form>

                    <form method="POST" action="{{ route('anniversary-wish-logs.destroy', $log->id) }}"
                          onsubmit="return confirm('Delete this log?');">
                        @csrf
                        @method('DELETE')
                        <button class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-800 dark:text-white bg-gray-100 dark:bg-neutral-800 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-700 border border-gray-200 dark:border-gray-700">
                            Delete Log
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Message</div>
                <pre class="text-xs whitespace-pre-wrap break-words text-gray-700 dark:text-gray-300">{{ $log->message ?? '-' }}</pre>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Response</div>
                <pre class="text-xs whitespace-pre-wrap break-words text-gray-700 dark:text-gray-300">{{ $log->response ?? '-' }}</pre>
            </div>
        </div>

    </div>
</x-layouts.app>