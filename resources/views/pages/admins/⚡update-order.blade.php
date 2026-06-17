<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\OrderedProduct;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public Order $order;

    // Address & Shipping Fields
    public $division_id = '';
    public $district_id = '';
    public $upazila_id = '';
    public $address = '';
    public $phone = '';
    public $alternative_phone = '';
    public $shipping_charge = 0;

    // Collections for Dependent Dropdowns
    public $divisions = [];
    public $districts = [];
    public $upazilas = [];
    public $availableProducts = [];

    // Order Items State
    public $orderItems = [];

    // Add New Product State
    public $new_product_id = '';
    public $new_product_quantity = 1;

    protected function rules()
    {
        return [
            'division_id'       => 'required|exists:divisions,id',
            'district_id'       => 'required|exists:districts,id',
            'upazila_id'        => 'required|exists:upazilas,id',
            'address'           => 'required|string|max:500',
            // Bangladeshi Phone Regex Validation
            'phone'             => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'alternative_phone' => ['nullable', 'regex:/^01[3-9]\d{8}$/'],
            'shipping_charge'   => 'required|numeric|min:0',
            'orderItems.*'      => 'required|numeric|min:0.1',
        ];
    }

    protected $messages = [
        'phone.regex'             => 'Please enter a valid 11-digit BD phone number.',
        'alternative_phone.regex' => 'Please enter a valid 11-digit BD phone number.',
    ];

    public function mount(Order $order)
    {
        $this->order = $order->load(['orderAddress', 'orderedProducts.product']);

        // Populate Address & Shipping State
        $this->division_id = $this->order->orderAddress->division_id;
        $this->district_id = $this->order->orderAddress->district_id;
        $this->upazila_id = $this->order->orderAddress->upazila_id;
        $this->address = $this->order->orderAddress->address;
        $this->phone = $this->order->orderAddress->phone;
        $this->alternative_phone = $this->order->orderAddress->alternative_phone;
        $this->shipping_charge = $this->order->total_shipping_charge;

        // Load Dropdown Data
        $this->divisions = Division::orderBy('name')->get();
        $this->availableProducts = Product::where('is_available', true)
            ->whereHas('category', function ($query) {
                $query->where('is_available', true);
            })
            ->orderBy('name')
            ->get();

        if ($this->division_id) {
            $this->districts = District::where('division_id', $this->division_id)->orderBy('name')->get();
        }

        if ($this->district_id) {
            $this->upazilas = Upazila::where('district_id', $this->district_id)->orderBy('name')->get();
        }

        $this->refreshOrderItemsState();
    }

    /**
     * Helper to keep the Livewire array synced with the DB relation
     */
    private function refreshOrderItemsState()
    {
        $this->order->load('orderedProducts.product');
        $this->orderItems = [];
        foreach ($this->order->orderedProducts as $item) {
            $this->orderItems[$item->id] = $item->quantity;
        }
    }

    // --- Dependent Dropdown Lifecycle Hooks ---
    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->orderBy('name')->get();
        $this->district_id = '';
        $this->upazilas = [];
        $this->upazila_id = '';
    }

    public function updatedDistrictId($value)
    {
        $this->upazilas = Upazila::where('district_id', $value)->orderBy('name')->get();
        $this->upazila_id = '';
    }

    // --- Dynamic Order Modification Methods ---

    public function autoRecalculateShipping()
    {
        if (!$this->district_id) {
            session()->flash('error', 'Please select a District first to calculate shipping.');
            return;
        }

        $this->order->weightCalculationAndDatabaseUpdate();
        $this->order->calculateTotalShippingCharge($this->district_id);

        // Update the local component state with the newly calculated DB value
        $this->shipping_charge = $this->order->fresh()->total_shipping_charge;
        session()->flash('success', 'Shipping charge recalculated based on current weights & district.');
    }

    public function removeProduct($orderedProductId)
    {
        $orderedProduct = OrderedProduct::find($orderedProductId);
        if ($orderedProduct) {
            $priceToDeduct = $orderedProduct->price;
            $orderedProduct->delete();

            // Update master order total and weights
            $this->order->update([
                'total_price' => max(0, $this->order->total_price - $priceToDeduct)
            ]);
            $this->order->weightCalculationAndDatabaseUpdate();

            $this->refreshOrderItemsState();
            session()->flash('success', 'Product removed from the order.');
        }
    }

    public function addProduct()
    {
        $this->validate([
            'new_product_id' => 'required|exists:products,id',
            'new_product_quantity' => 'required|numeric|min:0.1'
        ]);

        // Check if product already exists in this order
        $exists = $this->order->orderedProducts()->where('product_id', $this->new_product_id)->exists();
        if ($exists) {
            session()->flash('error', 'This product is already in the order! Please adjust its quantity above.');
            return;
        }

        $product = Product::find($this->new_product_id);
        $qty = $product->sell_by_piece ? max(1, intval($this->new_product_quantity)) : max(0.1, round(floatval($this->new_product_quantity), 2));
        $unitPrice = $product->sell_by_piece ? $product->price_per_piece : $product->price_per_kg;
        $linePrice = $unitPrice * $qty;

        OrderedProduct::create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'price' => $linePrice,
            'shipping_charge' => 0
        ]);

        // Update master order total and weights
        $this->order->update([
            'total_price' => $this->order->total_price + $linePrice
        ]);
        $this->order->weightCalculationAndDatabaseUpdate();

        // Reset Add Form
        $this->new_product_id = '';
        $this->new_product_quantity = 1;

        $this->refreshOrderItemsState();
        session()->flash('success', 'New product successfully added to the order!');
    }

    // --- Master Save Method ---
    public function updateOrder()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // 1. Update Address and Phones
            $this->order->orderAddress->update([
                'division_id'       => $this->division_id,
                'district_id'       => $this->district_id,
                'upazila_id'        => $this->upazila_id,
                'address'           => $this->address,
                'phone'             => $this->phone,
                'alternative_phone' => $this->alternative_phone,
            ]);

            // 2. Update Quantities of Existing Products
            $newTotalPrice = 0;
            foreach ($this->orderItems as $orderedProductId => $quantity) {
                $orderedProduct = OrderedProduct::with('product')->find($orderedProductId);

                if ($orderedProduct) {
                    $finalQty = $orderedProduct->product->sell_by_piece
                        ? max(1, intval($quantity))
                        : max(0.1, round(floatval($quantity), 2));

                    $lineTotal = $finalQty * $orderedProduct->unit_price;

                    $orderedProduct->update([
                        'quantity' => $finalQty,
                        'price'    => $lineTotal,
                    ]);

                    $newTotalPrice += $lineTotal;
                }
            }

            // 3. Update Master Order Totals & Shipping
            $this->order->update([
                'total_price' => $newTotalPrice,
                'total_shipping_charge' => $this->shipping_charge
            ]);

            // 4. Update Weights
            $this->order->weightCalculationAndDatabaseUpdate();

            DB::commit();

            session()->flash('success', 'Order updated successfully!');
            $this->mount($this->order->fresh());

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong while updating: ' . $e->getMessage());
        }
    }
};
?>

