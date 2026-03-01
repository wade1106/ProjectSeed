<?php

namespace App\Utils;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;

class MailUtil
{
    /**
     * 安全地讀取環境變數值 (針對Linux環境優化)
     *
     * @param string $key 環境變數鍵名
     * @param string $default 預設值
     * @return string 環境變數值或預設值
     */
    public static function getEnvValue($key, $default = '')
    {
        try {
            // --- 核心修改：方法 0 (優先從 Config 讀取) ---
            // 這裡的 key 必須對應到 services.php 的層級結構
            $configMapping = [
                'DPP_PLATFORM_URL'   => 'services.dpp.url',
                'APP_NAME'           => 'services.dpp.app_name',
            ];

            if (isset($configMapping[$key])) {
                $value = config($configMapping[$key]);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            // --- 以下為原本的邏輯作為 Fallback (備援) ---
            
            // 方法1: env() (開發模式沒 cache 時有效)
            $value = function_exists('env') ? env($key) : null;

            // 方法2: 讀取實體檔案 (當 cache 鎖死且 config 沒設對時的最後防線)
            if ($value === null || $value === '') {
                $envFile = base_path('.env');
                if (file_exists($envFile) && is_readable($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (strpos($line, $key . '=') === 0) {
                            $value = trim(substr($line, strlen($key) + 1), '"\' ');
                            break;
                        }
                    }
                }
            }

            return ($value !== null && $value !== '') ? $value : $default;

        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * 發送郵件公用程式
     *
     * @param array|string $to 收件人，可為單一email字串或多個email的陣列
     * @param array|string|null $cc CC對象，可為單一email字串或多個email的陣列
     * @param string $subject 郵件主旨
     * @param string $body 郵件內文
     * @param array $options 額外選項 ['from' => 'sender@example.com', 'fromName' => '發送者名稱']
     * @return array 回傳結果 ['success' => bool, 'message' => string]
     */
    public static function send($to, $cc = null, $subject, $body, $options = [])
    {
        try {
            // 處理收件人
            $recipients = self::parseRecipients($to);
            if (empty($recipients)) {
                return [
                    'success' => false,
                    'message' => '收件人不能為空'
                ];
            }

            // 處理CC對象
            $ccRecipients = self::parseRecipients($cc);

            // 設定發送者資訊 - 使用更安全的env讀取方式，加強容錯處理
            $from = isset($options['from']) && !empty($options['from']) ? $options['from'] : self::getEnvValue('MAIL_FROM_ADDRESS', 'lili.liao@digitalcreation.com.tw');
            $fromName = isset($options['fromName']) && !empty($options['fromName']) ? $options['fromName'] : self::getEnvValue('APP_NAME', 'DPP Platform');

            // 最終安全檢查 - 確保不為 null 或空值或字串 "null"
            if (empty($from) || !is_string($from) || $from === 'null') {
                $from = 'lili.liao@digitalcreation.com.tw';
            }
            if (empty($fromName) || !is_string($fromName) || $fromName === 'null') {
                $fromName = 'DPP Platform';
            }

            // 發送郵件
            Mail::raw($body, function (Message $message) use ($recipients, $ccRecipients, $subject, $from, $fromName) {
                // 設定收件人
                foreach ($recipients as $recipient) {
                    $message->to($recipient['email'], $recipient['name']);
                }

                // 設定CC
                if (!empty($ccRecipients)) {
                    foreach ($ccRecipients as $ccRecipient) {
                        $message->cc($ccRecipient['email'], $ccRecipient['name']);
                    }
                }

                // 設定主旨和發送者
                $message->subject($subject);
                $message->from($from, $fromName);
            });

            return [
                'success' => true,
                'message' => '郵件發送成功'
            ];

        } catch (\Exception $e) {
            // 記錄錯誤日誌
            Log::error('郵件發送失敗', [
                'error' => $e->getMessage(),
                'to' => $to,
                'cc' => $cc,
                'subject' => $subject
            ]);

            return [
                'success' => false,
                'message' => '郵件發送失敗: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 發送HTML格式郵件
     *
     * @param array|string $to 收件人
     * @param array|string|null $cc CC對象
     * @param string $subject 郵件主旨
     * @param string $htmlBody HTML郵件內文
     * @param array $options 額外選項
     * @return array 回傳結果
     */
    public static function sendHtml($to, $cc = null, $subject, $htmlBody, $options = [])
    {
        try {
            // 處理收件人
            $recipients = self::parseRecipients($to);
            if (empty($recipients)) {
                return [
                    'success' => false,
                    'message' => '收件人不能為空'
                ];
            }

            // 處理CC對象
            $ccRecipients = self::parseRecipients($cc);

            // 設定發送者資訊 - 使用更安全的env讀取方式，加強容錯處理
            $from = isset($options['from']) && !empty($options['from']) ? $options['from'] : self::getEnvValue('MAIL_FROM_ADDRESS', 'lili.liao@digitalcreation.com.tw');
            $fromName = isset($options['fromName']) && !empty($options['fromName']) ? $options['fromName'] : self::getEnvValue('APP_NAME', 'DPP Platform');

            // 最終安全檢查 - 確保不為 null 或空值
            if (empty($from) || !is_string($from) || $from === 'null') {
                $from = 'lili.liao@digitalcreation.com.tw';
            }
            if (empty($fromName) || !is_string($fromName)) {
                $fromName = 'DPP Platform';
            }

            // 除錯日誌
            Log::info('MailUtil::sendHtml 發送者資訊', [
                'from' => $from,
                'fromName' => $fromName,
                'options_from' => $options['from'] ?? 'null',
                'env_value' => self::getEnvValue('MAIL_FROM_ADDRESS', 'default'),
                'all_options' => $options
            ]);

            // 發送HTML郵件
            Mail::html($htmlBody, function (Message $message) use ($recipients, $ccRecipients, $subject, $from, $fromName) {
                // 設定收件人
                foreach ($recipients as $recipient) {
                    $message->to($recipient['email'], $recipient['name']);
                }

                // 設定CC
                if (!empty($ccRecipients)) {
                    foreach ($ccRecipients as $ccRecipient) {
                        $message->cc($ccRecipient['email'], $ccRecipient['name']);
                    }
                }

                // 設定主旨和發送者
                $message->subject($subject);
                $message->from($from, $fromName);
            });

            return [
                'success' => true,
                'message' => '通知郵件發送成功'
            ];

        } catch (\Exception $e) {
            // 記錄錯誤日誌
            Log::error('通知件發送失敗', [
                'error' => $e->getMessage(),
                'to' => $to,
                'cc' => $cc,
                'subject' => $subject
            ]);

            return [
                'success' => false,
                'message' => '通知郵件發送失敗: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 解析收件人格式
     *
     * @param mixed $recipients
     * @return array
     */
    private static function parseRecipients($recipients)
    {
        $result = [];

        if (empty($recipients)) {
            return $result;
        }

        // 如果是字串，轉換為陣列
        if (is_string($recipients)) {
            $recipients = [$recipients];
        }

        // 處理每個收件人
        foreach ($recipients as $key => $value) {
            if (is_string($value)) {
                // 只有email地址
                $result[] = [
                    'email' => trim($value),
                    'name' => ''
                ];
            } elseif (is_array($value)) {
                // 包含名稱和email的陣列
                $result[] = [
                    'email' => trim($value['email'] ?? ''),
                    'name' => trim($value['name'] ?? '')
                ];
            } elseif (is_object($value)) {
                // 物件格式
                $result[] = [
                    'email' => trim($value->email ?? ''),
                    'name' => trim($value->name ?? '')
                ];
            }
        }

        return $result;
    }

    /**
     * 驗證email地址格式
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 批次發送郵件
     *
     * @param array $mails 多個郵件設定
     * @return array 批次發送結果
     */
    public static function sendBatch(array $mails)
    {
        $results = [];

        foreach ($mails as $index => $mail) {
            $to = $mail['to'] ?? null;
            $cc = $mail['cc'] ?? null;
            $subject = $mail['subject'] ?? '';
            $body = $mail['body'] ?? '';
            $options = $mail['options'] ?? [];
            $isHtml = $mail['isHtml'] ?? false;

            if ($isHtml) {
                $results[$index] = self::sendHtml($to, $cc, $subject, $body, $options);
            } else {
                $results[$index] = self::send($to, $cc, $subject, $body, $options);
            }
        }

        return $results;
    }

    /**
     * 取代郵件內文中的佔位符(DPP系統內開帳號用)
     *
     * @param string $code 公司代碼
     * @param string $account 帳號
     * @param string $password 密碼
     * @return string 準備好的HTML郵件內容
     */
    public static function prepareActivationEmail(string $code, string $account, string $password): string
    {
        // === 獲取 DPP 平台基礎網址 ===
        $rawUrl = self::getEnvValue('DPP_PLATFORM_URL', 'dpp正式網址');
        $baseUrl = rtrim($rawUrl, '/') . '/'; 
        
        // === 1. 生成變更密碼連結 (符合前端 Vue Router 路徑) ===
        $changePasswordUrl = $baseUrl . '#/account/change-password?' . http_build_query([
            'code' => $code,
            'user' => $account
        ]);

        // === 2. 登入網址 ===
        $loginUrl = $baseUrl;

        $linkName = 'Digital Product Passport';
        $subName = '數位產品護照管理平台';
        
        // 使用與 Vue 元件一致的顏色：Indigo 600 (#4f46e5) 與 Slate 家族
        $htmlContent = '
        <div style="background-color: #f8fafc; padding: 40px 20px; font-family: \'Inter\', \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">
            <div style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                
                <div style="padding: 40px 40px 30px 40px; text-align: center;">
                    <h1 style="margin: 0; color: #1e293b; font-size: 24px; font-weight: 700; letter-spacing: -0.025em;">[連結名稱]</h1>
                    <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px; font-weight: 500;">' . $subName . '</p>
                </div>

                <div style="padding: 0 40px 40px 40px;">
                    <div style="color: #334155; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                        您好：<br>
                        您的專屬帳號已建立完成。為了確保帳戶安全，請點擊下方按鈕進行<strong>首次登入並變更密碼</strong>。
                    </div>

                    <div style="text-align: center; margin-bottom: 35px;">
                        <a href="[變更密碼連結]" style="background-color: #4f46e5; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: 700; border-radius: 8px; display: inline-block; font-size: 16px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">立即設定密碼並啟用</a>
                    </div>

                    <div style="background-color: #f1f5f9; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #cbd5e1; padding-bottom: 10px;">帳號資訊 / Account Info</h3>
                        
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td style="padding: 8px 0; color: #64748b; width: 85px; font-weight: 600;">登入網址</td>
                                <td style="padding: 8px 0;"><a href="[登入網址]" style="color: #4f46e5; text-decoration: none; font-weight: 500;">[登入網址]</a></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #64748b; font-weight: 600;">公司代碼</td>
                                <td style="padding: 8px 0; color: #1e293b; font-family: monospace; font-size: 15px;"><strong>[公司代碼]</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #64748b; font-weight: 600;">登入帳號</td>
                                <td style="padding: 8px 0; color: #1e293b; font-family: monospace; font-size: 15px;"><strong>[使用者帳號]</strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #64748b; font-weight: 600;">初始密碼</td>
                                <td style="padding: 8px 0; color: #1e293b; font-family: monospace; font-size: 15px;"><span style="background-color: #ffffff; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1;">[系統產生的密碼]</span></td>
                            </tr>
                        </table>
                    </div>

                    <div style="border-left: 4px solid #f59e0b; background-color: #fffbeb; padding: 15px; border-radius: 6px; color: #92400e; font-size: 13px;">
                        <strong>安全提示：</strong> 進入變更密碼頁面後，請先輸入上述「初始密碼」作為目前密碼，再設定您個人的新密碼。
                    </div>

                    <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid #e2e8f0; text-align: center;">
                        <p style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">股份有限公司</p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #94a3b8;">Vital Collaboration Co., Ltd. Support Team</p>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 25px; color: #94a3b8; font-size: 12px;">
                此郵件為系統自動發送，請勿直接回覆。<br>
                如有疑問請聯繫 <a href="mailto:service@carbontact.com" style="color: #64748b; text-decoration: underline;">service@carbontact.com</a>
            </div>
        </div>';

        $placeholders = [
            '[連結名稱]',
            '[變更密碼連結]',
            '[登入網址]',
            '[公司代碼]',
            '[使用者帳號]',
            '[系統產生的密碼]'
        ];

        $replacements = [
            htmlspecialchars($linkName), 
            htmlspecialchars($changePasswordUrl), 
            htmlspecialchars($loginUrl), 
            htmlspecialchars($code),
            htmlspecialchars($account),
            htmlspecialchars($password)
        ];

        return str_replace($placeholders, $replacements, $htmlContent);
    }
    
}