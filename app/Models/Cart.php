<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'fm_cart';
    protected $primaryKey = 'cart_seq';
    public $timestamps = false; // Legacy tables usually manage dates manually or allow nulls, but let's check.
    // fm_cart has regist_date and update_date. Eloquent expects created_at/updated_at.
    // We should override strict timestamp handling or map them.
    const CREATED_AT = 'regist_date';
    const UPDATED_AT = 'update_date';

    protected $guarded = [];

    // Relationships
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }

    public function options()
    {
        return $this->hasMany(CartOption::class, 'cart_seq', 'cart_seq');
    }

    public function inputs()
    {
        return $this->hasMany(CartInput::class, 'cart_seq', 'cart_seq');
    }

    // Scopes
    public function scopeCurrentUser($query)
    {
        if (Auth::check()) {
            return $query->where('member_seq', Auth::id());
        } else {
            return $query->where('session_id', Session::getId());
        }
    }

    /**
     * Merge session-based cart items into member-based cart items upon login
     */
    public static function mergeForMember($memberSeq, $sessionId)
    {
        if (!$memberSeq || !$sessionId) {
            return;
        }

        $sessionCarts = self::where('session_id', $sessionId)
            ->where(function($q) {
                $q->where('member_seq', 0)->orWhereNull('member_seq');
            })
            ->with(['options', 'inputs'])
            ->get();

        foreach ($sessionCarts as $sessionCart) {
            $sessionOption = $sessionCart->options->first();
            if (!$sessionOption) {
                $sessionCart->options()->delete();
                $sessionCart->inputs()->delete();
                $sessionCart->delete();
                continue;
            }

            $memberCarts = self::where('member_seq', $memberSeq)
                ->where('goods_seq', $sessionCart->goods_seq)
                ->with(['options', 'inputs'])
                ->get();

            $existingCart = null;
            foreach ($memberCarts as $memberCart) {
                $memberOption = $memberCart->options->first();
                if (!$memberOption) continue;

                if (
                    (string)$memberOption->option1 !== (string)$sessionOption->option1 ||
                    (string)$memberOption->option2 !== (string)$sessionOption->option2 ||
                    (string)$memberOption->option3 !== (string)$sessionOption->option3 ||
                    (string)$memberOption->option4 !== (string)$sessionOption->option4 ||
                    (string)$memberOption->option5 !== (string)$sessionOption->option5
                ) {
                    continue;
                }

                if ($memberCart->inputs->count() !== $sessionCart->inputs->count()) {
                    continue;
                }

                $inputsMatch = true;
                foreach ($sessionCart->inputs as $sInput) {
                    $found = $memberCart->inputs->first(function($mInput) use ($sInput) {
                        return (string)$mInput->input_title === (string)$sInput->input_title &&
                               (string)$mInput->input_value === (string)$sInput->input_value;
                    });
                    if (!$found) {
                        $inputsMatch = false;
                        break;
                    }
                }

                if ($inputsMatch) {
                    $existingCart = $memberCart;
                    break;
                }
            }

            if ($existingCart) {
                $existingOption = $existingCart->options->first();
                $existingOption->ea += $sessionOption->ea;
                $existingOption->save();

                $sessionCart->options()->delete();
                $sessionCart->inputs()->delete();
                $sessionCart->delete();
            } else {
                $sessionCart->member_seq = $memberSeq;
                $sessionCart->session_id = '';
                $sessionCart->save();
            }
        }
    }
}
