<?php

use Livewire\Component;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;
new class extends Component
{
    use Toast;
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount(): void
    {
        // if (Auth::user()) {
        //     return redirect('/');
        // }
    }

    public function login()
    {
        $credentials = $this->validate();
        if (Auth::attempt($credentials)) {
            //if the user is authenticated, then regenerate the session
            request()->session()->regenerate();
            return redirect()->intended('/');
        }

        $this->toast(
            type:'warning',
            title: 'Invalid credentials',
        );
    }

    
};
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-sm">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-semibold text-gray-900">Sign in</h1>
            <p class="text-gray-500 mt-2">Welcome back to MLCU</p>
        </div>

        <!-- Login Form -->
        <x-form wire:submit="login" class="space-y-6">
            <x-input 
                placeholder="Email" 
                wire:model="email" 
                icon="o-envelope"
            />
            <x-input 
                placeholder="Password" 
                wire:model="password" 
                type="password" 
                icon="o-key"
            />

            <x-button 
                label="Continue" 
                type="submit" 
                class="btn-neutral w-full" 
                spinner="login" 
            />

            <p class="text-center text-gray-500 text-sm">
                No account? <a href="/register" class="text-gray-900 hover:underline">Sign up</a>
            </p>
        </x-form>
    </div>
</div>
