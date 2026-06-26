<?php

namespace App\Http\Controllers;

class StaticController extends Controller
{
    public function faq()
    {
        return view('static.faq');
    }

    public function about()
    {
        return view('static.about');
    }

    public function contact()
    {
        return view('static.contact');
    }

    public function dmca()
    {
        return view('static.dmca');
    }

    public function terms()
    {
        return view('static.terms');
    }
}
