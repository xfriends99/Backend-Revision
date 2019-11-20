<?php namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Session;

class AlertsComposer {

    public function compose($view)
    {
        $alerts = [];
        if ($view->errors && $view->errors->any())
        {
            if ($view->errors->count() == 1)
                $alerts['danger'] = $view->errors->first();
            else
                $alerts['danger'] = $view->errors->all();
        }

        if (Session::get('alert_warning'))
            $alerts['warning'] = Session::get('alert_warning');

        if (Session::get('alert_success'))
            $alerts['success'] = Session::get('alert_success');

        if (Session::get('alert_info'))
            $alerts['info'] = Session::get('alert_info');

        if (Session::get('status'))
            $alerts['success'] = Session::get('status');

        $view->with('alerts', $alerts);
    }
}