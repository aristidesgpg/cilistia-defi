<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransferRecordResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class TransferRecordController extends Controller
{
    /**
     * Paginate transfer record
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = Auth::user()->transferRecords()->latest();

        if ($account = $request->query('account')) {
            $query = $query->where('wallet_accounts.id', $account);
        }

        return TransferRecordResource::collection(paginate($query));
    }
}
