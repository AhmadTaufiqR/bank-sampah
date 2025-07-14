<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function getNewsList()
    {
        $news = News::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id_news,
                    'source' => $item->source,
                    'title' => $item->title,
                    'content' => $item->content,
                    'photo' => $item->photo,
                ];
            })
        ]);
    }

    public function getNewsDetail($id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'status' => 'error',
                'message' => 'News not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $news->id_news,
                'source' => $news->source,
                'title' => $news->title,
                'content' => $news->content,
                'photo' => $news->photo,
                'date' => $news->date,
                'id_admin' => $news->id_admin,
            ]
        ]);
    }

    // public function createNews(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'source' => 'required|string|max:255',
    //         'title' => 'required|string|max:255',
    //         'content' => 'required|string',
    //         'photo' => 'required|string|max:255',
    //         'date' => 'required|date',
    //         'id_admin' => 'required|integer|exists:admins,id_admin',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors()
    //         ], 400);
    //     }

    //     $news = News::create($request->all());

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => [
    //             'id' => $news->id_news,
    //             'source' => $news->source,
    //             'title' => $news->title,
    //             'content' => $news->content,
    //             'photo' => $news->photo,
    //             'id_admin' => $news->id_admin,
    //         ]
    //     ], 201);
    // }

    public function createNews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_admin' => 'required|integer|exists:admins,id_admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Simpan foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('news', 'public');
        }

        $newsResponse = News::create([
            'source' => $request->source,
            'title' => $request->title,
            'content' => $request->content,
            'photo' => $photoPath,
            'date' => Carbon::now(), // ← Otomatis pakai tanggal sekarang
            'id_admin' => $request->id_admin,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $newsResponse->id_news,
                'source' => $newsResponse->source,
                'title' => $newsResponse->title,
                'content' => $newsResponse->content,
                'photo' => $newsResponse->photo,
                'date' => $newsResponse->date,
                'id_admin' => $newsResponse->id_admin,
            ]
        ], 201);
    }

    public function updateNews(Request $request, $id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'status' => 'error',
                'message' => 'News not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'source' => 'string|max:255',
            'title' => 'string|max:255',
            'content' => 'string',
            'photo' => 'string|max:255',
            'date' => 'date',
            'id_admin' => 'integer|exists:admins,id_admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $news->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $news->id_news,
                'source' => $news->source,
                'title' => $news->title,
                'content' => $news->content,
                'photo' => $news->photo,
                'id_admin' => $news->id_admin,
            ]
        ]);
    }

    public function deleteNews($id)
    {
        $news = News::find($id);

        if (!$news) {
            return response()->json([
                'status' => 'error',
                'message' => 'News not found'
            ], 404);
        }

        $news->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'News deleted successfully'
        ]);
    }
}
