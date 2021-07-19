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
use App\Models\Recipe;
use App\Models\Material;
use App\Models\Process;

class LineController extends Controller
{
    public function test(Request $request)
    {
        $name = $request->all();
        // カテゴリー名が一致するレシピを4つ取り出して
        // タイトル、URLを文字列に含めて返す
        $items = Recipe::where('category', $name)->take(4)->get();
        $count = $items->count();
        $replyText = "";
        if ($count) {
            for ($i = 0; $i < $count; $i++) {
                if (!$items[$i]) break;
                $replyText = $replyText . "name:{$items[$i]->name}URL:{$items[$i]->id}\n";
            }
        } else $replyText = "該当するレシピはありません。";

        return response()->json([
            'data' => $replyText
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
                // 入力した文字取得
                $message = $event->getText();
                // カテゴリー名が一致するレシピを4つ取り出して
                // タイトル、URLを文字列に含めて返す
                $items = Recipe::where('category', $message)->take(4)->orderBy('id', 'desc')->get();
                $count = $items->count();
                $replyText = "{$count}件のレシピが見つかりました。\n";
                if ($count) {
                    for ($i = 0; $i < $count; $i++) {
                        if (!$items[$i]) break;
                        $replyText = $replyText . "name:{$items[$i]->name}\nURL:https://cookingpj-c1d29.web.app/postdetail/{$items[$i]->id}\n";
                    }
                } else $replyText = "該当するレシピはありません。";
                $textMessage = new TextMessageBuilder($replyText);

                $replyToken = $event->getReplyToken();
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
