<?php namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Auth;
use LaravelLocalization;
use URL;

class NavbarComposer
{
    private $menu = [];

    private $sites = [];

    public function compose($view)
    {
        $this->buildMenu();
        $this->setActiveItems();

        $is_calculator = preg_match('/calculator/', URL::current());

        $view->with('menu', $this->menu);
        $view->with('sites', $this->sites);
        $view->with('is_calculator', $is_calculator);
    }

    private function buildMenu()
    {
        $this->menu = [];

        // Inicio
        $this->menu[] = [
            'title' => 'Blog',
            'link'  => request()->getScheme() . '://blog.' . request()->getHttpHost() . '/',
            'icon'  => null,
            'class' => 'd-md-none d-lg-flex'
        ];

        // Ayuda
        $this->menu[] = [
            'title'     => 'Ayuda',
            'link'      => route('contact.index'),
            'icon'      => null,
            'sub_items' => [
                ['title' => 'Preguntas Frecuentes', 'link' => route('faq')],
                ['title' => 'Productos Prohibidos', 'link' => route('restricted-products')],
                ['title' => 'Contacto', 'link' => route('contact.index')],
            ]
        ];

        // Calculator
        $this->menu[] = [
            'title' => 'Calculadora',
            'link'  => route('calculator.index'),
            'icon'  => null,
        ];

        if ($user = Auth::user()) {
            // Tu cuenta
            $this->menu[] = [
                'title' => 'Mi Cuenta',
                'link'  => route('account.index'),
                'icon'  => null,
            ];

            // User logged
            $this->menu[] = [
                'title'      => $user->full_name,
                'link'       => '#',
                'icon'       => null,
                'class'      => 'd-sm-inline ml-3 mr-3',
                'link_class' => 'btn btn-link btn-outline-primary',
                'alignment' => 'right',
                'sub_items'  => [
                    ['title' => 'Mi Cuenta', 'link' => '/account'],
                    ['title' => 'Cerrar Sesión', 'link' => '/logout']
                ]
            ];
        } else {
            // Tu cuenta
            $this->menu[] = [
                'title' => 'Ingresar',
                'link'  => route('account.index'),
                'icon'  => null,
            ];

            // Register
            $this->menu[] = [
                'title'      => 'Registrate',
                'link'       => route('casillerosmailamericas.register.index'),
                'icon'       => null,
                'class'      => 'nav-button-register',
                'link_class' => 'btn btn-link btn-outline-primary'
            ];
        }

        $countries = [];
        foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties) {
            $countries[] = [
                'title' => $properties['name'],
                'link'  => LaravelLocalization::getLocalizedURL($localeCode, null, [], true),
                'flag'  => strtolower($properties['regional']),
            ];
        }

        // Country chooser
        $this->menu[] = [
            'title'     => \LaravelLocalization::getCurrentLocaleName(),
            'link'      => '#',
            'flag'      => strtolower(\LaravelLocalization::getCurrentLocaleRegional()),
            'sub_items' => $countries,
            'alignment' => 'right'
        ];
    }

    private function setActiveItems()
    {
        foreach ($this->menu as &$menu) {
            if ($menu['link'] && $menu['link'] == URL::current()) {
                $menu['active'] = true;
                break;
            } else {
                if (isset($menu['sub_items'])) {
                    foreach ($menu['sub_items'] as $item) {
                        if ($item['link'] == URL::current()) {
                            $menu['active'] = true;
                            break;
                        }
                    }
                }
            }
        }
    }
}
