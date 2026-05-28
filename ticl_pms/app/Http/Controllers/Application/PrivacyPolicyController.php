<?php

namespace App\Http\Controllers\Application;

use \App\Http\Controllers\Controller;

class PrivacyPolicyController extends Controller
{
    public function getPrivacyPolicy()
    {
        return view('privacypolicy');
    }
}
