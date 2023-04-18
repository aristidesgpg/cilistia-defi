<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankResource;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankController extends Controller
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
     * Paginate bank
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate()
    {
        $records = paginate(Bank::latest()->with('operatingCountries'));

        return BankResource::collection($records);
    }

    /**
     * Create bank
     *
     * @param  Request  $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $validated = $this->validate($request, [
            'name' => ['required', 'string', 'max:250', 'unique:banks'],
            'operating_countries' => ['required', 'array'],
            'operating_countries.*' => ['required', 'exists:operating_countries,code'],
        ]);

        $bank = Bank::create(['name' => $validated['name']]);

        $bank->operatingCountries()->sync($validated['operating_countries']);
    }

    /**
     * Update bank
     *
     * @param  Request  $request
     * @param  Bank  $bank
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Bank $bank)
    {
        $validated = $this->validate($request, [
            'name' => ['required', 'string', 'max:250', $this->uniqueRule($bank)],
            'operating_countries' => ['required', 'array'],
            'operating_countries.*' => ['required', 'exists:operating_countries,code'],
        ]);

        $bank->update(['name' => $validated['name']]);

        $bank->operatingCountries()->sync($validated['operating_countries']);
    }

    /**
     * Delete bank
     *
     * @param  Bank  $bank
     */
    public function delete(Bank $bank)
    {
        $bank->delete();
    }

    /**
     * Upload logo
     *
     * @param  Request  $request
     * @param  Bank  $bank
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setLogo(Request $request, Bank $bank)
    {
        $this->validate($request, [
            'file' => 'required|mimetypes:image/png,image/jpeg|dimensions:ratio=1|file|max:100',
        ]);

        $file = $request->file('file');
        $logo = savePublicFile($file, $bank->path());
        $bank->update(['logo' => $logo]);
    }

    /**
     * Get unique rule
     *
     * @return \Illuminate\Validation\Rules\Unique
     */
    protected function uniqueRule(Bank $bank)
    {
        return Rule::unique('banks')->ignore($bank);
    }

    /**
     * Get operating banks
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getOperatingBanks()
    {
        $banks = Bank::latest()->has('operatingCountries')
            ->with('operatingCountries')->get();

        return BankResource::collection($banks);
    }
}
