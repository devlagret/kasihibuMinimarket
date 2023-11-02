<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CloseCashierLog;
use App\Models\CoreMember;
use App\Models\InvtItem;
use App\Models\InvtItemBarcode;
use App\Models\InvtItemCategory;
use App\Models\InvtItemPackge;
use App\Models\InvtItemRack;
use App\Models\InvtItemStock;
use App\Models\InvtItemUnit;
use App\Models\InvtWarehouse;
use App\Models\PreferenceCompany;
use App\Models\PreferenceVoucher;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SIIRemoveLog;
use App\Models\SystemLoginLog;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
// *change
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Prophecy\Doubler\Generator\Node\ReturnTypeNode;

class ConfigurationDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    //     $data=1;
    //    if(count(CoreMember::all())==0){
    //     $data = 0;
    //    }
    //     session()->flash('data', $data);
    if (empty(Session::get('close-cashier-token'))||is_null(Session::get('close-cashier-token'))) {
        Session::put('close-cashier-token',Str::uuid());
    }
    if (!Session::get('start_date')) {
        $start_date = date('Y-m-d');
    } else {
        $start_date = Session::get('start_date');
    }
    if (!Session::get('end_date')) {
        $end_date = date('Y-m-d');
    } else {
        $end_date = Session::get('end_date');
    }
        return view('content.ConfigurationData.ConfigurationData',compact('start_date', 'end_date', ));
    }

    public function checkDataConfiguration()
    {
        $response = Http::get(env('API_URL', 'https://ciptapro.com/kasihibu_minimarket').'/api/get-data-item-stock');
        $result_item_stock = json_decode($response,TRUE);
        
        foreach ($result_item_stock as $key => $val) {
            $data_stock[$key] = InvtItemStock::where('company_id', Auth::user()->company_id)
            ->where('item_id', $val['item_id'])
            ->where('item_unit_id', $val['item_unit_id'])
            ->where('item_category_id', $val['item_category_id'])
            ->where('last_balance','!=',$val['last_balance'])
            ->first();
        }

        $data = array_slice($data_stock, 0, 1);
        return json_encode($data, true);

    }

    public function dwonloadConfigurationData()
    {
        $response = Http::get(env('API_URL', 'https://ciptapro.com/kasihibu_minimarket').'/api/get-data');
        DB::beginTransaction();
        try {
            CoreMember::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['member'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    CoreMember::create($val);
                }
            }

            InvtItemCategory::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['category'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemCategory::create($val);
                }
            }

            InvtItemUnit::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['unit'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemUnit::create($val);
                }
            }

            InvtItemBarcode::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['barcode'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemBarcode::create($val);
                }
            }

            InvtItemPackge::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['packge'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemPackge::create($val);
                }
            }

            InvtWarehouse::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['warehouse'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtWarehouse::create($val);
                }
            }

            InvtItem::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['item'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItem::create($val);
                }
            }

            InvtItemStock::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['stock'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemStock::create($val);
                }
            }

            PreferenceVoucher::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['voucher'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    PreferenceVoucher::create($val);
                }
            }

            InvtItemRack::select(DB::statement('SET FOREIGN_KEY_CHECKS = 0'))->truncate();
            foreach ($response['rack'] as $key => $val) {
                if ($val['company_id'] == Auth::user()->company_id) {
                    InvtItemRack::create($val);
                }
            }

            DB::commit();
            session()->flash('msg', "Data berhasil didownload");
            return redirect('configuration-data');
        } catch (\Throwable $th) {
            DB::rollback();
            session()->flash('msg', "Data gagal didownload (".$th.")");
            return redirect('configuration-data');

        }
    }

    public function uploadConfigurationData()
    {
        if (empty(Session::get('close-cashier-token'))||is_null(Session::get('close-cashier-token'))) {
            $msg = "Data Berhasil diupload";
            return redirect('configuration-data')->with('msg', $msg);
        }
        //use Database: Query Builder (DB) not laravel eqloquent or date will be error on server side (not same)
        $sales = DB::table('sales_invoice')
        ->where('status_upload',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        $salesItem = DB::table('sales_invoice_item')
        ->where('status_upload',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        $member = DB::table('core_member')
        ->where('member_account_receivable_amount_temp', '!=', 0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        $closeCashier = DB::table('close_cashier_log')
        ->where('status_upload', 0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        //use DB if needed
        $loginLog = SystemLoginLog::
        where('status_upload', 0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        $salesRemove = SIIRemoveLog::
        where('status_upload', 0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        // dd($closeCashier);
        $response = Http::post(env('API_URL', 'https://ciptapro.com/kasihibu_minimarket').'/api/post-data', [
            'sales'         => json_decode($sales, true),
            'salesItem'     => json_decode($salesItem, true),
            'member'        => json_decode($member, true),
            'closeCashier'  => json_decode($closeCashier, true),
            'loginLog'      => json_decode($loginLog, true),
            'salesRemove'   => json_decode($salesRemove, true),
        ]);

        if ($response->body() == 'true') {
            $data=$response->object();
            session()->flash('error', $data);
            DB::beginTransaction();
            try {

                SalesInvoice::where('status_upload',0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'status_upload' => 1,
                    'updated_id' => Auth::id()
                ]);

                SalesInvoiceItem::where('status_upload',0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'status_upload' => 1,
                    'updated_id' => Auth::id()
                ]);

                CoreMember::where('member_account_receivable_amount_temp', '!=', 0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'member_account_receivable_amount_temp' => 0,
                ]);

                CloseCashierLog::where('status_upload', 0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'status_upload' => 1,
                    'updated_id' => Auth::id()
                ]);

                SystemLoginLog::where('status_upload', 0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'status_upload' => 1,
                ]);

                SIIRemoveLog::where('status_upload', 0)
                ->where('company_id', Auth::user()->company_id)
                ->update([
                    'status_upload' => 1,
                    'updated_id' => Auth::id()
                ]);

                DB::commit();
                $msg = "Data Berhasil diupload";
                Session::forget('close-cashier-token');
                return redirect('configuration-data')->with('msg', $msg);

            } catch (\Throwable $th) {
                $data=$response->object();
                Session::forget('close-cashier-token');
                session()->flash('error', [$th,$data]);
                DB::rollback();
                $msg = "Data Gagal diupload";
                return redirect('configuration-data')->with('msg', $msg);

            }
        } else {

            $data=$response->object();
            session()->flash('error', $data);
            $msg = "Data Gagal diupload";
            return redirect('configuration-data')->with('msg', $msg);
        }
    }

    public function checkCloseCashierConfiguration()
    {
            //js loging avaible at blade
        $time= CloseCashierLog::where('data_state',0)
        ->where('created_at','>',Carbon::now()->subHours())->where('cashier_log_date','=',Carbon::now()->format('Y-m-d'))->get();
        $data = CloseCashierLog::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->whereDate('cashier_log_date', date('Y-m-d'))
        ->orderByDesc('created_at')
        ->get();
    
        if(count($data)==0){
            return ["status"=>0,"msg"=>"Tutup shift 1"];
        }elseif(count($time)>=1&&count($data)>=1){
            if($data[0]->created_at!=$data[1]->created_at){
                return ["status"=>count($data), "msg"=>"the last one"];
            }
            return ["status"=>3,"time"=>Carbon::now()->subHours(),"count"=>[count($time),count($data)],"msg"=>"Shift 1 sudah ditutup, shift 2 masih panjang ("
            .Carbon::parse($time[0]->created_at)->addHour()->diff(Carbon::now())->format('%H:%I:%S').")"];
        }else if(count($data)>1&&count($time)==0){
              //js loging avaible at blade
           if($data[0]->created_at==$data[1]->created_at){
            $data = CloseCashierLog::where('data_state',0)
            ->where('company_id', Auth::user()->company_id)
            ->where('shift_cashier',2)
            ->orderBy('cashier_log_id', 'DESC')
            ->first();
            $data->data_state = '1';
            return ["status"=>1,$data->save()];
           }
        }
        return ["status"=>count($data), "msg"=>"the last one"];
    
    }

    public function closeCashierConfiguration()
    {
        $time= CloseCashierLog::where('created_at','>',Carbon::now()->subHours())->where('cashier_log_date','=',Carbon::now()->format('Y-m-d'))->get('created_at');
        if(count($time)==1){
            $msg = "Shift 1 Sudah Ditutup, Shift 2 Masih Berlangsung Panjang";
            return redirect('/configuration-data')->with('msg',$msg);
        }
        if (empty(Session::get('close-cashier-token'))||is_null(Session::get('close-cashier-token'))) {
            $msg = "Tutup Kasir Berhasil";
            return redirect('/configuration-data')->with('msg',$msg);
        }
        $sales_invoice = SalesInvoice::where('data_state',0)
        ->whereDate('sales_invoice_date', date('Y-m-d'))
        ->where('company_id', Auth::user()->company_id)
        ->get();
        $close_cashier = CloseCashierLog::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->whereDate('cashier_log_date', date('Y-m-d'))
        ->get();
        $first_cashier = CloseCashierLog::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->whereDate('cashier_log_date', date('Y-m-d'))
        ->first();

        $total_cash_transaction         = 0;
        $amount_cash_transaction        = 0;
        $total_receivable_transaction   = 0;
        $amount_receivable_transaction  = 0;
        $total_cashless_transaction     = 0;
        $amount_cashless_transaction    = 0;
        $total_transaction              = 0;
        $total_amount                   = 0;

        foreach ($sales_invoice as $key => $val) {
            if ($val['sales_payment_method'] == 1) {
                $total_cash_transaction += 1;
                $amount_cash_transaction += $val['total_amount'];
            } else if ($val['sales_payment_method'] == 2) {
                $total_receivable_transaction += 1;
                $amount_receivable_transaction += $val['total_amount'];
            } else {
                $total_cashless_transaction += 1;
                $amount_cashless_transaction += $val['total_amount'];
            }

            $total_transaction += 1;
            $total_amount +=  $val['total_amount'];
        }

        if (count($close_cashier) == 1) {
            $data_close_cashier = array(
                'company_id' => Auth::user()->company_id,
                'cashier_log_date' => date('Y-m-d'),
                'shift_cashier' => 2,
                'total_cash_transaction' => $total_cash_transaction - $first_cashier['total_cash_transaction'],
                'amount_cash_transaction' =>  $amount_cash_transaction - $first_cashier['amount_cash_transaction'],
                'total_receivable_transaction' => $total_receivable_transaction - $first_cashier['total_receivable_transaction'],
                'amount_receivable_transaction' => $amount_receivable_transaction - $first_cashier['amount_receivable_transaction'],
                'total_cashless_transaction' => $total_cashless_transaction - $first_cashier['total_cashless_transaction'],
                'amount_cashless_transaction' => $amount_cashless_transaction - $first_cashier['amount_cashless_transaction'],
                'total_transaction' => $total_transaction - ($first_cashier['total_cash_transaction'] + $first_cashier['total_receivable_transaction'] + $first_cashier['total_cashless_transaction']),
                'total_amount' => $total_amount - ($first_cashier['amount_cash_transaction'] + $first_cashier['amount_receivable_transaction'] + $first_cashier['amount_cashless_transaction']),
                'created_id' => Auth::id(),
                'updated_id' => Auth::id()
            );
        } else if (count($close_cashier) == 0) {
            $data_close_cashier = array(
                'company_id' => Auth::user()->company_id,
                'cashier_log_date' => date('Y-m-d'),
                'shift_cashier' => 1,
                'total_cash_transaction' => $total_cash_transaction,
                'amount_cash_transaction' => $amount_cash_transaction,
                'total_receivable_transaction' => $total_receivable_transaction,
                'amount_receivable_transaction' => $amount_receivable_transaction,
                'total_cashless_transaction' => $total_cashless_transaction,
                'amount_cashless_transaction' => $amount_cashless_transaction,
                'total_transaction' => $total_transaction,
                'total_amount' => $total_amount,
                'created_id' => Auth::id(),
                'updated_id' => Auth::id()
            );
        }

        if (CloseCashierLog::create($data_close_cashier)) {
            $msg = "Tutup Kasir Berhasil";
            Session::forget('close-cashier-token');
            return redirect('/configuration-data')->with('msg',$msg);
        } else {
            $msg = "Tutup Kasir Gagal";
            Session::forget('close-cashier-token');
            return redirect('/configuration-data')->with('msg',$msg);
        }
    }

    public function closeCashierTemp(){
        $start_date='2023-07-03 13:00';
        $end_date='2023-07-03 22:00';
        $data = SalesInvoice::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->where('sales_invoice_date','>=',date('Y-m-d', strtotime($start_date)))
        ->where('sales_invoice_date','<=',date('Y-m-d', strtotime($end_date)))
        ->whereTime('created_at','>=',date('H:i:00', strtotime($start_date)))
        ->whereTime('created_at','<=',date('H:i:00', strtotime($end_date)))->get()
        ;   

        $total_transaction = 0;
        $total_amount = 0;
        $total_receivable_transaction = 0;
        $amount_receivable_transaction = 0;
        $total_cashless_transaction = 0;
        $amount_cashless_transaction = 0;
        $total_cash_transaction = 0;
        $amount_cash_transaction = 0;
    
        foreach ($data as $key => $val) {
            if ($val['sales_payment_method'] != null) {
                if ($val['sales_payment_method'] == 1) {
                    $total_cash_transaction += 1;
                    $amount_cash_transaction += $val['total_amount'];
                } else if ($val['sales_payment_method'] == 2) {
                    $total_receivable_transaction += 1;
                    $amount_receivable_transaction += $val['total_amount'];
                } else {
                    $total_cashless_transaction += 1;
                    $amount_cashless_transaction += $val['total_amount'];
                }
            }
            $total_transaction += 1;
            $total_amount += $val['total_amount'];
        }

        $data_company = PreferenceCompany::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->first();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(5, 1, 5, 1); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::AddPage('P', array(75, 3276));

        $pdf::SetFont('helvetica', '', 10);

        $tbl = " 
        <table style=\" font-size:9px; \" >
            <tr>
                <td style=\"text-align: center; font-size:12px; font-weight: bold\">".$data_company['company_name']."</td>
            </tr>
            <tr>
                <td style=\"text-align: center; font-size:9px;\">".$data_company['company_address']."</td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
            
        $tblStock1 = "
        <div>-------------------------------------------------------</div>
        <table style=\" font-size:9px; \" border=\"0\">
            <tr>
                <td width=\"25%\">TGL.</td>
                <td width=\"5%\" style=\"text-align: center;\">:</td>
                <td width=\"70%\">".date('d-m-Y H:i', strtotime($start_date))." - ".date('d-m-Y H:i', strtotime($end_date))."</td>
            </tr>
            <tr>
                <td width=\"25%\">SHIFT</td>
                <td width=\"5%\" style=\"text-align: center;\">:</td>
                <td>2</td>
            </tr>
            <tr>
                <td width=\"25%\">KASIR</td>
                <td width=\"5%\" style=\"text-align: center;\">:</td>
                <td width=\"70%\">".ucfirst(Auth::user()->name)."</td>
            </tr>
        </table>
        <div>-------------------------------------------------------</div>
        ";

        $tblStock2 = "
        <table style=\" font-size:9px; \" width=\" 100% \">
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AWAL</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TOTAL</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$total_transaction.")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($total_amount,0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">PIUTANG</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$total_receivable_transaction.")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($amount_receivable_transaction,0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">E-WALLET</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$total_cashless_transaction.")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($amount_cashless_transaction,0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TUNAI</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$total_cash_transaction.")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($amount_cash_transaction,0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">DISETOR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($amount_cash_transaction,0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AKHIR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
        </table>
        <div>-------------------------------------------------------</div>
        
        ";

        $pdf::writeHTML($tblStock1.$tblStock2, true, false, false, false, '');


        $filename = 'Tutup_Kasir_'.date('d-m-Y',strtotime($start_date)).'_sd_'.date('d-m-Y',strtotime($end_date)).'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function reprintCloseCashierConfiguration(Request $request)
    {
        Session::put('start_date', $request->date);
        $data = CloseCashierLog::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->where('cashier_log_date','=',date('Y-m-d', strtotime($request->date)))
        ->where('shift_cashier','=',$request->shift)
        ->first();

        $data_company = PreferenceCompany::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->first();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 1, 5, 1); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::AddPage('P', array(76, 3276));

        $pdf::SetFont('dejavusans', '', 10);

        $tbl = " 
        <table style=\" font-size:9px; \" >
            <tr>
                <td style=\"text-align: center; font-size:12px; font-weight: bold\">".$data_company['company_name']."</td>
            </tr>
            <tr>
                <td style=\"text-align: center; font-size:9px;\">".$data_company['company_address']."</td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
            
        $tblStock1 = "
        <div>---------------------------------------------------</div>
        <table style=\" font-size:9px; \">
            <tr>
                <td width=\"25%\">TGL.</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td width=\"60%\">".date('d-m-Y H:i',strtotime($data->created_at))."</td>
            </tr>
            <tr>
                <td width=\"25%\">TGL. CETAK</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td width=\"60%\">".date('d-m-Y H:i')."</td>
            </tr>
            <tr>
                <td width=\"25%\">SHIFT</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td>".$data['shift_cashier']."</td>
            </tr>
            <tr>
                <td width=\"25%\">KASIR</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td width=\"60%\">".ucfirst(Auth::user()->name)."</td>
            </tr>
        </table>
        <div>---------------------------------------------------</div>
        ";

        $tblStock2 = "
        <table style=\" font-size:9px; \" width=\" 100% \">
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AWAL</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TOTAL</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['total_amount'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">PIUTANG</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_receivable_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_receivable_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">E-WALLET</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_cashless_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cashless_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TUNAI</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_cash_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cash_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">DISETOR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cash_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AKHIR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
        </table>
        <div>---------------------------------------------------</div>
        
        ";

        $pdf::writeHTML($tblStock1.$tblStock2, true, false, false, false, '');


        $filename = 'Tutup_Kasir.pdf';
        $pdf::Output($filename, 'I');
    }

    public function printCloseCashierConfiguration()
    {
        $data = CloseCashierLog::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->orderBy('cashier_log_id', 'DESC')
        ->first();

        $data_company = PreferenceCompany::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->first();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 1, 5, 1); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::AddPage('P', array(76, 3276));

        $pdf::SetFont('dejavusans', '', 10);

        $tbl = " 
        <table style=\" font-size:9px; \" >
            <tr>
                <td style=\"text-align: center; font-size:12px; font-weight: bold\">".$data_company['company_name']."</td>
            </tr>
            <tr>
                <td style=\"text-align: center; font-size:9px;\">".$data_company['company_address']."</td>
            </tr>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
            
        $tblStock1 = "
        <div>---------------------------------------------------</div>
        <table style=\" font-size:9px; \">
            <tr>
                <td width=\"25%\">TGL.</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td width=\"60%\">".date('d-m-Y')."  ".date('H:i')."</td>
            </tr>
            <tr>
                <td width=\"25%\">SHIFT</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td>".$data['shift_cashier']."</td>
            </tr>
            <tr>
                <td width=\"25%\">KASIR</td>
                <td width=\"10%\" style=\"text-align: center;\">:</td>
                <td width=\"60%\">".ucfirst(Auth::user()->name)."</td>
            </tr>
        </table>
        <div>---------------------------------------------------</div>
        ";

        $tblStock2 = "
        <table style=\" font-size:9px; \" width=\" 100% \">
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AWAL</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TOTAL</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['total_amount'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">PIUTANG</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_receivable_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_receivable_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">E-WALLET</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_cashless_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cashless_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 35% \" style=\"text-align: left;\">TUNAI</td>
                <td width=\" 25% \" style=\"text-align: right;\">(".$data['total_cash_transaction'].")</td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cash_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">DISETOR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">".number_format($data['amount_cash_transaction'],0,',','.')."</td>
            </tr>
            <tr>
                <td width=\" 45% \" style=\"text-align: left;\">SALDO AKHIR</td>
                <td width=\" 15% \" style=\"text-align: right;\"></td>
                <td width=\" 7% \" style=\"text-align: right;\">:</td>
                <td width=\" 33% \" style=\"text-align: right;\">400.000</td>
            </tr>
        </table>
        <div>---------------------------------------------------</div>
        
        ";

        $pdf::writeHTML($tblStock1.$tblStock2, true, false, false, false, '');


        $filename = 'Tutup_Kasir.pdf';
        $pdf::Output($filename, 'I');
    }

    public function backupDataConfiguration()
    {
        exec('start /B C:\xampp\htdocs\kasihibu_minimarket\backup_data.bat');

        $msg = "Data Berhasil dicadangkan";
        return redirect('/configuration-data')->with('msg', $msg);
    }

    public function checkReuploadData(Request $request) {
        if(Auth::id() != 55){      
            session()->flash('msg','Data Gagal diupload');
            return ['status'=>0,'msg'=>'Data Gagal diupload','Unautorized'];  
        }
        $sales = SalesInvoice::
        where('company_id', Auth::user()->company_id)
        ->where('sales_invoice_date','>=',date('Y-m-d', strtotime($request->header('start_date'))))
        ->where('sales_invoice_date','<=',date('Y-m-d', strtotime($request->header('end_date'))))
        ;
        $salesItem = SalesInvoiceItem::
        where('company_id', Auth::user()->company_id)
        ->where('created_at','>=',date('Y-m-d H:i', strtotime($request->header('start_date')." 00:00")))
        ->where('created_at','<=',date('Y-m-d H:i', strtotime($request->header('end_date')." 23:59")))
        ;
        if(count($sales->get())==0&&count($salesItem->get())==0){
            return ['status'=>1,'data' => 'no data'];
        }
        $response = Http::post(env('API_URL', 'https://ciptapro.com/kasihibu_minimarket').'/api/check-uploaded', [
            'start_date' => $request->header('start_date')." 00:00",
            'end_date'  => $request->header('end_date')." 23:59",
            'count_sales' => count($sales->get()),
            "sales_item"=>count($salesItem->get())
        ]);
        if($response->object()->result){
            
            DB::beginTransaction();
            try {
            $salesItem->where('status_upload', 0)
            ->update([
                'status_upload' => 1,
                'updated_id' => Auth::id()
            ]);
            $sales->where('status_upload', 0)
            ->update([
                'status_upload' => 1,
                'updated_id' => Auth::id()
            ]);
            DB::commit();
            return ['status'=>5,'data' => $response->object()];
        } catch (\Throwable $th) {
            DB::rollback();
            return ['status'=>0,$th,'from catch',$response->object()];
        }
        }
        return ['status'=>0,$response->body(),$response->object()->result,$request->header('start_date'),$request->header('end_date'),date('Y-m-d H:i', strtotime($request->header('end_date')." 00:00")),count($sales->get()),count($salesItem->get())];
    }
    public function reuploadConfiguration(Request $request){
        if(Auth::id() != 55){
            session()->flash('msg','Data Gagal diupload');
            return ['status'=>0,'msg'=>'Data Gagal diupload','Unautorized'];  

        }
        Session::put('start_date', $request->start_date);
        Session::put('end_date', $request->end_date);
       
        $sales =  DB::table('sales_invoice')
        ->where('company_id', Auth::user()->company_id)
        ->where('sales_invoice_date','>=',date('Y-m-d', strtotime($request->header('start_date'))))
        ->where('sales_invoice_date','<=',date('Y-m-d', strtotime($request->header('end_date'))))
        ->get();
        $salesItem = DB::table('sales_invoice_item')
        ->where('company_id', Auth::user()->company_id)
        ->where('created_at','>=',date('Y-m-d', strtotime($request->header('start_date')." 00:00")))
        ->where('created_at','<=',date('Y-m-d', strtotime($request->header('end_date')." 23:59")))
        ->get();
        // dd($salesItem);            
        $response = Http::post(env('API_URL', 'https://ciptapro.com/kasihibu_minimarket').'/api/reupload-data', [
            'start_date' => $request->start_date,
            'end_date'  => $request->end_date,
            'sales'         => json_decode($sales, true),
            'salesItem'     => json_decode($salesItem, true),
        ]);   
        if ($response->object()->result) {    
            session()->flash('msg','Data Berhasil diupload');
            return ['status'=>1,'msg'=>'Data Berhasil diupload','changed data'=>$response->object()->data];  
                    
        } else {
            session()->flash('msg','Data Gagal diupload');
            return ['status'=>0,'msg'=>'Data Gagal diupload','data'=>$response->body()];  
        }
    }
    public function test() {
        dd(['header'=>[0],'data'=>[0=>['data1','no apa','isi'=>[3]],1=>['data1','no apa','isi'=>[3]],2=>['data1','no apa','isi'=>[3]]]]);
    }
    public function getShift(Request $request) {
          $data = CloseCashierLog::where('data_state',0)
          ->where('company_id', Auth::user()->company_id)
          ->where('cashier_log_date','=',date('Y-m-d', strtotime($request->date)))
          ->get();
          $response = '';
          foreach ($data as $key => $value) {
            $response .= "<option data-kt-flag='".$value->shift_cashier."' value='".$value->shift_cashier."' ".($value->shift_cashier == old('shift_cashier', $request->shift_cashier_old ?? '') ? 'selected' :'')."  >". $value->shift_cashier."</option>";
          }
          return response($response);
    }
}
