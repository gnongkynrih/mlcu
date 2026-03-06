<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="flex justify-center">
    <x-form wire:submit="save">
        <x-input label="Name" />

        <x-slot:actions>
            <x-button label="Cancel" />
            <x-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>