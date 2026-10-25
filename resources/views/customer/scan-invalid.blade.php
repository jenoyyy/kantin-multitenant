@extends('layouts.customer')

@section('title', 'QR Code tidak valid')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="rounded-xl border border-red-700 bg-red-950/40 p-4" data-test="scan-invalid">
            <h2 class="text-lg font-bold">QR Code tidak dapat digunakan</h2>
            <p class="mt-1 text-sm text-gray-300">QR Code ini tidak valid atau sudah tidak berlaku. Silakan hubungi petugas kantin untuk mendapatkan QR meja yang aktif.</p>
        </div>
    </div>
@endsection