<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OperatingCountryResource;
use App\Models\OperatingCountry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OperatingCountryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_banks');
    }

    /**
     * Get available countries
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailable()
    {
        return $this->availableCountries()->values();
    }

    /**
     * Create operating country
     *
     * @param  Request  $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $available = $this->availableCountries();

        $validated = $this->validate($request, [
            'code' => ['required', Rule::in($available->pluck('code'))],
        ]);

        $country = collect($available->firstWhere('code', $validated['code']));

        OperatingCountry::create([
            'code' => $country->get('code'),
            'name' => $country->get('name'),
        ]);
    }

    /**
     * Paginate operating countries
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = paginate(OperatingCountry::withCount('banks'));

        return OperatingCountryResource::collection($records);
    }

    /**
     * Remove operating country
     *
     * @param  OperatingCountry  $country
     */
    public function delete(OperatingCountry $country)
    {
        $country->delete();
    }

    /**
     * Available countries
     *
     * @return \Illuminate\Support\Collection
     */
    protected function availableCountries()
    {
        $existing = OperatingCountry::all()->pluck('code')->toArray();

        return collect(config('countries'))->filter(function ($name, $code) use ($existing) {
            return !in_array($code, $existing);
        })->map(function ($name, $code) {
            return compact('name', 'code');
        });
    }
}
