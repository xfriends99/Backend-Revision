<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function home()
    {
        $layout = current_platform()->key;

        return view($layout . '.home');
    }

    public function howItWorks()
    {
        $layout = current_platform()->key;

        return view($layout . '.how_it_works');
    }

    public function countries()
    {
        return view('countries');
    }

    public function terms()
    {
        $layout = current_platform()->key;

        return view($layout . '.terms');
    }

    public function restrictedProducts()
    {
        $layout = current_platform()->key;

        $site = current_site();

        return view($layout . '.restricted_products', compact('site'));
    }

    public function faq()
    {
        $layout = current_platform()->key;
        $country = \Str::lower(current_site()->getCountryCode());

        return view("{$layout}.faq", compact('country'))
            ->nest('details', "{$layout}.faq.{$country}");
    }

    public function privacy()
    {
        return view('privacy');
    }
}
