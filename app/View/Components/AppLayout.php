<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout publik & receiver (Figma: top navigation + footer, tab bar di mobile).
 */
class AppLayout extends Component
{
    public function __construct(public bool $narrow = false) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
