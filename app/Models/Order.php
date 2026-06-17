<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_state_id',
        'total_price',
        'total_shipping_charge',
        'weight_for_mango',
        'weight_for_non_mango'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderState()
    {
        return $this->belongsTo(OrderState::class);
    }

    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function orderedProducts()
    {
        return $this->hasMany(OrderedProduct::class);
    }

    public function weightCalculationAndDatabaseUpdate()
    {
        $weightForMango = 0;
        $weightForNonMango = 0;

        foreach ($this->orderedProducts as $orderedProduct) {
            $productWeight = $orderedProduct->totalWeight();

            if ($orderedProduct->product->is_mango) {
                $weightForMango += $productWeight;
            } else {
                $weightForNonMango += $productWeight;
            }
        }

        $this->weight_for_mango = $weightForMango;
        $this->weight_for_non_mango = $weightForNonMango;
        $this->save();
    }

    public function calculateTotalShippingCharge(int $destinationDistrictId)
    {
        $weightForMango = $this->weight_for_mango;
        $weightForNonMango = $this->weight_for_non_mango;

        $CALCULATED_DELIVERY_CHARGE = 0;

        // Load the courier charges for inside an outside dhaka.
        $FIRST_HALF_KG_ISD = config('services.courier_charge.first_half_kg_isd');
        $FIRST_KG_ISD = config('services.courier_charge.first_kg_isd');
        $LATER_KGS_ISD = config('services.courier_charge.later_kgs_isd');

        $FIRST_HALF_KG_OSD = config('services.courier_charge.first_half_kg_osd');
        $FIRST_KG_OSD = config('services.courier_charge.first_kg_osd');
        $LATER_KGS_OSD = config('services.courier_charge.later_kgs_osd');
        $MANGO_DELIVERY_CHARGE_PER_KG = config('services.courier_charge.mango_delivery_charge_per_kg');

        // SUGGESTED MODIFICATION: Only calculate base charges if non-mango products exist
        if ($weightForNonMango > 0) {
            if ($weightForNonMango <= 0.5) {
                $weightForNonMango = 0.5;
            } elseif ($weightForNonMango > 0.5 && $weightForNonMango <= 1.00) {
                $weightForNonMango = 1.00;
            } elseif ($weightForNonMango > 1.00) {
                $weightForNonMango = ceil($weightForNonMango);
            }

            // CALCULATE DELIVERY CHARGE FOR NON-MANGO PRODUCTS
            if ($destinationDistrictId == 1) { // Assuming 1 is inside Dhaka
                if ($weightForNonMango == 0.5) {
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_HALF_KG_ISD;
                } elseif ($weightForNonMango == 1.00) {
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_KG_ISD;
                } elseif ($weightForNonMango > 1.00) {
                    $otherKgs = $weightForNonMango - 1;
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_KG_ISD + $otherKgs * $LATER_KGS_ISD;
                }
            } else {
                // outside Dhaka district
                if ($weightForNonMango == 0.5) {
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_HALF_KG_OSD;
                } elseif ($weightForNonMango == 1.00) {
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_KG_OSD;
                } elseif ($weightForNonMango > 1.00) {
                    $otherKgs = $weightForNonMango - 1;
                    $CALCULATED_DELIVERY_CHARGE = $FIRST_KG_OSD + $otherKgs * $LATER_KGS_OSD;
                }
            }
        }

        // Add Mango specific delivery charge
        if ($weightForMango > 0) {
            $CALCULATED_DELIVERY_CHARGE += $weightForMango * $MANGO_DELIVERY_CHARGE_PER_KG;
        }

        $this->total_shipping_charge = $CALCULATED_DELIVERY_CHARGE;
        $this->save();
    }
}
