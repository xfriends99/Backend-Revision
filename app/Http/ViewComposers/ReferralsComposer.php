<?php namespace App\Http\ViewComposers;

class ReferralsComposer
{
    public function compose($view)
    {
        $request = request();
        $token = $request->get('ref');

        $view->with('token', $token);
    }
}
