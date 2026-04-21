<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password','phone', 'tanggal_lahir', 'jenis_kelamin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dokterProfile()
    {
        return $this->hasOne(\App\Models\Dokter::class, 'user_id', 'id');
    }
    
    public function terapisProfile()
    {
        return $this->hasOne(\App\Models\Terapis::class, 'user_id', 'id');
    }

    public function pasienProfile()
    {
        return $this->hasOne(\App\Models\Pasien::class, 'user_id', 'id');
    }

    public function forumQuestions()
    {
        return $this->hasMany(\App\Models\ForumQuestion::class, 'user_id', 'id');
    }

    public function forumAnswers()
    {
        return $this->hasMany(\App\Models\ForumAnswer::class, 'dokter_id', 'id');
    }

    public function appointmentsAsDokter()
    {
        return $this->hasMany(Appointment::class, 'dokter_id', 'id');
    }

    public function appointmentsAsTerapis()
    {
        return $this->hasMany(Appointment::class, 'terapis_id', 'id');
    }

    // Relasi untuk mengambil item order milik Dokter via Appointment
    public function revenueItemsAsDokter()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Appointment::class,
            'dokter_id',      // Foreign key di tabel appointments
            'appointment_id', // Foreign key di tabel order_items
            'id',             // Local key di tabel users
            'appointment_id'  // Local key di tabel appointments
        );
    }

    // Relasi untuk mengambil item order milik Terapis via Appointment
    public function revenueItemsAsTerapis()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            Appointment::class,
            'terapis_id',     // Foreign key di tabel appointments
            'appointment_id', // Foreign key di tabel order_items
            'id',             // Local key di tabel users
            'appointment_id'  // Local key di tabel appointments
        );
    }
}
