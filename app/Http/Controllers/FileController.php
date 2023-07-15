<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    // 上传文件
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $path = $file->store('public');

        return $path;
    }

    // 上传头像
    public function avatar(Request $request)
    {
        $file = $request->file('file');
        $path = $file->store('public');
        $user = $request->user();
        $user->avatar = $path;
        $user->save();
        return $path;
    }

}
