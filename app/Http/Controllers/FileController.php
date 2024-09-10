<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

    public function handleFile(Request $request, $fileId = null) {
        $request->validate([
            'file' => 'required|file|max:8000',
            'filename' => 'nullable|string|max:110'
        ]);

        $userId = $request->session()->get('guest_id');
        $hashedUserId = hash('crc32', $userId);

        $file = $request->file('file');
        $fileExtension = $file->getClientOriginalExtension();

        $filename = $request->input('filename');
        if ($filename) {
            if (!str_contains($filename, '.')) {
                $filename .= '.' . $fileExtension;
            }
        } else {
            $filename = $file->getClientOriginalName();
        }

        if ($fileId) {
            // обновление уже созданного файла
            $existingFile = File::where('user_id', $userId)->where('id', $fileId)->first();
            if (!$existingFile) return response()->json(['message' => 'File not found or you do not have permission to edit this file'], 404);

            if (Storage::exists($existingFile->path)) {
                Storage::delete($existingFile->path);
            }

            $path = $file->storeAs('public/uploads/' . $hashedUserId, Str::random(26).'.'.$fileExtension);

            $existingFile->update([
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
            ]);
        } else {
            // загрузка нового файла
            $path = $file->storeAs('public/uploads/' . $hashedUserId, Str::random(26).'.'.$fileExtension);

            File::create([
                'user_id' => $userId,
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function getUserFiles(Request $request)
    {
        $userId = $request->session()->get('guest_id');
        $perPage = 50;
        $search = $request->query('search', '');

        $query = File::where('user_id', $userId);

        if ($search) {
            $query->where('filename', 'like', "%{$search}%");
        }

        $files = $query->orderBy('id', 'DESC')->paginate($perPage);

        return response()->json([
            'userId' => $userId,
            'files' => $files->items(),
            'currentPage' => $files->currentPage(),
            'lastPage' => $files->lastPage(),
            'totalFiles' => $files->total(),
        ]);
    }

    public function removeUserFile(Request $request) {
        $userId = $request->session()->get('guest_id');
        $fileId = $request->input('file_id');

        $file = File::where('user_id', $userId)->where('id', $fileId)->first();

        if (!$file) return response()->json(['message' => 'File not found or you do not have permission to delete this file'], 404);

        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}
