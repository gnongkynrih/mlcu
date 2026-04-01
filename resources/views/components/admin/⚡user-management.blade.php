<?php

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\User;
use Spatie\Permission\Models\Role;
new class extends Component
{
    use Toast;
    public $showInputForm = false;
    public $name;
    public $email;
    public $password;
    public $confirm_password;
    public $roles = [];
    public $selectedRole;

    public function rules(){
        return [
            'selectedRole' =>'required|exists:roles,name',
            'name' =>'required|string|min:3|max:60',
            'email' =>'required|email|unique:users,email',
            'password' =>'required|string|min:6',
            'confirm_password' =>'required|string|min:6|same:password'
        ];
    }
    public function mount(){
        $this->showInputForm = false;
        //get all the roles from roles table
        $this->roles = Role::all();
        // $this->roles = $this->roles->pluck('name', 'id')->toArray();
        
    }
    public function OpenCloseModal()
    {
        $this->showInputForm = !$this->showInputForm;
        // if($this->showInputForm==true){
        //     $this->showInputForm =false;
        // }else{
        //     $this->showInputForm = true;
        // }
    }
    
    public function save(){
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password
        ]);
        $user->assignRole($this->selectedRole);

        $this->toast(
            type:'success',
            title:"User added successfully"
        );
    }
};
?>

<div class="max-w-3xl rounded-lg mx-auto p-6 bg-indigo-300">
    <h1 class="text-2xl font-bold mb-6">User Management</h1>
    <!-- Add your user management content here -->
    <x-card  shadow separator>
        <div class="flex items-center gap-4">
                <div class="flex-1">
                    <x-input class="w-full" type="text" placeholder="Search users..." />
                </div>
            <x-button 
                class="btn-primary"
                icon="o-plus"
                type="button" 
                label="Add New User" 
                wire:click="OpenCloseModal"
            />
        </div>
    </x-card>

    <x-modal wire:model="showInputForm" title="User" subtitle="User Management">
        <x-form no-separator wire:submit="save">
            <x-select label="Select Role" 
                wire:model="selectedRole" 
                :options="$roles" 
                option-label="name" 
                option-value="name" 
                placeholder="Select a role"
            />
            <x-input wire:model="name" label="Name" icon="o-user" placeholder="The full name" />
            <x-input wire:model="email" label="Email" icon="o-envelope" placeholder="The e-mail" />
            <x-input wire:model="password" label="Password" type="password" icon="o-key" placeholder="The password" />
            <x-input wire:model="confirm_password" label="Confirm Password" type="password" icon="o-key" placeholder="Confirm password" />
            {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
            <x-slot:actions>
                <x-button label="Cancel" wire:click="OpenCloseModal" />
                <x-button label="Save" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
</div>