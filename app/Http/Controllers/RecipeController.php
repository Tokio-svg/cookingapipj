<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Material;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Recipe::all();
        return response()->json([
            'data' => $items
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
        // ここでrecipe,material,proseccにrequestの内容を振り分ける
        // Recipeレコード
        $data['name'] = $request->recipe_name;
        $data['category'] = $request->category;
        $data['user_id'] = $request->userId;
        $data['img_path'] = $request->image_path;

        // 画像ファイル処理(廃止)
        // if ($request->image) {
        //     $file_name = time() . '.' . $request->image->getClientOriginalName();
        //     $request->image->storeAs('public', $file_name);

        //     $data['img_path'] = 'storage/' . $file_name;
        // }

        $item = Recipe::create($data);
        // 格納したレシピのID番号
        $id = $item->id;

        // Materialレコード
        // ここで$request->materialsに格納されているJSON文字列を配列にデコードする
        $temp = json_decode($request->materials, true);
        foreach ($temp as $material) {
            $material_data['name'] = $material["material"];
            $material_data['quantity'] = $material["quantity"];
            $material_data['recipe_id'] = $id;
            Material::create($material_data);
        }

        // Processレコード
        // ここで$request->processesに格納されているJSON文字列を配列にデコードする
        $temp = json_decode($request->processes, true);
        foreach ($temp as $process) {
            $process_data['content'] = $process;
            $process_data['recipe_id'] = $id;
            Process::create($process_data);
        }

        return response()->json([
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function show(Recipe $recipe)
    {
        $item = Recipe::with('materials')->with('processes')->with('user')->find($recipe->id);
        // 画像ファイルのパスを取得
        $img_name = $item->img_path;
        if ($img_name === 'no_image.png') {
            $apiResponse = '/img/no_image.png';
        } else {
            // curlで画像apiから画像を呼び出し
            $curl = curl_init();
            // curl_setopt($curl, CURLOPT_URL, 'http://localhost/xfree/catch.php?file=' . $img_name);
            // XFREEのURL
            curl_setopt($curl, CURLOPT_URL, 'http://h2iuu2ea.php.xdomain.jp/catch.php?file=' . $img_name);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す
            $apiResponse = curl_exec($curl);  //レスポンス（base64型データor'not_exist'）
            if ($apiResponse === 'not_exist') {
                $apiResponse = '/img/no_image.png';
            }
        }

        // レスポンスを$itemに格納
        $item['img_data'] = $apiResponse;

        if ($item) {
            return response()->json([
                'data' => $item,
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
        $update = [
            'message' => $request->message,
            'url' => $request->url
        ];
        $item = Recipe::where('id', $recipe->id)->update($update);
        if ($item) {
            return response()->json([
                'message' => 'Updated successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recipe  $recipe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recipe $recipe)
    {
        // 関連するmaterial,processも同時に消去
        $item = Recipe::where('id', $recipe->id)->delete();
        $material = Material::where('recipe_id', $recipe->id)->delete();
        $process = Process::where('recipe_id', $recipe->id)->delete();
        if ($recipe->img_path != 'test') {
            \File::delete($recipe->img_path);
        }
        if ($item) {
            return response()->json([
                'message' => 'Deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }
    }
}
