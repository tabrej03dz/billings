<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AnniversaryController extends Controller
{
    public function run(Request $request)
    {
        // ✅ Simple token auth
        //        $token = $request->header('X-API-TOKEN') ?: $request->get('token');

        //        if (!$token || $token !== config('services.birthday_wish.token')) {
        //            return response()->json([
        //                'ok' => false,
        //                'message' => 'Unauthorized'
        //            ], 401);
        //        }

        // ✅ Optional: dry_run support
        // (agar tum command me dry-run add karna chaho, abhi ignore)
        // $dry = (bool)$request->boolean('dry_run');

        // ✅ Run artisan command
        Artisan::call('app:send-birthday-wishes');

        return response()->json([
            'ok' => true,
            'message' => 'Birthday wish command executed',
            'output' => Artisan::output(),
        ]);
    }
}
