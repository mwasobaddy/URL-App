<?php

use function Livewire\Volt\{state, computed, mount};
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

state([
    'roles' => [],
    'permissions' => [],
    'showCreateModal' => false,
    'showEditModal' => false,
    'showDeleteModal' => false,
    'editingRole' => null,
    'form' => [
        'name' => '',
        'permissions' => [],
    ],
]);

mount(function () {
    $this->refreshData();
});

$refreshData = function () {
    $this->roles = Role::with('permissions')->get();
    $this->permissions = Permission::all();
};

$createRole = function () {
    $this->validate([
        'form.name' => 'required|string|max:255|unique:roles,name',
        'form.permissions' => 'required|array',
    ]);

    $role = Role::create(['name' => $this->form['name']]);
    $role->syncPermissions($this->form['permissions']);

    $this->resetForm();
    $this->refreshData();
    $this->showCreateModal = false;

    $this->dispatch('swal:toast', [
        'type' => 'success',
        'message' => 'Role created successfully.',
    ]);
};

$editRole = function (Role $role) {
    $this->editingRole = $role;
    $this->form = [
        'name' => $role->name,
        'permissions' => $role->permissions->pluck('name')->toArray(),
    ];
    $this->showEditModal = true;
};

$updateRole = function () {
    $this->validate([
        'form.name' => 'required|string|max:255|unique:roles,name,' . $this->editingRole->id,
        'form.permissions' => 'required|array',
    ]);

    $this->editingRole->update(['name' => $this->form['name']]);
    $this->editingRole->syncPermissions($this->form['permissions']);

    $this->resetForm();
    $this->refreshData();
    $this->showEditModal = false;

    $this->dispatch('swal:toast', [
        'type' => 'success',
        'message' => 'Role updated successfully.',
    ]);
};

$confirmDelete = function (Role $role) {
    $this->editingRole = $role;
    $this->showDeleteModal = true;
};

$deleteRole = function () {
    $this->editingRole->delete();
    
    $this->editingRole = null;
    $this->refreshData();
    $this->showDeleteModal = false;

    $this->dispatch('swal:toast', [
        'type' => 'success',
        'message' => 'Role deleted successfully.',
    ]);
};

$resetForm = function () {
    $this->form = [
        'name' => '',
        'permissions' => [],
    ];
    $this->editingRole = null;
};

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Role Management
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage roles and their permissions
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button
                wire:click="$set('showCreateModal', true)"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
                Create Role
            </button>
        </div>
    </div>

    {{-- Roles List --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid gap-6">
                @foreach($roles as $role)
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $role->name }}
                            </h3>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($role->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button
                                wire:click="editRole({{ $role->id }})"
                                class="text-sm text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300"
                            >
                                Edit
                            </button>
                            @unless(in_array($role->name, ['admin', 'free', 'premium']))
                                <button
                                    wire:click="confirmDelete({{ $role->id }})"
                                    class="text-sm text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Create Role Modal --}}
    <x-dialog-modal wire:model.live="showCreateModal">
        <x-slot name="title">
            Create New Role
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Role Name
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-zinc-800 dark:border-zinc-700"
                    />
                    @error('form.name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Permissions
                    </label>
                    <div class="mt-2 space-y-2">
                        @foreach($permissions as $permission)
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    wire:model="form.permissions"
                                    value="{{ $permission->name }}"
                                    class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500 focus:ring-opacity-50"
                                />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('form.permissions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showCreateModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ml-3" wire:click="createRole" wire:loading.attr="disabled">
                Create Role
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Edit Role Modal --}}
    <x-dialog-modal wire:model.live="showEditModal">
        <x-slot name="title">
            Edit Role
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Role Name
                    </label>
                    <input
                        type="text"
                        id="edit-name"
                        wire:model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-zinc-800 dark:border-zinc-700"
                        @if(in_array($form['name'], ['admin', 'free', 'premium'])) disabled @endif
                    />
                    @error('form.name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Permissions
                    </label>
                    <div class="mt-2 space-y-2">
                        @foreach($permissions as $permission)
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    wire:model="form.permissions"
                                    value="{{ $permission->name }}"
                                    class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-500 focus:ring-opacity-50"
                                />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('form.permissions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ml-3" wire:click="updateRole" wire:loading.attr="disabled">
                Update Role
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Delete Role Modal --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            Delete Role
        </x-slot>

        <x-slot name="content">
            Are you sure you want to delete this role? This action cannot be undone.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="deleteRole" wire:loading.attr="disabled">
                Delete Role
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
