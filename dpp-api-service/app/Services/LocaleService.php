<?php

namespace App\Services;

use App\Models\Locale;
use Illuminate\Support\Facades\Log;
use Exception;

class LocaleService
{
    
    public function getLocales(): array 
    {
        try {
            // 根據 Model 定義，僅撈取啟用資料並依 ID 升序排列
            $locales = Locale::where('isEnable', true)
                ->orderBy('id', 'asc')
                ->get();

            return [
                'success' => true,
                'data'    => $locales
            ];

        } catch (Exception $e) {
            // 確保 Log 資訊準確
            Log::error('LocaleService getLocales error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => '無法取得語系資料'
            ];
        }
    }
    
}