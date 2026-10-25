@extends('layouts.admin')

@section('title', 'QR Meja '.$table->code)

@section('content')
    <h2 class="text-xl font-semibold">QR — {{ $table->label }} ({{ $table->code }})</h2>

    @if ($url)
        <p class="mt-4 p-3 bg-amber-100 text-amber-900 rounded text-sm">
            Token QR baru ditampilkan <strong>satu kali</strong>. Cetak/simpan URL ini sekarang —
            token mentah tidak dapat ditampilkan lagi.
        </p>
        <div class="mt-4 border rounded p-4">
            <p class="text-sm text-gray-500">URL untuk QR (arahkan generator QR ke URL ini):</p>
            <p class="mt-1 break-all font-mono text-sm">{{ $url }}</p>
        </div>
        <p class="mt-3 text-xs text-gray-500">Gambar QR siap cetak ditambahkan di Tahap 8. Keamanan token tidak bergantung pada gambar.</p>
    @else
        <p class="mt-4 p-3 bg-gray-100 rounded text-gray-600">
            Tidak ada token baru untuk ditampilkan. Kembali ke daftar meja dan klik Rotate/Terbitkan QR.
        </p>
    @endif

    <div class="mt-6">
        <a class="text-sm font-medium underline" href="{{ route('admin.tables.index') }}">← Kembali ke daftar meja</a>
    </div>
@endsection