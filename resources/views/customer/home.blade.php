@extends('layouts.customer')

@section('title', 'Kantin ' . $canteen)

@section('content')
    <div class="max-w-md mx-auto text-center py-20">
        <h2 class="text-xl font-semibold mb-2">Kantin: {{ $canteen }}</h2>
        <p class="text-gray-400">Katalog belum tersedia. Menu publik untuk kantin ini akan tampil di sini (Modul 7).</p>
    </div>
@endsection