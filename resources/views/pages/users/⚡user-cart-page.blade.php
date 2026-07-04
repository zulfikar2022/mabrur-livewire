<?php

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderedProduct;
use App\Models\OrderState;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class () extends Component {
    public User $user;

    // --- Checkout Form Properties ---
    public $division_id = '';
    public $district_id = '';
    public $upazila_id = '';
    public $address = '';
    public $phone = '';
    public $second_phone = '';

    // --- Dependent Dropdown Collections ---
    public $divisions = [];
    public $districts = [];
    public $upazilas = [];

    // --- Validation Rules ---
    protected function rules()
    {
        return [
            'division_id'  => 'required|exists:divisions,id',
            'district_id'  => 'required|exists:districts,id',
            'upazila_id'   => 'required|exists:upazilas,id',
            'address'      => 'required|string|max:500',
            // Bangladeshi Phone Regex: Must start with 01 followed by 3-9 and exactly 8 more digits
            'phone'        => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'second_phone' => ['nullable', 'regex:/^01[3-9]\d{8}$/'],
        ];
    }

    protected $messages = [
        'division_id.required' => 'Please select a division.',
        'district_id.required' => 'Please select a district.',
        'upazila_id.required'  => 'Please select an upazila.',
        'phone.regex'          => 'Please enter a valid 11-digit BD phone number (e.g., 017...).',
        'second_phone.regex'   => 'Please enter a valid 11-digit BD phone number.',
    ];

    public function mount(User $user)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to view your cart.');
            return redirect()->route('login');
        }

        if ($user->id !== Auth::id()) {
            session()->flash('error', 'Unauthorized access! You cannot view another user\'s shopping cart.');
            return redirect()->route('guest.home');
        }

        $this->user = $user;
        // Load initial divisions for the dropdown
        $this->divisions = Division::orderBy('name')->get();
    }

    // --- Dependent Dropdown Lifecycle Hooks ---
    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->orderBy('name')->get();
        // Reset child fields when parent changes
        $this->district_id = '';
        $this->upazilas = [];
        $this->upazila_id = '';
    }

    public function updatedDistrictId($value)
    {
        $this->upazilas = Upazila::where('district_id', $value)->orderBy('name')->get();
        $this->upazila_id = '';
    }

    /**
     * Computed Property: Fetches valid cart items
     */
    #[Computed]
    public function cartItems()
    {
        return Cart::where('user_id', $this->user->id)
            ->with(['product.productImages', 'product.category'])
            ->whereHas('product', function ($query) {
                $query->where('is_available', true)
                      ->whereHas('category', function ($catQuery) {
                          $catQuery->where('is_available', true);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Computed Property: Real-time calculation of shipping charge for the UI
     */
    #[Computed]
    public function shippingCharge()
    {
        if (empty($this->district_id)) {
            return 0;
        }

        $weightForMango = 0;
        $weightForNonMango = 0;

        foreach ($this->cartItems as $item) {
            $productWeight = ($item->product->weight_per_piece ?? 0) * $item->quantity;

            if ($item->product->is_mango) {
                $weightForMango += $productWeight;
            } else {
                $weightForNonMango += $productWeight;
            }
        }

        $charge = 0;

        // Non-Mango Items Logic
        if ($weightForNonMango > 0) {
            if ($weightForNonMango <= 0.5) {
                $weightForNonMango = 0.5;
            } elseif ($weightForNonMango > 0.5 && $weightForNonMango <= 1.00) {
                $weightForNonMango = 1.00;
            } elseif ($weightForNonMango > 1.00) {
                $weightForNonMango = ceil($weightForNonMango);
            }

            if ($this->district_id == 1) { // Inside Dhaka
                if ($weightForNonMango == 0.5) {
                    $charge += config('services.courier_charge.first_half_kg_isd', 60);
                } elseif ($weightForNonMango == 1.00) {
                    $charge += config('services.courier_charge.first_kg_isd', 70);
                } else {
                    $charge += config('services.courier_charge.first_kg_isd', 70) + (($weightForNonMango - 1) * config('services.courier_charge.later_kgs_isd', 15));
                }
            } else { // Outside Dhaka
                if ($weightForNonMango == 0.5) {
                    $charge += config('services.courier_charge.first_half_kg_osd', 110);
                } elseif ($weightForNonMango == 1.00) {
                    $charge += config('services.courier_charge.first_kg_osd', 130);
                } else {
                    $charge += config('services.courier_charge.first_kg_osd', 130) + (($weightForNonMango - 1) * config('services.courier_charge.later_kgs_osd', 25));
                }
            }
        }

        // Mango Logic
        if ($weightForMango > 0) {
            $charge += $weightForMango * config('services.courier_charge.mango_delivery_charge_per_kg', 15);
        }

        return $charge;
    }

    public function incrementQuantity($cartId)
    {
        $cartItem = Cart::findOrFail($cartId);

        if ($cartItem->product->sell_by_piece) {
            $cartItem->quantity += 1;
        } else {
            $cartItem->quantity = round($cartItem->quantity + 0.1, 2);
        }

        $cartItem->save();
        $this->dispatch('cart-updated');
    }

    public function decrementQuantity($cartId)
    {
        $cartItem = Cart::findOrFail($cartId);
        $step = $cartItem->product->sell_by_piece ? 1 : 0.1;

        if ($cartItem->quantity > $step) {
            $cartItem->quantity = $cartItem->product->sell_by_piece
                ? $cartItem->quantity - 1
                : round($cartItem->quantity - 0.1, 2);

            $cartItem->save();
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($cartId)
    {
        $cartItem = Cart::where('user_id', $this->user->id)->where('id', $cartId)->firstOrFail();
        $cartItem->delete();

        $this->dispatch('cart-updated');
        session()->flash('success', 'Item removed from your cart.');
    }

    public function getCartTotal()
    {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $price = $item->product->sell_by_piece ? $item->product->price_per_piece : $item->product->price_per_kg;
            $total += ($item->quantity * $price);
        }
        return $total;
    }

    /**
     * Core Order Processing Method
     */
    public function placeOrder()
    {
        // 1. Validate the form inputs
        $this->validate();

        $cartItems = $this->cartItems;
        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Your cart is empty or contains unavailable products.');
            return;
        }

        // a user cannot place more than 3 orders in a single day
        $todayOrdersCount = Order::where('user_id', $this->user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayOrdersCount >= 3) {
            session()->flash('error', 'You cannot place more than 3 orders in a single day.');
            return;
        }

        // 2. Wrap database inserts in a transaction to prevent partial data creation
        DB::beginTransaction();

        try {
            $pendingState = OrderState::where('name', OrderState::PENDING)->firstOrFail();

            // STEP A: Create the Order
            $order = Order::create([
                'user_id'               => $this->user->id,
                'order_state_id'        => $pendingState->id,
                'total_price'           => $this->getCartTotal(),
                'total_shipping_charge' => 0, // Will be updated by model methods below
            ]);

            // STEP B: Create Ordered Products
            foreach ($cartItems as $item) {
                $unitPrice = $item->product->sell_by_piece ? $item->product->price_per_piece : $item->product->price_per_kg;

                OrderedProduct::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item->product_id,
                    'unit_price'      => $unitPrice,
                    'quantity'        => $item->quantity,
                    'price'           => $unitPrice * $item->quantity,
                    'shipping_charge' => 0,
                ]);
            }

            // STEP C: Create Order Address
            OrderAddress::create([
                'order_id'     => $order->id,
                'division_id'  => $this->division_id,
                'district_id'  => $this->district_id,
                'upazila_id'   => $this->upazila_id,
                'address'      => $this->address,
                'phone'        => $this->phone,
                'alternative_phone' => $this->second_phone,
            ]);

            // STEP D: Calculate accurate shipping charge & weights using the Model logic
            $order->weightCalculationAndDatabaseUpdate();
            $order->calculateTotalShippingCharge($this->district_id);

            DB::commit();

            // Reset checkout form state
            $this->reset(['division_id', 'district_id', 'upazila_id', 'address', 'phone', 'second_phone', 'districts', 'upazilas']);

            session()->flash('success', 'Your order has been placed successfully!');
            return redirect()->route('user.my.orders');

        } catch (\Exception $e) {
            DB::rollBack();
            // log the erorr
            Log::error('Order Placement Failed: ' . $e->getMessage(), [
                'user_id' => $this->user->id,
                'cart_items' => $cartItems->pluck('id'),
                'form_data' => [
                    'division_id' => $this->division_id,
                    'district_id' => $this->district_id,
                    'upazila_id' => $this->upazila_id,
                    'address' => $this->address,
                    'phone' => $this->phone,
                    'second_phone' => $this->second_phone,
                ],
            ]);
            session()->flash('error', 'Something went wrong while processing your order. Please try again.');
        }
    }
};
?>

<x-slot:title>
    Cart - {{ config('app.name') }}
</x-slot>

<div x-data="{ openItemDeleteModal: null, productNameToDelete: '', openConfirmOrderModal: false }" class="max-w-7xl mx-auto p-4 md:p-6 my-6">
    
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        
        <div class="lg:col-span-8 xl:col-span-9 space-y-2">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                <h2 class="text-xl font-black text-gray-800 tracking-tight flex items-center gap-2 mb-6">
                    <i class="fa-solid fa-cart-shopping text-blue-600"></i>
                    <span>Shopping Cart Basket</span>
                </h2>

                <div class="divide-y divide-gray-100">
                    @forelse($this->cartItems as $item)
                        <div wire:key="cart-row-{{ $item->id }}" class="py-5 flex flex-col md:flex-row items-start sm:items-center justify-between gap-4 first:pt-0 last:pb-0">
                            
                            <div class="flex items-center space-x-4 min-w-0 flex-1">
                                <div class="w-20 h-20 bg-gray-50 border border-gray-100 rounded-lg overflow-hidden shrink-0 shadow-inner relative">
                                    @php $img = $item->product->productImages->first(); @endphp
                                    @if($img)
                                        <img src="{{ config('services.imagekit.url_endpoint') . $img->image_link }}?tr=w-300,h-300,fo-auto" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"><i class="fa-solid fa-image text-xl"></i></div>
                                    @endif
                                </div>

                                <div class="space-y-1 min-w-0 pr-2 sm:pr-4"> 
                                    <p class="font-bold text-[13px] text-gray-900 text-base hover:text-blue-600 transition-colors wrap-break-word leading-tight">
                                        <a wire:navigate href="{{ route('guest.product.details', ['product' => $item->product->id, 'productName' => $item->product->nameModifier()]) }}">{{ $item->product->name }}</a>
                                    </p>
                                    <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded mt-0.5">
                                        {{ $item->product->category->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-2 md:gap-6 sm:gap-8 shrink-0">
                                <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50 p-1 shadow-sm">
                                    <button type="button" wire:click="decrementQuantity({{ $item->id }})" {{ $item->quantity <= ($item->product->sell_by_piece ? 1 : 0.1) ? 'disabled' : '' }} class="w-8 h-8 rounded-md bg-white hover:bg-gray-100 text-gray-600 font-bold flex items-center justify-center transition-colors shadow-sm cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>
                                    <input type="text" value="{{ $item->product->sell_by_piece ? intval($item->quantity) : number_format($item->quantity, 1) }}" disabled class="w-14 text-center bg-transparent border-0 font-bold text-sm text-gray-800 focus:outline-none focus:ring-0 select-none cursor-not-allowed">
                                    <button type="button" wire:click="incrementQuantity({{ $item->id }})" class="w-8 h-8 rounded-md bg-white hover:bg-gray-100 text-gray-600 font-bold flex items-center justify-center transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <div class="text-right min-w-22.5">
                                    @php
                                        $price = $item->product->sell_by_piece ? $item->product->price_per_piece : $item->product->price_per_kg;
                                        $rowTotal = $item->quantity * $price;
                                    @endphp
                                    <p class="font-black text-gray-900 text-base">৳{{ number_format($rowTotal, 2) }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium">৳{{ number_format($price, 2) }} / {{ $item->product->sell_by_piece ? 'piece' : 'kg' }}</p>
                                </div>

                                <button type="button" @click="openItemDeleteModal = {{ $item->id }}; productNameToDelete = '{{ e($item->product->name) }}'" class="text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-trash-can text-base text-red-500"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-basket-shopping text-5xl text-gray-200 mb-3 block"></i>
                            <p class="font-bold text-gray-600">Your shopping cart is empty</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 xl:col-span-3 space-y-2">
            
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 tracking-tight">Order Summary</h3>
                <div class="space-y-3 text-sm pb-4 border-b border-gray-100">
                    <div class="flex justify-between text-gray-500">
                        <span>Products Price</span>
                        <span class="font-semibold text-gray-800">৳{{ number_format($this->getCartTotal(), 2) }}</span>
                    </div>
                    <!-- UPDATE: Dynamic Delivery Charge Display -->
                    <div class="flex justify-between items-center text-gray-500">
                        <span>Delivery Charge</span>
                        @if($district_id)
                            <span class="font-semibold text-gray-800 text-base">৳{{ number_format($this->shippingCharge, 2) }}</span>
                        
                        @else
                            <span class="font-semibold text-gray-800 text-xs text-right max-w-32.5 leading-tight bg-gray-100 p-1.5 rounded-lg border border-gray-200">
                                Select district to calculate
                            </span>
                        @endif
                    </div>
                    @if($district_id)
                        <div>
                            <span class="text-[10px] text-gray-500">
                                *ডেলিভারি চার্জ পরিবর্তিত হতে পারে।
                            </span>
                        </div>
                    @endif
                </div>
                <div class="pt-4 flex items-baseline justify-between">
                    <span class="text-sm font-bold text-gray-800">Total Payable</span>
                    <!-- UPDATE: Dynamic Total Payable Display -->
                    @if($district_id)
                        <span class="text-lg font-extrabold text-gray-900">৳{{ number_format($this->getCartTotal() + $this->shippingCharge, 2) }}</span>
                    @else
                        <span class="text-sm font-bold text-gray-800 text-right max-w-37.5 leading-tight bg-gray-100 p-1.5 rounded-lg border border-gray-200">
                            Select district to calculate
                        </span>
                    @endif
                </div>
            </div>

            @if($this->cartItems->count() > 0)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                    <p class="text-gray-800 underline">Order Product On COD</p>
                    <h3 class="text-base font-bold text-gray-800 mb-4 tracking-tight">Shipping Information</h3>
                    
                    <form class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Division <span class="text-red-500">*</span></label>
                            <select wire:model.live="division_id" class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Select Division</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                            @error('division_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">District <span class="text-red-500">*</span></label>
                            <!-- Note: Because this uses wire:model.live, changing it automatically recalculates shipping! -->
                            <select wire:model.live="district_id" class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($districts) ? 'disabled' : '' }}>
                                <option value="">Select District</option>
                                @foreach($districts as $dis)
                                    <option value="{{ $dis->id }}">{{ $dis->name }}</option>
                                @endforeach
                            </select>
                            @error('district_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Upazila <span class="text-red-500">*</span></label>
                            <select wire:model="upazila_id" class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($upazilas) ? 'disabled' : '' }}>
                                <option value="">Select Upazila</option>
                                @foreach($upazilas as $upa)
                                    <option value="{{ $upa->id }}">{{ $upa->name }}</option>
                                @endforeach
                            </select>
                            @error('upazila_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" wire:model="phone" placeholder="017XXXXXXXX" class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            @error('phone') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Alternative Phone (Optional)</label>
                            <input type="tel" wire:model="second_phone" placeholder="019XXXXXXXX" class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            @error('second_phone') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Full Address <span class="text-red-500">*</span></label>
                            <textarea wire:model="address" rows="3" placeholder="Village, Union, Road/House No..." class="w-full text-sm p-2 border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm"></textarea>
                            @error('address') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-100">
                            <button type="button" 
                                    @click="openConfirmOrderModal = true"
                                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm py-3 px-4 rounded-xl transition-colors shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                <span>Place Order Now</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

    </div>

    <!-- Modals remaining unchanged but hidden for brevity... -->
    <div wire:ignore>
        
        <div x-show="openItemDeleteModal !== null" 
             @click="openItemDeleteModal = null"
             class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
            <div @click.stop x-show="openItemDeleteModal !== null" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 relative overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-1.5 bg-red-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0 shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Remove Item?</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">Are you sure you want to remove <span class="font-bold text-gray-800 wrap-break-word" x-text="productNameToDelete"></span> from your basket?</p>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="openItemDeleteModal = null" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors border shadow-sm">Cancel</button>
                    <button type="button" @click="let targetId = openItemDeleteModal; openItemDeleteModal = null; setTimeout(() => { $wire.removeItem(targetId); }, 200);" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-md flex items-center gap-2"><i class="fa-solid fa-trash-can text-xs"></i><span>Remove</span></button>
                </div>
            </div>
        </div>

        <div x-show="openConfirmOrderModal" 
             @click="openConfirmOrderModal = false"
             class="fixed inset-0 z-99999 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
            <div @click.stop x-show="openConfirmOrderModal" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 relative overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-1.5 bg-blue-500"></div>
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 shadow-inner">
                        <i class="fa-solid fa-clipboard-check text-xl"></i>
                    </div>
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Confirm Your Order</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Are you ready to place this order? Once confirmed, our team will begin processing it for delivery.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="openConfirmOrderModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors border shadow-sm cursor-pointer">Review Details</button>
                    <button type="button" @click="openConfirmOrderModal = false; setTimeout(() => { $wire.placeOrder(); }, 200);" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-md flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i><span>Yes, Confirm Order</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>