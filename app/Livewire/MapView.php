<?php

namespace App\Livewire;

use Livewire\Component;

class MapView extends Component
{
    public function render()
    {
        return view('livewire.map-view')->layout('layouts.app');
    }
}
