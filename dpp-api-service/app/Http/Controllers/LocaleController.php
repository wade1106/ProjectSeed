<?php

namespace App\Http\Controllers;

use App\Services\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocaleController extends Controller
{
    public function __construct(
        private readonly LocaleService $localeService
    ) {}

    /**
     * 取得所有啟用中的語系清單
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLocales(Request $request): JsonResponse
    {
        try {
            $locales = $this->localeService->getLocales();

            return response()->json([
                'success' => true,
                'data'    => $locales,
                'message' => 'Locales retrieved successfully.'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            // 這裡記錄 Controller 層級的錯誤訊息
            Log::error("LocaleController@getLocales: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve locales.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}