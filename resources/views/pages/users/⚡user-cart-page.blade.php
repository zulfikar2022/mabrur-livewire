<?php

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    public User $user;

    public function mount(User $user)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to view your cart.');
            return redirect()->route('login');
        }

        if ($user->id !== Auth::id()) {
            session()->flash('error', 'Unauthorized access! You cannot view another user\'s shopping cart.');
            return redirect()->route('home');
        }

        $this->user = $user;
    }

    /**
     * Computed Property: Fetches cart items strictly verifying availability rules
     */
    public function getCartItemsProperty()
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
     * Increments product volume based on product unit strategy profile type mapping
     */
    public function incrementQuantity($cartId)
    {
        $cartItem = Cart::findOrFail($cartId);

        if ($cartItem->product->sell_by_piece) {
            $cartItem->quantity += 1;
        } else {
            // Float precision mapping step fix for 100g weight increments
            $cartItem->quantity = round($cartItem->quantity + 0.1, 2);
        }

        $cartItem->save();
        $this->dispatch('cart-updated');
    }

    /**
     * Decrements product volume, strictly respecting minimum bounds constraints
     */
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

    /**
     * Validates manual key input changes, forcing numerical integrity profiles
     */


    /**
     * Safely drops an explicit record row line from active session scopes
     */
    public function removeItem($cartId)
    {
        $cartItem = Cart::where('user_id', $this->user->id)->where('id', $cartId)->firstOrFail();
        $cartItem->delete();

        $this->dispatch('cart-updated');
    }

    /**
     * Helper to compute combined active cart currency summaries
     */
    public function getCartTotal()
    {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $price = $item->product->sell_by_piece ? $item->product->price_per_piece : $item->product->price_per_kg;
            $total += ($item->quantity * $price);
        }
        return $total;
    }
};
?>

<?php
// PHP Backend code remains perfectly identical to your provided file
?>

<div x-data="{ openItemDeleteModal: null, productNameToDelete: '' }" class="max-w-7xl mx-auto p-4 md:p-6 my-6">
    
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <div class="lg:col-span-9 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                <h2 class="text-xl font-black text-gray-800 tracking-tight flex items-center gap-2 mb-6">
                    <i class="fa-solid fa-cart-shopping text-blue-600"></i>
                    <span>Shopping Cart Basket</span>
                </h2>

                <div class="divide-y divide-gray-100">
                    @forelse($this->cartItems as $item)
                        <div wire:key="cart-row-{{ $item->id }}" class="py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 first:pt-0 last:pb-0">
                            
                            <div class="flex items-center space-x-4 min-w-0 flex-1">
                                <div class="w-20 h-20 bg-gray-50 border border-gray-100 rounded-lg overflow-hidden shrink-0 shadow-inner relative">
                                    @php $img = $item->product->productImages->first(); @endphp
                                    @if($img)
                                        <img src="{{ asset('storage/' . $img->image_link) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"><i class="fa-solid fa-image text-xl"></i></div>
                                    @endif
                                </div>

                                <div class="truncate space-y-0.5">
                                    <h4 class="font-bold text-gray-900 text-base hover:text-blue-600 transition-colors truncate">
                                        <a href="#">{{ $item->product->name }}</a>
                                    </h4>
                                    <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded">
                                        {{ $item->product->category->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-6 sm:gap-8 shrink-0">
                                
                                <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50 p-1 shadow-sm">
                                    <button type="button" 
                                            wire:click="decrementQuantity({{ $item->id }})"
                                            {{ $item->quantity <= ($item->product->sell_by_piece ? 1 : 0.1) ? 'disabled' : '' }}
                                            class="w-8 h-8 rounded-md bg-white hover:bg-gray-100 text-gray-600 font-bold flex items-center justify-center transition-colors shadow-sm cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>

                                    <input type="text" 
                                           value="{{ $item->product->sell_by_piece ? intval($item->quantity) : number_format($item->quantity, 1) }}"
                                           disabled
                                           class="w-14 text-center bg-transparent border-0 font-bold text-sm text-gray-800 focus:outline-none focus:ring-0 select-none cursor-not-allowed">

                                    <button type="button" 
                                            wire:click="incrementQuantity({{ $item->id }})"
                                            class="w-8 h-8 rounded-md bg-white hover:bg-gray-100 text-gray-600 font-bold flex items-center justify-center transition-colors shadow-sm cursor-pointer">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <div class="text-right min-w-22.5">
                                    @php
                                        $price = $item->product->sell_by_piece ? $item->product->price_per_piece : $item->product->price_per_kg;
                                        $rowTotal = $item->quantity * $price;
                                    @endphp
                                    <p class="font-black text-gray-900 text-base">৳{{ number_format($rowTotal, 2) }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium">
                                        ৳{{ number_format($price, 2) }} / {{ $item->product->sell_by_piece ? 'piece' : 'kg' }}
                                    </p>
                                </div>

                                <button type="button" 
                                        @click="openItemDeleteModal = {{ $item->id }}; productNameToDelete = '{{ e($item->product->name) }}'"
                                        class="text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition-colors cursor-pointer">
                                        <i class="fa-solid fa-trash-can text-base"></i>
                                </button>

                            </div>

                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400">
                            <i class="fa-solid fa-basket-shopping text-5xl text-gray-200 mb-3 block"></i>
                            <p class="font-bold text-gray-600">Your shopping cart is empty</p>
                            <p class="text-xs text-gray-400 mt-0.5">Add items from the store directory to see them listed here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4 tracking-tight">Order Summary</h3>
                
                <div class="space-y-3 text-sm pb-4 border-b border-gray-100">
                    <div class="flex justify-between text-gray-500">
                        <span>Distinct Products</span>
                        <span class="font-semibold text-gray-800">{{ $this->cartItems->count() }}</span>
                    </div>
                </div>

                <div class="pt-4 flex items-baseline justify-between mb-6">
                    <span class="text-sm font-bold text-gray-800">Total Payable</span>
                    <span class="text-2xl font-black text-blue-600">৳{{ number_format($this->getCartTotal(), 2) }}</span>
                </div>

                <div class="bg-slate-50 border border-dashed border-slate-200 rounded-xl p-4 text-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Shipping & Address Modules</p>
                    <p class="text-[11px] text-slate-400 mt-1">This workspace slot is ready for your checkout address modules.</p>
                </div>
            </div>
        </div>

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
            
            <div @click.stop
                 x-show="openItemDeleteModal !== null"
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
                            Are you sure you want to remove <span class="font-bold text-gray-800 break-words" x-text="productNameToDelete"></span> from your shopping basket? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" 
                            @click="openItemDeleteModal = null"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-xl transition-colors cursor-pointer border border-gray-200 shadow-sm">
                        Cancel
                    </button>
                    
                    <button type="button" 
                            @click="
                                let targetId = openItemDeleteModal; 
                                openItemDeleteModal = null; 
                                setTimeout(() => { $wire.removeItem(targetId); }, 200);
                            "
                            class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-xl transition-colors cursor-pointer shadow-md shadow-red-600/10 flex items-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Yes, Remove</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>