<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PraKonsultasiRekomendasiItem extends Model
{
    protected $table = 'pra_konsultasi_rekomendasi_items';
    protected $primaryKey = 'pra_konsultasi_rekomendasi_item_id';

    protected $fillable = [
        'patient_form_id','product_id','treatment_id','bundle_id','catatan'
    ];

    public function patientForm()
    {
        return $this->belongsTo(PatientForm::class, 'patient_form_id', 'patient_form_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'bundle_id');
    }

    public function getItemNamaAttribute(): string
    {
        return $this->product?->nama
            ?? $this->treatment?->nama
            ?? $this->bundle?->nama
            ?? '-';
    }

    public function getItemTipeAttribute(): string
    {
        if ($this->product_id) return 'Produk';
        if ($this->treatment_id) return 'Treatment';
        if ($this->bundle_id) return 'Bundle';
        return '-';
    }
}
