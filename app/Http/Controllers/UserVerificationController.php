<?php

namespace App\Http\Controllers;

use App\Helpers\FileVault;
use App\Http\Resources\RequiredDocumentResource;
use App\Http\Resources\UserAddressResource;
use App\Http\Resources\UserDocumentResource;
use App\Models\RequiredDocument;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserVerificationController extends Controller
{
    /**
     * Get verification data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        return response()->json([
            'basic' => Auth::user()->verification->getBasic(),
            'advanced' => Auth::user()->verification->getAdvanced(),
            'level' => Auth::user()->verification->getLevel(),
        ]);
    }

    /**
     * Get address resource
     *
     * @return UserAddressResource
     */
    public function getAddress()
    {
        return UserAddressResource::make(Auth::user()->address);
    }

    /**
     * Update user address
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     */
    public function updateAddress(Request $request)
    {
        $address = Auth::user()->address()->firstOrNew();

        if ($address->status == 'approved') {
            abort(403, trans('common.forbidden'));
        }

        $data = $this->validate($request, [
            'address' => 'required|string|max:1000',
            'unit' => 'required|string|max:200',
            'city' => 'required|string|max:200',
            'postcode' => 'required|string|max:200',
            'state' => 'required|string|max:200',
        ]);

        $address->status = 'pending';

        $address->fill($data)->save();
    }

    /**
     * Get documents
     *
     * @return Collection
     */
    public function getDocuments()
    {
        return RequiredDocument::enabled()->get()->map(function (RequiredDocument $requirement) {
            $document = $requirement->getDocument(Auth::user());

            return [
                'verified' => $document?->status === 'approved',
                'requirement' => RequiredDocumentResource::make($requirement),
                'document' => UserDocumentResource::make($document),
            ];
        });
    }

    /**
     * Upload document
     *
     * @param  Request  $request
     *
     * @throws ValidationException|FileNotFoundException
     */
    public function uploadDocument(Request $request)
    {
        Auth::user()->acquireLock(function (User $user) use ($request) {
            $requirement = RequiredDocument::enabled()
                ->findOrFail((int) $request->get('requirement'));

            $existing = $requirement->getDocument($user);

            if ($existing && $existing->status !== 'rejected') {
                abort(403, trans('verification.information_pending'));
            }

            $document = new UserDocument();
            $document->data = $this->processFile($request);
            $document->requirement()->associate($requirement);
            $user->documents()->save($document);
        });
    }

    /**
     * Process file data
     *
     * @param  Request  $request
     * @return array
     *
     * @throws ValidationException|FileNotFoundException
     */
    protected function processFile(Request $request)
    {
        $this->validate($request, [
            'data' => 'required|file|mimes:png,jpeg,doc,docx,pdf|max:5120',
        ]);

        $file = $request->file('data');

        return [
            'extension' => $file->clientExtension(),
            'path' => FileVault::encrypt($file->get()),
            'mimeType' => $file->getMimeType(),
        ];
    }
}
