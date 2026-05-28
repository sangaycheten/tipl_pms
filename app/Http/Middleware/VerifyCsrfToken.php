<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'fetcherrordetail',
        'fetchcriteriainput',
        'uploadexcelapplicant',
        'forgotpassword',
        'fetchcategoriesondepartment',
        'fetchsubordinategoals',
        'fetchsubordinategoalsl2',
        'savefile',
        '_api_@login',
        'deletemultiplegoalsandtargets',
        'approvemultiplegoalsandtargets',
        'resendmultiplegoalsandtargets',
        'deletemultipletasktargets',
        'approvemultipletasktargets',
        'resendmultipletasktargets',
    ];
}
