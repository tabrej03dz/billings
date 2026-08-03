<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'reverse_charge' => 'boolean',
        'charges_json'   => 'array',   // if you store extra charges as json
        'items_json'     => 'array',   // if you store items as json (optional)
        'invoice_date'   => 'date',
        'kots_json'      => 'array',
        'hospital_details_json' => 'array',
        'visit_at' => 'datetime',
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];




    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function business(){
        return $this->belongsTo(Business::class, 'business_id');
    }

//    public function additonalCharges(){
//        return $this->hasMany(Additional)
//    }


//    protected static function booted()
//    {
//        static::created(function (Invoice $invoice) {
//            $invoice->loadMissing('items.item');
//            app(StockService::class)->recordSale($invoice);
//        });
//
//        static::updated(function (Invoice $invoice) {
//            $invoice->loadMissing('items.item');
//            $service = app(StockService::class);
//
//            $service->rollbackReference($invoice);
//            $service->recordSale($invoice);
//        });
//
//        static::deleted(function (Invoice $invoice) {
//            $invoice->loadMissing('items.item');
//            app(StockService::class)->rollbackReference($invoice);
//        });
//    }


    public function applyJewelleryGST()
    {
        // Default jewellery GST
        $totalGstPercent = 3; // 3%

        // reset
        $this->cgst_percent = 0;
        $this->cgst_amount  = 0;
        $this->sgst_percent = 0;
        $this->sgst_amount  = 0;
        $this->igst_percent = 0;
        $this->igst_amount  = 0;

        $taxable = (float) ($this->taxable_amount ?? $this->sub_total ?? 0);

        if ($taxable <= 0) {
            return;
        }

        // Same state = CGST + SGST
        if ($this->is_same_state) {
            $this->cgst_percent = 1.5;
            $this->sgst_percent = 1.5;

            $this->cgst_amount = round($taxable * 1.5 / 100, 2);
            $this->sgst_amount = round($taxable * 1.5 / 100, 2);
        }
        // Different state = IGST
        else {
            $this->igst_percent = 3;
            $this->igst_amount  = round($taxable * 3 / 100, 2);
        }

        // Final value
        $this->final_value =
            $taxable
            + $this->cgst_amount
            + $this->sgst_amount
            + $this->igst_amount
            - ($this->less_amount ?? 0);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function billRequest()
    {
        return $this->belongsTo(BillRequest::class, 'bil_request_id');
    }


    public function patientVisit()
    {
        return $this->belongsTo(
            PatientVisit::class,
            'patient_visit_id'
        );
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
