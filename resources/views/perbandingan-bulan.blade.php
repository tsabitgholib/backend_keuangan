@extends('layout')
@section('content')
<h2>Perbandingan Bulan</h2>
<form method="GET" action="http://103.23.103.43/AkuntansiKeuangan/backend_keuangan/perbandingan-bulan" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode1_id" placeholder="Periode 1 ID" value="{{ $periode1 }}" required></div>
        <div class="col"><input type="number" class="form-control" name="periode2_id" placeholder="Periode 2 ID" value="{{ $periode2 }}" required></div>
        <div class="col"><input type="number" class="form-control" name="level" placeholder="Level COA (opsional)" value="{{ $level }}"></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
@if($data)
    <div class="row">
        <div class="col">
            <h4>Periode 1</h4>
            <table class="table table-bordered">
                <thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo</th></tr></thead>
                <tbody>
                @foreach($data['periode1'] as $a)
                    <tr><td>{{ $a->account_code ?? $a['account_code'] }}</td><td>{{ $a->account_name ?? $a['account_name'] }}</td><td>{{ $a->saldo ?? $a['saldo'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="col">
            <h4>Periode 2</h4>
            <table class="table table-bordered">
                <thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo</th></tr></thead>
                <tbody>
                @foreach($data['periode2'] as $a)
                    <tr><td>{{ $a->account_code ?? $a['account_code'] }}</td><td>{{ $a->account_name ?? $a['account_name'] }}</td><td>{{ $a->saldo ?? $a['saldo'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection 