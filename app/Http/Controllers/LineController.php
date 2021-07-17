<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Exception;
use Illuminate\Support\Facades\Log;

class LineController extends Controller
{
    public function test(Request $request)
    {
        return response()->json([
            'message' => 'Test successfully',
        ], 200);
    }

    public function webhook(Request $request)
    {
        $lineAccessToken = env('LINE_ACCESS_TOKEN', "");
        $lineChannelSecret = env('LINE_CHANNEL_SECRET', "");

        // 署名のチェック
        $signature = $_SERVER['HTTP_' . LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
        if (!LINEBot\SignatureValidator::validateSignature($request->getContent(), $lineChannelSecret, $signature)) {
            // TODO 不正アクセス
            Log::debug('error signature');
            abort(400);
        }

        $httpClient = new CurlHTTPClient($lineAccessToken);
        $lineBot = new LINEBot($httpClient, ['channelSecret' => $lineChannelSecret]);

        try {
            // イベント取得
            $events = $lineBot->parseEventRequest($request->getContent(), $signature);

            foreach ($events as $event) {
                // ログファイルの設定
                // $file = __DIR__ . "/log.txt"
                // file_put_contents($file, print_r($event, true) . PHP_EOL, FILE_APPEND);

                // 入力した文字取得
                $message = $event->getText();
                $replyToken = $event->getReplyToken();
                $textMessage = new TextMessageBuilder($message);
                $lineBot->replyMessage($replyToken, $textMessage);
                Log::debug('set replyToken OK');
            }
        } catch (Exception $e) {
            // TODO 例外
            Log::debug('error exception');
            abort(400);
        }
        return;
    }
}
