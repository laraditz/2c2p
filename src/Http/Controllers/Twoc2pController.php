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
        // logger()->info('2C2P Backend : Received', $request->all());

        if ($request->payload) {
            try {
                $decoded = app('Twoc2p')->decodeJWT($request->payload);

                if ($decoded && data_get($decoded, 'invoiceNo')) {
                    if (is_object($decoded)) { // if received as object, convert into array
                        $decoded = json_decode(json_encode($decoded), true);
                    }

                    event(new BackendReceived($decoded));

                    Twoc2pPayment::create([
                        'action' => Str::after(__METHOD__, '::'),
                        'response' => $decoded
                    ]);
                } else {
                    logger()->error('2C2P Backend : Failed to decode token ' . $request->payload);
                }
            } catch (\Throwable $th) {
                //throw $th;
                logger()->error('2C2P Backend :' . $th->getMessage());
            }
        } else {
            // no payload received
            logger()->error('2C2P Backend : No payload');
        }
    }
}
