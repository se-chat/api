<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use App\Utils\HashId;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function upload(Request $request): JsonResponse
    {
        $file = $request->file('file');
        $ext = $request->input('ext');
        $mime = $request->input('mime');
        $folderName = date('Y/m/d');
        $path = $file->store('files/' . $folderName, ['disk' => 'local']);
        $fileRecord = FileService::save([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'hash' => $file->hashName(),
            'ext' => $ext,
        ]);
        return $this->success([
            'id' => HashId::encode('file', $fileRecord['id']),
        ]);
    }

    /**
     * @param string $hashId
     * @return BinaryFileResponse
     */
    public function download(string $hashId): BinaryFileResponse
    {
        $id = HashId::decode('file', $hashId);
        $file = FileService::findById($id);
        if (!$file) {
            abort(404);
        }
        $path = storage_path('app/' . $file['path']);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path, $file['name'], [
            'Content-Type' => $file['mime_type'],
        ]);
    }
}
