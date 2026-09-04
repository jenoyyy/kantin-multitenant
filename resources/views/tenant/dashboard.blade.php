@extends('layouts.tenant')

@section('title', 'Dashboard Tenant ' . $tenant)

@section('content')
    <h2 class="text-xl font-semibold mb-4">Dashboard Tenant: {{ $tenant }}</h2>
    <p class="text-gray-600">Ringkasan pesanan & antrean dapur akan tampil di sini.</p>
@endsection