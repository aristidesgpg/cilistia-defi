<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    /**
     * Get banks for user's country
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all()
    {
        return BankResource::collection(Auth::user()->operatingBanks()->get());
    }
}
