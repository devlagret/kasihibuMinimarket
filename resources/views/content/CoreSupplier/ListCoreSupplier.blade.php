@extends('adminlte::page')

@section('title', 'MOZAIC Minimarket')

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Pemasok</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Pemasok</b> <small>Kelola Pemasok </small>
</h3>
<br/>

@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
  <div class="card-header bg-dark clearfix">
    <h5 class="mb-0 float-left">
        Daftar
    </h5>
    <div class="form-actions float-right">
        <button onclick="location.href='{{ url('/core-supplier/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Pemasok </button>
    </div>
  </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="20%" style='text-align:center'>Nama Pemasok</th>
                        <th width="20%" style='text-align:center'>Telp Pemasok</th>
                        <th width="20%" style='text-align:center'>Alamat</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($data as $row)
                    <tr>
                        <td style='text-align:center'>{{ $no++ }}.</td>
                        <td>{{ $row['supplier_name'] }}</td>
                        <td>{{ $row['supplier_phone'] }}</td>
                        <td>{{ $row['supplier_address'] }}</td>
                        <td class="text-center">
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/core-supplier/edit/'.$row['supplier_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/core-supplier/delete/'.$row['supplier_id']) }}">Hapus</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop