@extends('adminlte::page')

@section('title', 'MOZAIC Minimarket')
@section('js')
    <script>
        var data = {!! json_encode(session('msg')) !!}
        @if (session('error')) 
        alert(session('error'));
        @endif
        if (data == 'Tutup Kasir Berhasil') {
            var mywindow = window.open("{{ route('print-close-cashier-configuration') }}", '_blank');
            mywindow.print();
        } else if (data != null) {
            alert(data);
        }

        function hideButton() {
            $('#upload_data').addClass('disabled');
            $('#close_cashier_temp').addClass('disabled');
            $('#download_data').addClass('disabled');
            $('#close_cashier').addClass('disabled');
            $('#backup_data').addClass('disabled');
            $('#reupload').addClass('disabled');
            $('#reprint').addClass('disabled');
            hideInput();
            console.log('Buttons disabled');
        }

        function showButton() {
            $('#upload_data').removeClass('disabled');
            $('#close_cashier_temp').removeClass('disabled');
            $('#download_data').removeClass('disabled');
            $('#close_cashier').removeClass('disabled');
            $('#backup_data').removeClass('disabled');
            $('#reupload').removeClass('disabled');
            $('#reprint').removeClass('disabled');
            showInput();
            console.log('Buttons enable');
        }

        function hideInput() {
            $("input").attr('disabled', '');
        }

        function showInput() {
            $("input").removeAttr('disabled');
        }

        function buttonClick(name) {
            if (name == "download_data") {
                hideButton();
                $('#download_data').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Checking ...'
                    );
                $.ajax({
                    type: "GET",
                    url: "{{ route('check-data-configuration') }}",
                    success: function(data) {
                        if (data != '[null]') {
                            $('#modal').modal('show');
                            $('#download_data').html('<i class="fa fa-download"></i> Download Data');
                            showButton();
                        } else {
                            $('#download_data').html(
                                '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                            );
                            hideButton();
                            window.location.replace("{{ route('configuration-data-dwonload') }}");
                        }
                    }
                });

            } else if (name == "close_cashier_temp") {

                hideButton();
                $('#close_cashier_temp').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                    );

                var mywindow = window.open('{{ route('close_cashier_tmp') }}', '_blank');

                mywindow.print();
                location.reload();


            } else if (name == "upload_data") {

                hideButton()
                $('#upload_data').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                    );
                window.location.replace("{{ route('configuration-data-upload') }}");

            } else if (name == "close_cashier") {
                hideButton();
                $('#close_cashier').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Checking ...'
                    );
                $.ajax({
                    type: "GET",
                    url: "{{ route('check-close-cashier-configuration') }}",
                    success: function(data) {
                        console.log(data);
                        if (data.status == 0) {
                            $('#modalCloseCashierLabel').text('Tutup Kasir Shift 1');
                            $('#modal-body').text(' Apakah anda yakin ingin menutup kasir?');
                            $('#modalCloseCashier').modal('show');
                            $('#close_cashier').html('<i class="fa fa-archive"></i> Tutup Kasir');
                            showButton();
                        } else if (data.status == 1) {
                            $('#modalCloseCashierLabel').text('Tutup Kasir Shift 2');
                            $('#modal-body').text(' Apakah anda yakin ingin menutup kasir?');
                            $('#modalCloseCashier').modal('show');
                            $('#close_cashier').html('<i class="fa fa-archive"></i> Tutup Kasir');
                            showButton();
                        } else if (data.status == 3) {
                            $('#modal-body-single').text('Shift 1 Sudah Ditutup, Shift 2 Masih Berlangsung Panjang');
                            $('#modalCloseCashier1').modal('show');
                            $('#close_cashier').html('<i class="fa fa-archive"></i> Tutup Kasir');
                            showButton();
                        } else {
                            $('#modalCloseCashier1').modal('show');
                            $('#modal-body-single').text('Anda sudah Tutup Kasir 2 kali !');
                            $('#close_cashier').html('<i class="fa fa-archive"></i> Tutup Kasir');
                            showButton();
                        }
                    }
                });

            } else if (name == "backup_data") {

                hideButton();
                $('#backup_data').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                    );
                window.location.replace("{{ route('backup-data-configuration') }}");

            } else if (name == "isTrueDownload") {
                $('#modal').modal('toggle');
                hideButton();
                $('#download_data').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                    );
                window.location.replace("{{ route('configuration-data-dwonload') }}");

            } else if (name == "isTrueCloseCashier") {

                $('#modalCloseCashier').modal('toggle');
                hideButton();
                $('#close_cashier').html(
                    '<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...'
                    );
                window.location.replace("{{ route('close-cashier-configuration') }}");
            } else if (name == "reupload") {
                console.log('0:click reupload');
                hideButton();
                var start_date = $("#start_date_reupload").val();
                var end_date = $("#end_date_reupload").val();
                $('#reupload').html('<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Checking ...');
                $.ajax({
                    type: "get",
                    url: "{{ route('check-data-reupload') }}",
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("start_date", start_date);
                        xhr.setRequestHeader("end_date", end_date);
                    },
                    success: function(data) {
                        console.log('Check Response:')
                        console.log(data); 
                        if (data.status == 0) {
                            $('#modalCloseCashier1').modal('show');
                            $('#modalCloseCashierLabel1').text('Upload Ulang');
                            $('#modal-body-single').text('Upload Ulang Gagal');
                            $('#reupload').html('Upload');
                            showButton();
                        } else  if (data.status == 1 ) {
                            $('#modalReupload').modal('show');
                            $('#reupload-modal-body').text('Tidak Ada Data Untuk Diupload')
                            $('#modalReuploadTitle').text('Upload Ulang')
                            $('#reupload').html('Upload');
                            showButton();
                        } else  if (data.status == 2 ) {
                            $('#modalReupload').modal('show');
                            $('#reupload-modal-body').text('Data sudah diupload ke server')
                            $('#modalReuploadTitle').text('Data Sudah Diupload')
                            $('#reupload').html('Upload');
                            showButton();
                        } else {
                            $('#reupload').html('<span class=\'spinner-grow spinner-grow-sm mb-1\' role=\'status\' aria-hidden=\'true\'></span> Proses ...');
                            hideButton();
                            console.log('Date range:');
                            console.log([start_date,end_date]);
                            $.ajax({
                    type: "get",
                    url: "{{ route('reupload') }}",
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("start_date", start_date);
                        xhr.setRequestHeader("end_date", end_date);
                    },
                    success: function(data) {
                        console.log('Reupload response:');
                        console.log(data);
                        if (data.status == 0) {
                            $('#modalCloseCashier1').modal('show');
                            $('#modalCloseCashierLabel1').text('Upload Ulang');
                            $('#modal-body-single').text('Upload Ulang Gagal');
                            $('#reupload').html('Upload');
                            showButton();
                        } else  if (data.status == 1 ) {
                            $('#modalReupload').modal('show');
                            $('#reupload-modal-body').text('Data sudah diupload ke server')
                            $('#modalReuploadTitle').text('Data Sudah Diupload')
                            $('#reupload').html('Upload');
                            showButton();
                        }
                    }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        // Log the error to the console
                        console.error("The following error occurred: " + textStatus, errorThrown
                        );
                    }); 
               
                        }
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    // Log the error to the console
                    console.error("The following error occurred: " + textStatus, errorThrown
                    );
                });


            }

        }
        function getShift(date) {
            $.ajax({
                type: "get",
                url: "{{route('get-shift')}}",
                data: {
                    'date' : date
                },
                dataType: "html",
                success: function (response) {
                    console.log(response);
                    $('#shift').html(response);
                }
            });
         }
         $(document).ready(function () {
            getShift($('#date').val());
         });
    </script>
