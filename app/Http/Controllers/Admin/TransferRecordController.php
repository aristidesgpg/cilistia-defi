<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransferRecordResource;
use App\Models\TransferRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransferRecordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:manage_wallets');
    }

    /**
     * Paginate transfer records
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $query = TransferRecord::query()->latest();

        $this->filterByUser($query, $request);

        return TransferRecordResource::collection(paginate($query));
    }

    /**
     * Remove transfer record
     *
     * @param  TransferRecord  $record
     * @return void
     */
    public function remove(TransferRecord $record)
    {
        $record->acquireLock(function (TransferRecord $record) {
            if ($record->isRemovable()) {
                return $record->delete();
            }
        });
    }

    /**
     * Filter query by user
     *
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function filterByUser(Builder $query, Request $request)
    {
        if ($search = $request->get('searchUser')) {
            $query->whereHas('walletAccount.user', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }
    }
}
