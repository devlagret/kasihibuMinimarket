<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcctAccount;
use App\Models\AcctAccountSetting;
use App\Models\AcctProfitLossReport;
use App\Models\CloseCashierLog;
use App\Models\CoreMember;
use App\Models\CoreMemberKopkar;
use App\Models\Expenditure;
use App\Models\InvtItem;
use App\Models\InvtItemBarcode;
use App\Models\InvtItemCategory;
use App\Models\InvtItemPackge;
use App\Models\InvtItemRack;
use App\Models\InvtItemStock;
use App\Models\InvtItemUnit;
use App\Models\InvtWarehouse;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherItem;
use App\Models\PreferenceTransactionModule;
use App\Models\PreferenceVoucher;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SystemLoginLog;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function getDataItem(){
        $data = InvtItem::get();

        return json_encode($data);
    }

    public function getDataItemUnit()
    {
        $data = InvtItemUnit::get();

        return json_encode($data);
    }

    public function getDataItemCategory()
    {
        $data = InvtItemCategory::get();

        return json_encode($data);
    }

    public function getDataItemWarehouse()
    {
        $data = InvtWarehouse::get();

        return json_encode($data);
    }

    public function getDataItemBarcode()
    {
        $data = InvtItemBarcode::get();

        return json_encode($data);
    }

    public function getDataItemPackge()
    {
        $data = InvtItemPackge::get();

        return json_encode($data);
    }

    public function getDataItemStock()
    {
        $data = InvtItemStock::get();

        return json_encode($data);
    }

    public function postDataSalesInvoice(Request $request)
    {
        $transaction_module_code = 'PJL';
        $transaction_module_id  = $this->getTransactionModuleID($transaction_module_code);

        $data_journal = array(
            'company_id'                    => $request['company_id'],
            'journal_voucher_status'        => 1,
            'journal_voucher_description'   => $this->getTransactionModuleName($transaction_module_code),
            'journal_voucher_title'         => $this->getTransactionModuleName($transaction_module_code),
            'transaction_module_id'         => $transaction_module_id,
            'transaction_module_code'       => $transaction_module_code,
            'journal_voucher_date'          => $request['sales_invoice_date'],
            'transaction_journal_no'        => $request['sales_invoice_no'],
            'journal_voucher_period'        => date('Ym', strtotime($request['sales_invoice_date'])),
            'updated_id'                    => $request['updated_id'],
            'created_id'                    => $request['created_id']
        );
        JournalVoucher::create($data_journal);

        if ($request['sales_payment_method'] == 1) {
            $account_setting_name = 'sales_cash_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_debit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_debit);

            $account_setting_name = 'sales_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_credit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_credit);
        } else if ($request['sales_payment_method'] == 2) {
            $account_setting_name = 'sales_cash_receivable_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_debit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_debit);

            $account_setting_name = 'sales_receivable_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_credit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_credit);
        } else {
            $account_setting_name = 'sales_cashless_cash_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_debit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_debit);

            $account_setting_name = 'sales_cashless_account';
            $account_id = $this->getAccountId($account_setting_name);
            $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
            $account_default_status = $this->getAccountDefaultStatus($account_id);
            $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
            if ($account_setting_status == 0){
                $debit_amount = $request['total_amount'];
                $credit_amount = 0;
            } else {
                $debit_amount = 0;
                $credit_amount = $request['total_amount'];
            }
            $journal_credit = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                'account_id'                    => $account_id,
                'journal_voucher_amount'        => $request['total_amount'],
                'account_id_default_status'     => $account_default_status,
                'account_id_status'             => $account_setting_status,
                'journal_voucher_debit_amount'  => $debit_amount,
                'journal_voucher_credit_amount' => $credit_amount,
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucherItem::create($journal_credit);
        }

        if ($request['data_state'] == 1) {
            $transaction_module_code = 'HPSPJL';
            $transaction_module_id  = $this->getTransactionModuleID($transaction_module_code);
            $journal = array(
                'company_id'                    => $request['company_id'],
                'journal_voucher_status'        => 1,
                'journal_voucher_description'   => $this->getTransactionModuleName($transaction_module_code),
                'journal_voucher_title'         => $this->getTransactionModuleName($transaction_module_code),
                'transaction_module_id'         => $transaction_module_id,
                'transaction_module_code'       => $transaction_module_code,
                'transaction_journal_no'        => $request['sales_invoice_no'],
                'journal_voucher_date'          => $request['sales_invoice_date'],
                'journal_voucher_period'        => date('Ym', strtotime($request['sales_invoice_date'])),
                'updated_id'                    => $request['updated_id'],
                'created_id'                    => $request['created_id']
            );
            JournalVoucher::create($journal);
            if ($request['sales_payment_method'] == 1) {
                $account_setting_name = 'sales_cash_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 0){
                    $account_setting_status = 1;
                } else {
                    $account_setting_status = 0;
                }
                if ($account_setting_status == 0){ 
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_debit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_debit);
        
                $account_setting_name = 'sales_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 1){
                    $account_setting_status = 0;
                } else {
                    $account_setting_status = 1;
                }
                if ($account_setting_status == 0){
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_credit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_credit);
            } else if ($request['sales_payment_method'] == 2) {
                $account_setting_name = 'sales_cash_receivable_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 0){
                    $account_setting_status = 1;
                } else {
                    $account_setting_status = 0;
                }
                if ($account_setting_status == 0){ 
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_debit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_debit);
        
                $account_setting_name = 'sales_receivable_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 1){
                    $account_setting_status = 0;
                } else {
                    $account_setting_status = 1;
                }
                if ($account_setting_status == 0){
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_credit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_credit);
            } else {
                $account_setting_name = 'sales_cashless_cash_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 0){
                    $account_setting_status = 1;
                } else {
                    $account_setting_status = 0;
                }
                if ($account_setting_status == 0){ 
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_debit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_debit);
        
                $account_setting_name = 'sales_cashless_account';
                $account_id = $this->getAccountId($account_setting_name);
                $account_setting_status = $this->getAccountSettingStatus($account_setting_name);
                $account_default_status = $this->getAccountDefaultStatus($account_id);
                $journal_voucher_id = JournalVoucher::orderBy('created_at', 'DESC')->where('company_id', $request['company_id'])->first();
                if($account_setting_status == 1){
                    $account_setting_status = 0;
                } else {
                    $account_setting_status = 1;
                }
                if ($account_setting_status == 0){
                    $debit_amount = $request['total_amount'];
                    $credit_amount = 0;
                } else {
                    $debit_amount = 0;
                    $credit_amount = $request['total_amount'];
                }
                $journal_credit = array(
                    'company_id'                    => $request['company_id'],
                    'journal_voucher_id'            => $journal_voucher_id['journal_voucher_id'],
                    'account_id'                    => $account_id,
                    'journal_voucher_amount'        => $request['total_amount'],
                    'account_id_default_status'     => $account_default_status,
                    'account_id_status'             => $account_setting_status,
                    'journal_voucher_debit_amount'  => $debit_amount,
                    'journal_voucher_credit_amount' => $credit_amount,
                    'updated_id'                    => $request['updated_id'],
                    'created_id'                    => $request['created_id']
                );
                JournalVoucherItem::create($journal_credit);
            }
        }

    //     $data = SalesInvoice::create([
    //         'company_id'                => $request['company_id'],
    //         'customer_id'               => $request['customer_id'],
    //         'voucher_id'                => $request['voucher_id'],
    //         'voucher_no'                => $request['voucher_no'],
    //         'sales_invoice_no'          => $request['sales_invoice_no'],
    //         'sales_invoice_date'        => $request['sales_invoice_date'],
    //         'sales_payment_method'      => $request['sales_payment_method'],
    //         'subtotal_item'             => $request['subtotal_item'],
    //         'subtotal_amount'           => $request['subtotal_amount'],
    //         'voucher_amount'            => $request['voucher_amount'],
    //         'discount_percentage_total' => $request['discount_percentage_total'],
    //         'discount_amount_total'     => $request['discount_amount_total'],
    //         'total_amount'              => $request['total_amount'],
    //         'paid_amount'               => $request['paid_amount'],
    //         'change_amount'             => $request['change_amount'],
    //         'data_state'                => $request['data_state'],
    //         'created_id'                => $request['created_id'],
    //         'updated_id'                => $request['updated_id'],
    //         'created_at'                => $request['created_at'],
    //         'updated_at'                => $request['updated_at'],
    //    ]);

       $data =  SalesInvoice::create($request->all());
        
        return $data;
    }

    public function getAccountDefaultStatus($account_id)
    {
        $data = AcctAccount::where('account_id',$account_id)->first();

        return $data['account_default_status'];
    }

    public function getAccountSettingStatus($account_setting_name)
    {
        $data = AcctAccountSetting::where('account_setting_name', $account_setting_name)
        ->first();

        return $data['account_setting_status'];
    }

    public function getAccountId($account_setting_name)
    {
        $data = AcctAccountSetting::where('account_setting_name', $account_setting_name)
        ->first();

        return $data['account_id'];
    }

    public function getTransactionModuleID($transaction_module_code)
    {
        $data = PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)->first();

        return $data['transaction_module_id'];
    }

    public function getTransactionModuleName($transaction_module_code)
    {
        $data = PreferenceTransactionModule::where('transaction_module_code',$transaction_module_code)->first();

        return $data['transaction_module_name'];
    }

    public function postDataSalesInvoiceItem(Request $request)
    {
        $data_packge = InvtItemPackge::where('company_id',$request['company_id'])
        ->where('item_id', $request['item_id'])
        ->where('item_unit_id', $request['item_unit_id'])
        ->where('item_category_id', $request['item_category_id'])
        ->first();

        $data_stock = InvtItemStock::where('company_id',$request['company_id'])
        ->where('item_id', $request['item_id'])
        ->where('item_unit_id', $request['item_unit_id'])
        ->where('item_category_id', $request['item_category_id'])
        ->first();

        if(isset($data_stock) && ($request['data_state'] == 0)){
            $table = InvtItemStock::findOrFail($data_stock['item_stock_id']);
            $table->last_balance = $data_stock['last_balance'] - ($request['quantity'] * $data_packge['item_default_quantity']);
            // $table->updated_id = $request['updated_id'];
            $table->save();

        }
        $data = SalesInvoiceItem::create($request->all());
        return $data;
        // return $request->all();
    }

    public function getDataSalesInvoice()
    {
        $data = SalesInvoice::where('sales_invoice.data_state',0)
        ->get();

        return json_encode($data);
    }

    public function getDataExpenditure()
    {
        $data = Expenditure::where('data_state',0)
        ->get();

        return json_encode($data);
    }

    public function getDataProfitLossReport()
    {
        $data = AcctProfitLossReport::where('data_state',0)
        ->get();

        return json_encode($data);
    }

    public function getDataJournalVoucher()
    {
        $data = JournalVoucher::join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','acct_journal_voucher.journal_voucher_id')
        ->where('acct_journal_voucher.data_state',0)
        ->get();

        return json_encode($data);
    }

    public function getDataCoreMember()
    {
        $member_kopkar = CoreMemberKopkar::join('ciptaprocpanel_kopkarkasihibu.core_member_working', 'ciptaprocpanel_kopkarkasihibu.core_member_working.member_id','=', 'core_member.member_id')
        ->join('ciptaprocpanel_kopkarkasihibu.core_division', 'ciptaprocpanel_kopkarkasihibu.core_member_working.division_id','=', 'ciptaprocpanel_kopkarkasihibu.core_division.division_id')
        ->get();

        CoreMember::whereNotNull('member_id')->delete();
        foreach ($member_kopkar as $key => $val) {
            CoreMember::create([
                'member_id' => $val['member_id'],
                'member_no' => $val['member_no'],
                'member_name' => $val['member_name'],
                'division_name' => $val['division_name'],
                'member_mandatory_savings' => (int)$val['member_mandatory_savings'],
                'member_account_receivable_amount' => (int)$val['member_account_credits_store_debt'],
                'member_account_receivable_status' => $val['member_account_receivable_status'],
                'member_account_receivable_amount_temp' => (int)$val['member_account_receivable_amount_temp'],
                'data_state' => $val['data_state']
            ]);
        }
        $core_member = CoreMember::get();
        
        return json_encode($core_member);
    }

    public function postDataCoreMember(Request $request)
    {
        $data_member = CoreMember::where('member_no',$request->member_no)
        ->first();
        $data = CoreMember::where('member_no',$request->member_no)
        ->update(['member_account_receivable_amount' => $data_member['member_account_receivable_amount'] + $request->member_account_receivable_amount_temp]);

        return $data;
    }

    public function getDataItemRack()
    {
        $data = InvtItemRack::get();

        return json_encode($data);
    }

    public function postDataCoreMemberKopkar(Request $request)
    {
        $data_member = CoreMemberKopkar::where('member_no',$request->member_no)
        ->first();
        $data = CoreMemberKopkar::where('member_no',$request->member_no)
        ->update(['member_account_receivable_amount' => $data_member['member_account_receivable_amount'] + $request->member_account_receivable_amount_temp, 'member_account_credits_store_debt' => $data_member['member_account_credits_store_debt'] + $request->member_account_receivable_amount_temp]);

        return $data;
    }

    public function getDataPreferenceVoucher()
    {
        $data = PreferenceVoucher::get();

        return json_encode($data);
    }

    public function postDataLoginLog(Request $request)
    {
        $data = SystemLoginLog::create($request->all());

        return $data;
    }

    public function postDataCloseCashier(Request $request)
    {
        $data = CloseCashierLog::create($request->all());

        return $data;
    }
}
