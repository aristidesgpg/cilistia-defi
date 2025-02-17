<?php

namespace App\Http\Controllers;

use App\Helpers\Settings;
use App\Models\Role;
use App\Models\User;
use App\Rules\Username;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class InstallerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('installer.show');
    }

    /**
     * Show installer
     *
     * @param  Settings  $settings
     * @return Factory|View
     */
    public function view(Settings $settings)
    {
        $data = $this->getData($settings);

        return view('installer', compact('data', 'settings'));
    }

    /**
     * Get installer settings
     *
     * @param  Settings  $settings
     * @return array
     */
    protected function getData(Settings $settings)
    {
        return [
            'name' => config('app.name'),
            'settings' => [
                'installer' => [
                    'license' => app('installer')->installed(),
                    'display' => true,
                ],
                'theme' => [
                    'mode' => $settings->theme->get('mode'),
                    'direction' => $settings->theme->get('direction'),
                    'color' => $settings->theme->get('color'),
                ],
            ],
            'notification' => session('notification'),
            'csrfToken' => csrf_token(),
        ];
    }

    /**
     * Install license code
     *
     * @param  Request  $request
     * @return array|mixed
     *
     * @throws ValidationException
     */
    public function install(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|uuid',
        ]);

        $code = $request->get('code');

        try {
            return app('installer')->install($code);
        } catch (RequestException $e) {
            $errors = $e->response->json('errors.code');

            if (!is_array($errors)) {
                return abort(403, $e->response->json('message'));
            } else {
                return abort(403, Arr::first($errors));
            }
        }
    }

    /**
     * Register root account
     *
     * @param  Request  $request
     * @return void
     *
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        if (User::superAdmin()->exists()) {
            return abort(403, trans('auth.action_forbidden'));
        }

        $this->validate($request, [
            'name' => ['required', 'string', 'min:3', 'max:25', 'unique:users', new Username],
            'email' => ['required', 'string', 'email:rfc,dns,spoof', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', Password::defaults()],
        ]);

        $this->createUser($request->all());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function createUser(array $data)
    {
        $user = User::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
        ]);

        return tap($user, function (User $user) {
            $user->assignRole(Role::superAdmin());
            $user->assignRole(Role::operator());
        });
    }
}
