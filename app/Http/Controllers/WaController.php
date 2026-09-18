<?php

namespace App\Http\Controllers;

use App\Wa\WaProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Throwable;

class WaController extends Controller
{
    public function page(): View
    {
        return view('wa');
    }

    /** Estado + QR vigente. La página lo consulta cada 5 s. */
    public function qr(WaProvider $wa): JsonResponse
    {
        try {
            $res = $wa->connect();
        } catch (Throwable $e) {
            return response()->json(['state' => 'error', 'qr' => null, 'error' => $e->getMessage()], 200);
        }

        return response()->json($res);
    }
}
