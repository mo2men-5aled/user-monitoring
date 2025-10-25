<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">System Settings</h1>
                            <p class="text-gray-600 mt-1">Configure system-wide settings and parameters</p>
                        </div>
                        <button onclick="openSettingsModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Setting
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($settings as $setting)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $setting->key }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @if($setting->key === 'idle_timeout')
                                            {{ $setting->value }} seconds
                                        @elseif($setting->key === 'monitoring_enabled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        @else
                                            {{ Str::limit($setting->value, 30) }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $setting->description ? Str::limit($setting->description, 50) : 'No description' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="openEditModal({{ $setting->id }}, '{{ $setting->key }}', '{{ addslashes($setting->value) }}', '{{ addslashes($setting->description ?? '') }}')" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.settings.destroy', $setting->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this setting? This may affect system functionality.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6">
                        {{ $settings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Settings Modal -->
    <div id="settings-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-xl rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 id="settings-modal-title" class="text-lg font-semibold text-gray-800">Add Setting</h3>
                    <button onclick="closeSettingsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="settings-form" method="POST" action="{{ route('admin.settings.store') }}" class="mt-4">
                    @csrf
                    <input type="hidden" id="setting-id" name="id">
                    <div class="mb-4">
                        <label for="key" class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                        <input type="text" id="key" name="key" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                        <p class="mt-1 text-xs text-gray-500">Use lowercase letters, numbers, and underscores only (e.g., idle_timeout)</p>
                    </div>
                    <div class="mb-4">
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                        <input type="text" id="value" name="value" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                        <p id="value-help" class="mt-1 text-xs text-gray-500">Enter the setting value</p>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="3"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="closeSettingsModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Save Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Update help text based on key selection
        document.getElementById('key').addEventListener('input', function() {
            const key = this.value.toLowerCase();
            const helpText = document.getElementById('value-help');
            
            if (key === 'idle_timeout') {
                helpText.textContent = 'Enter timeout in seconds (e.g., 300 for 5 minutes)';
            } else if (key === 'monitoring_enabled') {
                helpText.textContent = 'Enter "true" to enable or "false" to disable monitoring';
            } else {
                helpText.textContent = 'Enter the setting value';
            }
        });
        
        function openSettingsModal() {
            document.getElementById('settings-modal-title').textContent = 'Add Setting';
            document.getElementById('settings-form').method = 'POST';
            document.getElementById('settings-form').action = '{{ route('admin.settings.store') }}';
            document.getElementById('settings-form').reset();
            document.getElementById('setting-id').value = '';
            document.getElementById('key').removeAttribute('readonly');
            document.getElementById('value-help').textContent = 'Enter the setting value';
            document.getElementById('settings-modal').classList.remove('hidden');
        }
        
        function openEditModal(id, key, value, description) {
            document.getElementById('settings-modal-title').textContent = 'Edit Setting';
            document.getElementById('settings-form').method = 'PUT';
            document.getElementById('settings-form').action = '/admin/settings/' + id;
            document.getElementById('setting-id').value = id;
            document.getElementById('key').value = key;
            document.getElementById('value').value = value;
            document.getElementById('description').value = description;
            document.getElementById('key').setAttribute('readonly', true);
            
            // Update help text for known settings
            const helpText = document.getElementById('value-help');
            if (key === 'idle_timeout') {
                helpText.textContent = 'Enter timeout in seconds (e.g., 300 for 5 minutes)';
            } else if (key === 'monitoring_enabled') {
                helpText.textContent = 'Enter "true" to enable or "false" to disable monitoring';
            } else {
                helpText.textContent = 'Enter the setting value';
            }
            
            document.getElementById('settings-modal').classList.remove('hidden');
        }
        
        function closeSettingsModal() {
            document.getElementById('settings-modal').classList.add('hidden');
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('settings-modal');
            if (event.target === modal) {
                closeSettingsModal();
            }
        }
    </script>
</x-app-layout>