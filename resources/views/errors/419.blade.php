@extends('errors.layout')

@section('code', '419')
@section('icon', 'clock')
@section('title', 'Sesi sudah berakhir')
@section('message', 'Halaman terbuka terlalu lama sehingga sesinya kedaluwarsa. Muat ulang halaman lalu coba lagi.')

@section('action')
    <x-button type="button" onclick="location.reload()"><x-icon name="refresh" class="h-4 w-4" /> Muat ulang</x-button>
@endsection
