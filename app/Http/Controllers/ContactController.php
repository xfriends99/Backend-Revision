<?php

namespace App\Http\Controllers;

use App\Mail\ContactReceived;
use App\Models\User;
use App\Services\Cloud\MultiAnalyticsFactory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $layout = current_platform()->key;

        return view($layout . '.contact');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'fullname' => 'required',
            'email'    => 'required|email|exists:users,email',
            'message'  => 'required',
        ], [
            'email.exists' => 'El usuario no se encuentra registrado bajo esa dirección de e-mail.'
        ]);

        try {
            // Track with MultyAnalytics
            /** @var User $user */
            if ($user = Auth::user()) {
                MultiAnalyticsFactory::trackUser($user, 'Contact');
            } else {
                MultiAnalyticsFactory::trackGuest($user, 'Contact');
            }

            // Send mail
            Mail::send(new ContactReceived($request->get('fullname'), $request->get('email'), $request->get('message')));

            return redirect()->back()->with('alert_success', 'Mensaje enviado correctamente.');
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return redirect()->back()->withErrors('No se logró enviar el mensaje, intente nuevamente.');
        }
    }
}
