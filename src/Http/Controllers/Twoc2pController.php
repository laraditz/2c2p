<?php

namespace Laraditz\Twoc2p\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laraditz\Twoc2p\Models\Twoc2pPayment;
use Laraditz\Twoc2p\Events\BackendReceived;

class Twoc2pController extends Controller
{
    public function backend(Request $request)
    {
        // info('Twoc2p Backend receive.', $request->all());
        event(new BackendReceived($request->all()));

        Twoc2pPayment::create([
            'action' => Str::after(__METHOD__, '::'),
            'response' => $request->all()
        ]);
    }
}
