{{-- resources/views/roles/index.blade.php --}}
<x-layouts.app :title="__('Roles')">

    <style>
        [x-cloak] { display: none !important; }
        .modal-open { overflow: hidden; } /* prevent body scroll when modal open */
    </style>

    <div
        x-data="rolesPage()"
        x-init="init()"
        class="flex flex-col gap-6"
    >

        <!-- Header -->
        <div class="flex flex-wrap gap-3 justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Assign Roles</h1>

            <div class="flex items-center gap-3">
      <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-neutral-800 text-gray-700 dark:text-gray-200">
        Selected: <span x-text="selectedCount"></span>
      </span>

                <button
                    type="button"
                    @click="submitAssign()"
                    :disabled="selectedCount === 0 || !document.getElementById('user')?.value"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Assign Selected
                </button>

                <button
                    type="button"
                    @click="open = true; $nextTick(() => document.getElementById('role-name')?.focus())"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
                    </svg>
                    Create Role
                </button>
            </div>
        </div>

        <!-- Assign Roles Form -->
        <form id="assignRoleForm" action="{{ route('roles.assign') }}" method="POST">
            @csrf
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="user" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select User</label>
                    <select name="user" id="user" required
                            class="mt-1 block w-full border-gray-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring focus:ring-indigo-200 dark:bg-neutral-800 dark:text-white">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-semibold text-gray-700 dark:text-white">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3"><input type="checkbox" id="all-roles"> All</th>
                        <th class="px-6 py-3">Role Name</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}" @change="updateCount()">
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $role->name }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button type="button"
                                            @click="openPerms({ id: {{ $role->id }}, name: '{{ $role->name }}' })"
                                            class="px-3 py-1 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
                                        Permissions
                                    </button>
                                    <button type="button"
                                            @click="selectedRole = '{{ $role->id }}'; delOpen = true"
                                            class="px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 text-sm">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No roles found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition">
                    Assign Roles
                </button>
            </div>
        </form>

        <!-- CREATE & DELETE modals stay same -->

        <!-- Permissions Modal -->
        <div x-show="permOpen" x-transition.opacity x-cloak @keydown.escape.window="permOpen = false"
             class="fixed inset-0 z-[100]" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/50" @click="permOpen = false"></div>
            <div class="relative w-full h-full overflow-y-auto">
                <div class="min-h-full flex items-center justify-center p-4">
                    <div
                        class="w-full max-w-3xl rounded-xl bg-white dark:bg-neutral-900 shadow-2xl border border-gray-200 dark:border-neutral-700
                 flex flex-col max-h-[85vh]">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Permissions — <span x-text="currentRoleName"></span>
                                </h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Checked = assigned. (<span x-text="permSelectedCount"></span> selected)
                                </p>
                            </div>
                            <button @click="permOpen = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">✕</button>
                        </div>

                        <!-- scrollable content -->
                        <form :action="permSyncAction" method="POST"
                              class="flex-1 overflow-y-auto px-5 py-4 space-y-4"
                              @submit.prevent="submitPermSync($event)">
                            @csrf
                            <div class="flex flex-wrap gap-3 items-center">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAllPerms()" class="px-3 py-1 rounded border text-sm">Select all</button>
                                    <button type="button" @click="clearAllPerms()" class="px-3 py-1 rounded border text-sm">Clear</button>
                                </div>

                                <div class="relative flex-1 min-w-[220px]">
                                    <input type="text" x-model="permQuery" placeholder="Search permissions…"
                                           class="w-full rounded-md border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white pl-3 pr-8 py-2">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400">⌕</span>
                                </div>
                            </div>

                            <div class="rounded border border-gray-200 dark:border-neutral-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                    <thead class="bg-gray-50 dark:bg-neutral-800 text-xs uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Permission</th>
                                        <th class="px-4 py-2 text-right">Assigned</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                    <template x-for="p in filteredPerms" :key="p.name">
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-100" x-text="p.name"></td>
                                            <td class="px-4 py-2 text-right">
                                                <input type="checkbox" class="h-4 w-4" :value="p.name"
                                                       :checked="currentPerms.has(p.name)"
                                                       @change="togglePerm(p.name, $event.target.checked)">
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filteredPerms.length === 0">
                                        <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No permissions match.</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div x-ref="permContainer"></div>
                            <div class="h-3"></div>
                        </form>

                        <!-- footer -->
                        <div class="px-5 py-3 border-t border-gray-200 dark:border-neutral-700 bg-white/90 dark:bg-neutral-900/90 backdrop-blur-sm">
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="permOpen = false"
                                        class="px-4 py-2 rounded-md border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    Cancel
                                </button>
                                <button @click="const f=$root.querySelector('form[method=POST][@submit\\.prevent]'); if(f){f.requestSubmit();}"
                                        class="px-5 py-2 rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON bootstrap -->
    <script type="application/json" id="all-permissions-json">{!! $permissions->pluck('name')->values()->toJson(JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/json" id="role-perms-json">{!! $rolePerms->toJson(JSON_UNESCAPED_UNICODE) !!}</script>

    <script>
        function rolesPage() {
            return {
                open:false, delOpen:false, selectedRole:null, selectedCount:0,
                permOpen:false, currentRoleId:null, currentRoleName:'',
                currentPerms:new Set(), allPerms:[], rolePermsMap:{}, permQuery:'',
                get permSelectedCount(){return this.currentPerms.size},
                get filteredPerms(){
                    const q=this.permQuery.trim().toLowerCase();
                    return this.allPerms.filter(n=>!q||n.toLowerCase().includes(q)).map(n=>({name:n}));
                },
                get permSyncAction(){
                    const id=this.currentRoleId??'';return `{{ route('roles.permissions.sync', ':id') }}`.replace(':id',id);
                },
                submitAssign(){document.getElementById('assignRoleForm')?.requestSubmit();},
                updateCount(){this.selectedCount=document.querySelectorAll('input[name="roles[]"]:checked').length;},
                init(){
                    try{
                        this.allPerms=JSON.parse(document.getElementById('all-permissions-json').textContent||'[]');
                        this.rolePermsMap=JSON.parse(document.getElementById('role-perms-json').textContent||'{}');
                    }catch(e){}
                    this.$watch('permOpen',v=>document.documentElement.classList.toggle('modal-open',v));
                    document.getElementById('all-roles')?.addEventListener('change',()=>{
                        const c=document.getElementById('all-roles').checked;
                        document.querySelectorAll('input[name="roles[]"]').forEach(cb=>cb.checked=c);
                        this.updateCount();
                    });
                    this.updateCount();
                },
                openPerms(r){
                    this.currentRoleId=r.id;this.currentRoleName=r.name;
                    this.currentPerms=new Set(this.rolePermsMap[r.id]||[]);
                    this.permQuery='';this.permOpen=true;this.rebuildHiddenPermInputs();
                },
                togglePerm(n,c){c?this.currentPerms.add(n):this.currentPerms.delete(n);this.rebuildHiddenPermInputs();},
                selectAllPerms(){this.allPerms.forEach(n=>this.currentPerms.add(n));this.rebuildHiddenPermInputs();},
                clearAllPerms(){this.currentPerms.clear();this.rebuildHiddenPermInputs();},
                rebuildHiddenPermInputs(){
                    const box=this.$refs.permContainer;if(!box)return;box.innerHTML='';
                    this.currentPerms.forEach(n=>{
                        const i=document.createElement('input');i.type='hidden';i.name='permissions[]';i.value=n;box.appendChild(i);
                    });
                },
                submitPermSync(e){this.rebuildHiddenPermInputs();e.target.submit();}
            }
        }
    </script>
</x-layouts.app>
