<?php

use Livewire\Component;

new class extends Component
{
    public $tableSession = [];
    public function mount(){
        //get the table session from the session
        $this->tableSession = session('table_selection');
        //if the table is not selected then go back
        if(!$this->tableSession){
            return redirect()->route('pos.table-selection');
        }
    }
};
?>

<div>
    <x-card title="{{$tableSession['table_name']}}" subtitle="Number of Guests: {{$tableSession['guest_count']}}" shadow separator>
        I have title, subtitle and separator.
    </x-card>
</div>