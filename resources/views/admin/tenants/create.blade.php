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

    <form method="POST" action="{{ route('admin.tenants.store') }}" class="grid max-w-3xl gap-6 md:grid-cols-2">
        @csrf

        <fieldset class="space-y-4">
            <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Identitas usaha</legend>

            <div>
                <label class="block text-sm font-medium" for="display_name">Nama tenant</label>
                <input class="w-full border rounded p-2" id="display_name" name="display_name" value="{{ old('display_name') }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium" for="code">Kode (unik per kantin)</label>
                <input class="w-full border rounded p-2" id="code" name="code" value="{{ old('code') }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium" for="slug">Slug</label>
                <input class="w-full border rounded p-2" id="slug" name="slug" value="{{ old('slug') }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium" for="commission_rate">Komisi awal (%)</label>
                <input class="w-full border rounded p-2" id="commission_rate" name="commission_rate" type="number" step="0.01" min="0" max="100" value="{{ old('commission_rate', 10) }}" required>
            </div>
        </fieldset>

        <div class="space-y-6">
            <fieldset class="space-y-4">
                <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Penanggung jawab</legend>

                <div>
                    <label class="block text-sm font-medium" for="pic_name">Nama</label>
                    <input class="w-full border rounded p-2" id="pic_name" name="pic_name" value="{{ old('pic_name') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium" for="pic_email">Surel (menerima tautan atur kata sandi)</label>
                    <input class="w-full border rounded p-2" id="pic_email" name="pic_email" type="email" value="{{ old('pic_email') }}" required>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Rekening bank</legend>

                <div>
                    <label class="block text-sm font-medium" for="bank_name">Bank</label>
                    <input class="w-full border rounded p-2" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium" for="account_holder_name">Nama pemilik rekening</label>
                    <input class="w-full border rounded p-2" id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium" for="account_number">Nomor rekening</label>
                    <input class="w-full border rounded p-2" id="account_number" name="account_number" inputmode="numeric" required>
                </div>
            </fieldset>
        </div>

        <div class="md:col-span-2">
            <button class="px-4 py-2 bg-gray-900 text-white rounded" type="submit">Simpan &amp; kirim kredensial awal</button>
            <a class="ml-2 text-gray-600 underline" href="{{ route('admin.tenants.index') }}">Batal</a>
        </div>
    </form>
@endsection