@endsection
@section('content_header')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Konfigurasi Data</li>
        </ol>
    </nav>

@stop

@section('content')

    <h3 class="page-title">
        <b>Konfigurasi Data</b>
    </h3>
    <br />
    @if (session('msg'))
        <div class="alert alert-info" role="alert">
            {{ session('msg') }}
        </div>
    @endif


    <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="staticBackdropLabel">Data Stok Ada yang Berbeda</h5>
                </div>
                <div class="modal-body">
                    Apakah anda ingin mengganti data yang sudah ada?
                </div>
                <div class="modal-footer">
                    <button onclick="hideButton();buttonClick('isTrueDownload')" class="btn btn-success">Iya</button>
                    <button onclick="showButton();" type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCloseCashier" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="modalCloseCashierLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="modalCloseCashierLabel">Tutup Kasir</h5>
                </div>
                <div class="modal-body" id="modal-body">
                    Apakah anda yakin ingin menutup kasir?
                </div>
                <div class="modal-footer">
                    <button onclick="this.disabled=true;hideButton();buttonClick('isTrueCloseCashier')" class="btn btn-success">Iya</button>
                    <button onclick="showButton();" type="button" class="btn btn-danger" data-bs-dismiss="modal">Tidak</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCloseCashier1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="modalCloseCashierLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="modalCloseCashierLabel1">Tutup Kasir Gagal</h5>
                </div>
                <div class="modal-body" id="modal-body-single">
                    Anda sudah Tutup Kasir 2 kali !
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="showButton();" class="btn btn-danger" data-bs-dismiss="modal">Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReupload" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="modalCloseCashierLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="modalReuploadTitle">Data Sudah Diupload</h5>
                </div>
                <div class="modal-body" id="reupload-modal-body">
                    Data sudah diupload ke server
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="showButton();" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <div style="display: flex; justify-content: center; align-items: center; height: 150px; ">

        {{-- <button onclick="hideButton();buttonClick('close_cashier_temp');" id="close_cashier_temp" class="btn btn-success btn-lg mx-2">
            <i class="fa fa-archive"></i> Tutup kasir 03-07-2023 shift 2</button> --}}
        <button onclick="hideButton();buttonClick('download_data');" id="download_data" class="btn btn-success btn-lg mx-2">
            <i class="fa fa-download"></i> Download Data</button>
        <button onclick="hideButton();buttonClick('upload_data');" id="upload_data" class="btn btn-success btn-lg mx-2">
            <i class="fa fa-upload"></i> Upload Data</button>
        <button onclick="hideButton();buttonClick('close_cashier');" id="close_cashier" class="btn btn-success btn-lg mx-2">
            <i class="fa fa-archive"></i> Tutup Kasir</button>
        <button onclick="hideButton();buttonClick('backup_data');" id="backup_data" class="btn btn-success btn-lg mx-2">
            <i class="fa fa-cloud"></i> Candangkan Data</button>
    </div>
    @if (Auth::id() == 55)
        <div class="row">
            <div class="col-md-12">
                <div class="card border border-gray">
                    <div class="card-header border-dark bg-dark">
                        <h5 class="mb-0 float-left">
                            Upload Ulang
                        </h5>
                    </div>
                    <div class="mx-3">
                        <form method="post" class="reupload" id="form-reupload" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row form-group">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <a class="text-dark">Tanggal Awal<a class='red'> *</a></a>
                                            <input style="width: 50%" class="form-control input-bb" name="start_date"
                                                id="start_date_reupload" type="date" max="{{date('Y-m-d')}}" autocomplete="off"
                                                value="{{ $start_date }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <a class="text-dark">Tanggal Akhir<a class='red'> *</a></a>
                                            <input style="width: 50%" class="form-control input-bb" name="end_date"
                                                id="end_date_reupload" type="date" max="{{date('Y-m-d')}}" autocomplete="off"
                                                value="{{ $end_date }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <div class="form-actions float-right">
                                    <button type="button" id="reupload" name="Save"
                                        onclick="hideButton();buttonClick('reupload');" class="btn btn-success"
                                        title="Save">Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
        @if (1)
            <div class="row">
                <div class="col-md-12">
                    <div class="card border border-gray">
                        <div class="card-header border-dark bg-dark">
                            <h5 class="mb-0 float-left">
                                Cetak Ulang Tutup Kasir
                            </h5>
                        </div>
                        <div class="mx-3">
                            <form method="post" id="form-prevent" action="{{ route('reprint') }}" target="_blank"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row form-group">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <a class="text-dark">Tanggal<a class='red'> *</a></a>
                                                <input style="width: 50%" class="form-control input-bb" name="date"
                                                    id="date" type="date" max="{{ date('Y-m-d') }}"
                                                    autocomplete="off" value="{{ date('Y-m-d') }}" onchange="getShift(this.value)"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <a class="text-dark">Shift<a class='red'> *</a></a>
                                                <select class="selection-search-clear select-form form-control" id="shift" name="shift">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-muted">
                                    <div class="form-actions float-right">
                                        <button type="submit" id="reprint" name="Save" class="btn btn-success"
                                            title="Save">Proses</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

@stop

@section('footer')

@stop

@section('css')

@stop

@section('js')

@stop
