<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($category)
    {
        // ページネート
        $items = Recipe::where('category', $category)->paginate(4);
        foreach ($items as $item) {
            // 画像ファイルのパスを取得
            $img_name = $item->img_path;
            if ($img_name === 'no_image.png') {
                $apiResponse = '/img/no_image.png';
            } else {
                // curlで画像apiから画像を呼び出し
                $curl = curl_init();
                // ローカル
                curl_setopt($curl, CURLOPT_URL, 'http://localhost/xfree/catch.php?file=' . $img_name);
                // XFREEのURL
                // curl_setopt($curl, CURLOPT_URL, 'http://h2iuu2ea.php.xdomain.jp/catch.php?file=' . $img_name);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す
                $apiResponse = curl_exec($curl);  //レスポンス（base64型データor'not_exist'）
                if ($apiResponse === 'not_exists') {
                    $apiResponse = '/img/no_image.png';
                } else {
                    $apiResponse = 'data:image/png;base64,' . $apiResponse;
                }
            }
            // レスポンスを$itemに格納
            $item['img_data'] = $apiResponse;
        }
        return response()->json([
            'data' => $items,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Recipe $recipe)
    {
        $item = $request;
        if ($item) {
            return response()->json([
                'data' => $item
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Recipe $recipe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipe $recipe)
    {
        //
    }
}
