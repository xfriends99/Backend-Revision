<?php namespace App\Http\ViewComposers;

use Carbon\Carbon;

class FooterComposer
{
    public function compose($view)
    {
        $now = Carbon::now('America/Argentina/Buenos_Aires');
        $morning = $now->clone()->startOfDay()->addHours(9);
        $afternoon = $now->clone()->startOfDay()->addHours(14);

        $whatsapp = null;

        // Whatsapp 9 to 14hs
        if ($now->between($morning, $afternoon) && !$now->isWeekend()) {
            $whatsapp = '5491170039669';
        }

        $view->with('whatsapp', $whatsapp);
    }
}
