<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommerceAccountRequest;
use App\Http\Resources\CommerceAccountResource;
use App\Models\CommerceAccount;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommerceAccountController extends Controller
{
    /**
     * Get commerce account
     *
     * @return CommerceAccountResource
     */
    public function get()
    {
        return CommerceAccountResource::make(Auth::user()->commerceAccount);
    }

    /**
     * Create commerce account
     *
     * @return CommerceAccountResource
     *
     * @throws AuthorizationException
     */
    public function create(CommerceAccountRequest $request)
    {
        $this->authorize('create', CommerceAccount::class);

        $commerceAccount = Auth::user()->commerceAccount()->create($request->validated());

        return CommerceAccountResource::make($commerceAccount);
    }

    /**
     * Upload account logo
     *
     * @param  Request  $request
     * @return CommerceAccountResource
     */
    public function setLogo(Request $request)
    {
        $commerceAccount = Auth::user()->commerceAccount()->firstOrFail();

        $request->validate([
            'file' => [
                'required',
                'mimetypes:image/png,image/jpeg',
                'dimensions:ratio=1',
                'file',
                'max:100',
            ],
        ]);

        $file = $request->file('file');

        $commerceAccount->logo = savePublicFile($file, $commerceAccount->path());
        $commerceAccount->save();

        return CommerceAccountResource::make($commerceAccount);
    }

    /**
     * Update commerce account
     *
     * @param  CommerceAccountRequest  $request
     * @return CommerceAccountResource
     */
    public function update(CommerceAccountRequest $request)
    {
        $commerceAccount = Auth::user()->commerceAccount()->firstOrFail();

        $commerceAccount->update($request->validated());

        return CommerceAccountResource::make($commerceAccount);
    }
}
