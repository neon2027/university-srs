<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('pages.legal.terms');
    }

    public function privacy(): View
    {
        return view('pages.legal.privacy');
    }

    public function cookies(): View
    {
        return view('pages.legal.cookies');
    }

    public function dataProtection(): View
    {
        return view('pages.legal.data-protection');
    }

    public function transparency(): View
    {
        return view('pages.legal.transparency');
    }
}
