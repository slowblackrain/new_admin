<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function academy()
    {
        return view('front.page.academy');
    }

    public function ats()
    {
        return view('front.page.ats');
    }
}
