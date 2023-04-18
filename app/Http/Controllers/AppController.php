<?php

namespace App\Http\Controllers;

use App\Helpers\LocaleManager;
use App\Http\Resources\UserResource;
use App\Models\Module;
use ArrayObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    /**
     * Locale manager
     *
     * @var LocaleManager
     */
    protected LocaleManager $localeManager;

    /**
     * AppController constructor.
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * Show admin dashboard.
     *
     * @return View
     */
    public function admin()
    {
        return view('admin', [
            'data' => $this->getData(),
        ]);
    }

    /**
     * Where it all begins!
     *
     * @return View
     */
    public function main()
    {
        return view('main', [
            'data' => $this->getData(),
        ]);
    }

    /**
     * Get app data
     *
     * @return array
     */
    protected function getData()
    {
        return [
            'name' => config('app.name'),
            'broadcast' => getBroadcastConfig(),
            'settings' => [
                'recaptcha' => [
                    'enable' => config('services.recaptcha.enable'),
                    'sitekey' => config('services.recaptcha.sitekey'),
                    'size' => config('services.recaptcha.size'),
                ],
                'locales' => $this->getLocales(),
                'modules' => $this->getModules(),
                'baseCurrency' => app('exchanger')->config('base_currency'),
                'theme' => settings()->theme->all(),
                'brand' => settings()->brand->all(),
            ],
            'auth' => [
                'credential' => config('auth.credential'),
                'user' => $this->getAuthUser(),
                'userSetup' => settings()->get('user_setup'),
            ],
            'notification' => session('notification'),
            'csrfToken' => csrf_token(),
        ];
    }

    /**
     * Get user object
     *
     * @return UserResource
     */
    protected function getAuthUser()
    {
        Auth::user()?->updatePresence('online');

        return UserResource::make(Auth::user());
    }

    /**
     * Get supported locales object
     *
     * @return ArrayObject
     */
    protected function getLocales()
    {
        $locales = $this->localeManager->getLocales();

        return new ArrayObject($locales->toArray());
    }

    /**
     * Get modules
     *
     * @return Collection
     */
    protected function getModules()
    {
        return Module::all()->pluck('status', 'name');
    }

    /**
     * Get IP Info
     *
     * @return array
     */
    public function ipInfo(Request $request)
    {
        return geoip($request->ip())->toArray();
    }
}
