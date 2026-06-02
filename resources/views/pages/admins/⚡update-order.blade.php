<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\OrderedProduct;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public Order $order;

    // Address Fields
    public $division_id = '';
    public $district_id = '';
    public $upazila_id = '';
    public $address = '';

    // Collections for Dependent Dropdowns
    public $divisions = [];
    public $districts = [];
    public $upazilas = [];

    // Order Items (key: ordered_product_id, value: quantity)
    public $orderItems = [];

    protected function rules()
    {
        return [
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'upazila_id'  => 'required|exists:upazilas,id',
            'address'     => 'required|string|max:500',
            'orderItems.*' => 'required|numeric|min:0.1',
        ];
    }

    public function mount(Order $order)
    {
        // Load the order with necessary relationships
        $this->order = $order->load([
            'orderAddress',
            'orderedProducts.product'
        ]);

        // Populate Address State
        $this->division_id = $this->order->orderAddress->division_id;
        $this->district_id = $this->order->orderAddress->district_id;
        $this->upazila_id = $this->order->orderAddress->upazila_id;
        $this->address = $this->order->orderAddress->address;

        // Load Dropdown Data
        $this->divisions = Division::orderBy('name')->get();

        if ($this->division_id) {
            $this->districts = District::where('division_id', $this->division_id)->orderBy('name')->get();
        }

        if ($this->district_id) {
            $this->upazilas = Upazila::where('district_id', $this->district_id)->orderBy('name')->get();
        }

        // Populate Order Items State
        foreach ($this->order->orderedProducts as $item) {
            $this->orderItems[$item->id] = $item->quantity;
        }
    }

    // --- Dependent Dropdown Lifecycle Hooks ---
    public function updatedDivisionId($value)
    {
        $this->districts = District::where('division_id', $value)->orderBy('name')->get();
        $this->district_id = ''; // Reset child
        $this->upazilas = [];    // Clear grandchildren
        $this->upazila_id = '';  // Reset grandchild
    }

    public function updatedDistrictId($value)
    {
        $this->upazilas = Upazila::where('district_id', $value)->orderBy('name')->get();
        $this->upazila_id = ''; // Reset child
    }

    public function updateOrder()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            // 1. Update the Order Address (Excluding Phone Numbers as requested)
            $this->order->orderAddress->update([
                'division_id' => $this->division_id,
                'district_id' => $this->district_id,
                'upazila_id'  => $this->upazila_id,
                'address'     => $this->address,
            ]);

            // 2. Update Ordered Products & Calculate New Total
            $newTotalPrice = 0;

            foreach ($this->orderItems as $orderedProductId => $quantity) {
                $orderedProduct = OrderedProduct::with('product')->find($orderedProductId);

                if ($orderedProduct) {
                    // Force proper numerical formatting based on product type
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

            // 3. Update the Master Order Record Total Price
            $this->order->update([
                'total_price' => $newTotalPrice,
            ]);

            DB::commit();

            session()->flash('success', 'Order updated successfully!');

            // Refresh data from DB to reflect strictly formatted changes (e.g., if admin typed 1.5 for a 'piece' product, it saves as 1 and refreshes the UI)
            $this->mount($this->order->fresh());

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Something went wrong while updating: ' . $e->getMessage());
        }
    }
};
?>

<div class="max-w-5xl mx-auto p-4 md:p-6 my-6 space-y-6">
    
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

    <form wire:submit.prevent="updateOrder" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 border-b pb-4 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-box text-blue-600"></i> Adjust Quantities
                </h3>

                <div class="space-y-5">
                    @foreach($order->orderedProducts as $item)
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 text-sm">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-500">
                                    Unit Price: ৳{{ number_format($item->unit_price, 2) }} 
                                    ({{ $item->product->sell_by_piece ? 'Per Piece' : 'Per Kg' }})
                                </p>
                            </div>
                            
                            <div class="w-24 shrink-0">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Quantity</label>
                                <input type="number" 
                                       wire:model="orderItems.{{ $item->id }}" 
                                       step="{{ $item->product->sell_by_piece ? '1' : '0.1' }}"
                                       class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm px-3 py-2">
                                @error('orderItems.'.$item->id) <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-50 bg-gray-50 p-3 rounded-xl text-sm text-gray-600 text-center">
                    Current Master Order Total: <span class="font-bold text-gray-900">৳{{ number_format($order->total_price, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 border-b pb-4 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-truck text-blue-600"></i> Edit Shipping Destination
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Division</label>
                        <select wire:model.live="division_id" class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">Select Division</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                        @error('division_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">District</label>
                        <select wire:model.live="district_id" class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($districts) ? 'disabled' : '' }}>
                            <option value="">Select District</option>
                            @foreach($districts as $dis)
                                <option value="{{ $dis->id }}">{{ $dis->name }}</option>
                            @endforeach
                        </select>
                        @error('district_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Upazila</label>
                        <select wire:model="upazila_id" class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm" {{ empty($upazilas) ? 'disabled' : '' }}>
                            <option value="">Select Upazila</option>
                            @foreach($upazilas as $upa)
                                <option value="{{ $upa->id }}">{{ $upa->name }}</option>
                            @endforeach
                        </select>
                        @error('upazila_id') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Full Address Line</label>
                        <textarea wire:model="address" rows="3" class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:ring-blue-500 focus:border-blue-500 shadow-sm"></textarea>
                        @error('address') <span class="text-[10px] text-red-500 font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Changes & Recalculate Totals</span>
            </button>
        </div>
        
    </form>
</div>