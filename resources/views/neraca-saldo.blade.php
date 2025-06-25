@extends('layout')
@section('content')
<h2>Neraca Saldo</h2>
<form method="GET" action="http://103.23.103.43/AkuntansiKeuangan/backend_keuangan/neraca-saldo" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" value="{{ $periode }}" required></div>
        <div class="col"><input type="number" class="form-control" name="level" placeholder="Level COA (opsional)" value="{{ $level }}"></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
@if($data)
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Saldo Awal</th>
                <th>Total Debit</th>
                <th>Total Kredit</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['account_code'] }}</td>
                    <td>{{ $row['account_name'] }}</td>
                    <td>{{ $row['saldo_awal'] }}</td>
                    <td>{{ $row['total_debit'] }}</td>
                    <td>{{ $row['total_kredit'] }}</td>
                    <td>{{ $row['saldo_akhir'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection 