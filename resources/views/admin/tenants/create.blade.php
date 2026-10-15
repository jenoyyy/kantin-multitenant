@extends('layouts.admin')

@section('title', 'Tambah Tenant')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Tambah Tenant</h2>

    @if ($errors->any())
        <ul class="mb-4 p-3 bg-red-100 text-red-800 rounded list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('admin.tenants.store') }}" class="space-y-4 max-w-md">
        @csrf

        <div>
            <label class="block text-sm font-medium" for="code">Kode</label>
            <input class="w-full border rounded p-2" id="code" name="code" value="{{ old('code') }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium" for="slug">Slug</label>
            <input class="w-full border rounded p-2" id="slug" name="slug" value="{{ old('slug') }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium" for="display_name">Nama tenant</label>
            <input class="w-full border rounded p-2" id="display_name" name="display_name" value="{{ old('display_name') }}" required>
        </div>

        <div>
            <label class="block text-sm font-medium" for="commission_rate">Komisi awal (%)</label>
            <input class="w-full border rounded p-2" id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', 10) }}" required>
        </div>

        <button class="px-4 py-2 bg-gray-900 text-white rounded" type="submit">Simpan</button>
        <a class="ml-2 text-gray-600 underline" href="{{ route('admin.tenants.index') }}">Batal</a>
    </form>
@endsection