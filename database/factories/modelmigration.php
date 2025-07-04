<?php

// 1. Admin Model
// app/Models/Admin.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'admin_name',
        'admin_username',
        'admin_password',
        'phone',
        'address',
        'photo'
    ];

    protected $hidden = [
        'admin_password',
        'remember_token',
    ];

    protected $casts = [
        'admin_password' => 'hashed',
    ];

    // Relationships
    public function bankBalances()
    {
        return $this->hasMany(BankBalance::class, 'id_admin', 'id_admin');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_admin', 'id_admin');
    }

    public function wasteCollectionSchedules()
    {
        return $this->hasMany(WasteCollectionSchedule::class, 'id_admin', 'id_admin');
    }

    public function news()
    {
        return $this->hasMany(News::class, 'id_admin', 'id_admin');
    }

    public function wasteTypes()
    {
        return $this->hasMany(WasteType::class, 'id_admin', 'id_admin');
    }
}

// 2. User Model
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'user_name',
        'username',
        'user_password',
        'phone',
        'address',
        'balance',
        'withdrawal_count',
        'withdrawal_amount'
    ];

    protected $hidden = [
        'user_password',
        'remember_token',
    ];

    protected $casts = [
        'user_password' => 'hashed',
        'balance' => 'decimal:2',
        'withdrawal_amount' => 'decimal:2',
        'withdrawal_count' => 'integer',
    ];

    // Relationships
    public function bankBalances()
    {
        return $this->hasMany(BankBalance::class, 'id_user', 'id_user');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'id_user', 'id_user');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'id_user', 'id_user');
    }

    public function wasteTypes()
    {
        return $this->hasMany(WasteType::class, 'id_user', 'id_user');
    }

    public function wasteTransactions()
    {
        return $this->hasMany(WasteTransaction::class, 'id_user', 'id_user');
    }

    // Accessor & Mutator
    public function getFormattedBalanceAttribute()
    {
        return 'Rp ' . number_format($this->balance, 0, ',', '.');
    }

    // Scopes
    public function scopeWithPositiveBalance($query)
    {
        return $query->where('balance', '>', 0);
    }
}

// 3. BankBalance Model
// app/Models/BankBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankBalance extends Model
{
    use HasFactory;

    protected $table = 'bank_balances';
    protected $primaryKey = 'id_balance';

    protected $fillable = [
        'id_admin',
        'id_user',
        'total_balance',
        'description',
        'date'
    ];

    protected $casts = [
        'total_balance' => 'decimal:2',
        'date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}

// 4. Withdrawal Model
// app/Models/Withdrawal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $table = 'withdrawals';
    protected $primaryKey = 'id_withdrawal';

    protected $fillable = [
        'id_user',
        'user_name',
        'withdrawal_date',
        'withdrawal_amount'
    ];

    protected $casts = [
        'withdrawal_amount' => 'decimal:2',
        'withdrawal_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Accessor
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->withdrawal_amount, 0, ',', '.');
    }
}

// 5. Notification Model
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'id_notification';

    protected $fillable = [
        'id_user',
        'id_admin',
        'message_content',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('date', 'desc');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }
}

// 6. WasteCollectionSchedule Model
// app/Models/WasteCollectionSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCollectionSchedule extends Model
{
    use HasFactory;

    protected $table = 'waste_collection_schedules';
    protected $primaryKey = 'id_schedule';

    protected $fillable = [
        'id_admin',
        'photo',
        'content',
        'date',
        'activity'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}

// 7. News Model
// app/Models/News.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    protected $primaryKey = 'id_news';

    protected $fillable = [
        'id_admin',
        'title',
        'content',
        'photo',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('date', '<=', now()->toDateString())
            ->orderBy('date', 'desc');
    }

    public function scopeLatest($query, $limit = 5)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }

    // Accessor
    public function getExcerptAttribute()
    {
        return substr(strip_tags($this->content), 0, 150) . '...';
    }
}

// 8. WasteType Model
// app/Models/WasteType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteType extends Model
{
    use HasFactory;

    protected $table = 'waste_types';
    protected $primaryKey = 'id_waste_type';

    protected $fillable = [
        'id_user',
        'id_admin',
        'waste_type',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    // Accessor
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.') . '/kg';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('price', '>', 0);
    }
}

// 9. WasteTransaction Model
// app/Models/WasteTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteTransaction extends Model
{
    use HasFactory;

    protected $table = 'waste_transactions';
    protected $primaryKey = 'id_transaction';

    protected $fillable = [
        'id_user',
        'waste_type',
        'weight',
        'description',
        'price',
        'photo'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Accessors
    public function getTotalValueAttribute()
    {
        return $this->weight * $this->price;
    }

    public function getFormattedTotalValueAttribute()
    {
        return 'Rp ' . number_format($this->total_value, 0, ',', '.');
    }

    public function getFormattedWeightAttribute()
    {
        return $this->weight . ' kg';
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }

    public function scopeByWasteType($query, $wasteType)
    {
        return $query->where('waste_type', $wasteType);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

// Usage Examples:

/*
// 1. Getting user with all transactions
$user = User::with(['wasteTransactions', 'withdrawals'])->find(1);

// 2. Getting admin with all managed content
$admin = Admin::with(['news', 'wasteCollectionSchedules', 'notifications'])->find(1);

// 3. Getting recent notifications for a user
$notifications = Notification::forUser(1)->recent()->get();

// 4. Getting upcoming waste collection schedules
$upcomingSchedules = WasteCollectionSchedule::upcoming()->get();

// 5. Getting user's transaction summary
$user = User::find(1);
$totalEarnings = $user->wasteTransactions()->sum(DB::raw('weight * price'));

// 6. Getting latest news
$latestNews = News::published()->latest(3)->get();

// 7. Getting active waste types
$activeWasteTypes = WasteType::active()->get();

// 8. Creating a new transaction
WasteTransaction::create([
    'id_user' => 1,
    'waste_type' => 'Plastic',
    'weight' => 2.5,
    'price' => 3000,
    'description' => 'Plastic bottles'
]);
*/