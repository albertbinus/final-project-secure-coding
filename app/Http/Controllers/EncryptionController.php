<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EncryptionController extends Controller
{
    public function encryptData(Request $request)
    {
        // Kunci lemah untuk AES-256-CBC
        $strongKey = env('AES_KEY');  // Kunci lemah
        $iv = env('AES_IV');  // IV yang lemah (16 bytes)

        // Data yang akan dienkripsi
        $data = $request->input('data');

        // Enkripsi menggunakan AES-256-CBC dengan kunci lemah
        $cipher = openssl_encrypt($data, 'AES-256-CBC', $strongKey, 0, $iv);

        return response()->json([
            'encrypted_data' => $cipher
        ]);
    }
}
