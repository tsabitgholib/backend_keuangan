@extends('layout')
@section('content')
<h2>Buku Besar</h2>
<form method="GET" action="/buku-besar" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="akun_id" placeholder="Akun ID" value="{{ $akunId }}" required></div>
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" value="{{ $periodeId }}" required></div>
        <div class="col"><input type="date" class="form-control" name="start_date" value="{{ $start }}" required></div>
        <div class="col"><input type="date" class="form-control" name="end_date" value="{{ $end }}" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
@if($data)
    <p>Saldo Awal: <b>{{ $data['saldo_awal'] }}</b></p>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo Berjalan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['jurnals'] as $j)
                <tr>
                    <td>{{ $j->jurnal_tanggal }}</td>
                    <td>{{ $j->keterangan }}</td>
                    <td>{{ $j->debit }}</td>
                    <td>{{ $j->kredit }}</td>
                    <td>{{ $j->saldo_berjalan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Saldo Akhir: <b>{{ $data['saldo_akhir'] }}</b></p>
@endif
@endsection 