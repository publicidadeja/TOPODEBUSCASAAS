<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ToggleSwitch extends Component
{
    public $name;
    public $checked;
    public $label;

    public function __construct($name, $checked = false, $label = '')
    {
        $this->name = $name;
        $this->checked = $checked;
        $this->label = $label;
    }

    public function render()
    {
        return view('components.toggle-switch');
    }
}