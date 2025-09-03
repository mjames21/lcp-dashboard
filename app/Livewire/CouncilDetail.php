<?php

namespace App\Livewire;

use Livewire\Component;

class CouncilDetail extends Component
{
    public function render()
    {
        return view('livewire.council-detail')->layout('layouts.app');
    }
}