<div x-data="{ openItemDeleteModal: null, productNameToDelete: '' }" class="max-w-6xl mx-auto p-4 md:p-6 my-6 space-y-6">
    
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">
            Update Order #{{ $order->id }}
        </h1>
        <a href="{{ route('admin.order-details', $order->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">
            Back to Details
        </a>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-7 space-y-6">
            
            <form wire:submit.prevent="updateOrder" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 border-b pb-4 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-box text-blue-600"></i> Adjust or Remove Items
                </h3>

                <div class="space-y-4">
                    @forelse($order->orderedProducts as $item)
                        <div wire:key="item-{{ $item->id }}" class="flex items-center justify-between gap-4 p-3 bg-gray-50 border border-gray-100 rounded-xl">
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 text-sm">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    ৳{{ number_format($item->unit_price, 2) }} 
                                    ({{ $item->product->sell_by_piece ? 'Per Piece' : 'Per Kg' }}) 
                                    <span class="font-bold text-gray-700 ml-2">Line Total: ৳{{ number_format($item->price, 2) }}</span>
                                </p>
                            </div>
                            
                            <div class="w-24 shrink-0">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Qty</label>
                                <input type="number" 
                                        min="1"
                                       wire:model="orderItems.{{ $item->id }}" 
                                       step="{{ $item->product->sell_by_piece ? '1' : '1' }}"
                                       class="w-full text-sm rounded-lg border-gray-200 bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm px-3 py-1.5">
                                @error('orderItems.'.$item->id) <span class="text-[10px] text-red-500 font-medium block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <button type="button" @click="openItemDeleteModal = {{ $item->id }}; productNameToDelete = '{{ e($item->product->name) }}'" class="mt-4 shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-red-500 hover:bg-red-100 transition-colors cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-sm">
                            No products remaining in this order.
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Products Subtotal: <span class="font-black text-gray-900 text-lg ml-1">৳{{ number_format($order->total_price, 2) }}</span>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl transition-colors shadow-md text-sm">
                        Save Quantities
                    </button>
                </div>
            </form>

            <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100 p-6">
                <h3 class="font-bold text-blue-900 border-b border-blue-200 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Add Product to Order
                </h3>
                
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Select Product</label>
                        <select wire:model="new_product_id" class="w-full text-sm rounded-lg border-blue-200 p-2.5 bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">-- Choose Product --</option>
                            @foreach($availableProducts as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (৳{{ $prod->sell_by_piece ? $prod->price_per_piece : $prod->price_per_kg }})</option>
                            @endforeach
                        </select>
                        @error('new_product_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="w-full sm:w-28 shrink-0">
                        <label class="block text-xs font-semibold text-blue-800 mb-1">Quantity</label>
                        <input type="number" wire:model="new_product_quantity" step="1" min="1" class="w-full text-sm rounded-lg border-blue-200 p-2.5 bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @error('new_product_quantity') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <button type="button" wire:click="addProduct" class="w-full sm:w-auto mt-0 sm:mt-5 bg-white border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm text-sm shrink-0 cursor-pointer">
                        Add Item
                    </button>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="updateOrder" class="lg:col-span-5 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 border-b pb-4 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-blue-600"></i> Shipping Charge
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Manual Override Charge (৳)</label>
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                            <input type="number" wire:model="shipping_charge" step="1" class="flex-1 text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm font-bold text-gray-900">
                            
                            <button type="button" wire:click="autoRecalculateShipping" class="shrink-0 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold py-3 px-4 rounded-xl transition-colors shadow-sm text-xs flex items-center gap-2 cursor-pointer" title="Calculate based on weight and district">
                                <i class="fa-solid fa-calculator"></i> Auto-Calc
                            </button>
                        </div>
                        @error('shipping_charge') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex justify-between items-center text-sm">
                        <span class="font-semibold text-gray-600">Grand Total:</span>
                        <span class="font-black text-blue-600 text-xl">৳{{ number_format($order->total_price + $shipping_charge, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 border-b pb-4 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck text-blue-600"></i> Edit Destination
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Division <span class="text-red-500">*</span></label>
                        <select wire:model.live="division_id" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">Select Division</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                        @error('division_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">District <span class="text-red-500">*</span></label>
                        <select wire:model.live="district_id" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($districts) ? 'disabled' : '' }}>
                            <option value="">Select District</option>
                            @foreach($districts as $dis)
                                <option value="{{ $dis->id }}">{{ $dis->name }}</option>
                            @endforeach
                        </select>
                        @error('district_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Upazila <span class="text-red-500">*</span></label>
                        <select wire:model="upazila_id" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($upazilas) ? 'disabled' : '' }}>
                            <option value="">Select Upazila</option>
                            @foreach($upazilas as $upa)
                                <option value="{{ $upa->id }}">{{ $upa->name }}</option>
                            @endforeach
                        </select>
                        @error('upazila_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" wire:model="phone" placeholder="017XXXXXXXX" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @error('phone') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Alternative Phone</label>
                        <input type="tel" wire:model="alternative_phone" placeholder="019XXXXXXXX" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @error('alternative_phone') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Full Address Line <span class="text-red-500">*</span></label>
                        <textarea wire:model="address" rows="3" class="w-full text-sm rounded-lg border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm"></textarea>
                        @error('address') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save All Changes</span>
            </button>
        </form>
    </div>

    <div wire:ignore>
        <div x-show="openItemDeleteModal !== null" 
             @click="openItemDeleteModal = null"
             class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             x-cloak>
            
            <div @click.stop x-show="openItemDeleteModal !== null" 
                 x-transition:enter="transition ease-out duration-300 transform" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave="transition ease-in duration-200 transform" 
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0" 
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 relative overflow-hidden">
                
                <div class="absolute top-0 inset-x-0 h-1.5 bg-red-500"></div>
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0 shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Remove Item?</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Are you sure you want to remove <span class="font-bold text-gray-800 wrap-break-word" x-text="productNameToDelete"></span> from this order?
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="openItemDeleteModal = null" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors border shadow-sm cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" @click="let targetId = openItemDeleteModal; openItemDeleteModal = null; setTimeout(() => { $wire.removeProduct(targetId); }, 200);" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-md flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-trash-can text-xs"></i><span>Remove</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>