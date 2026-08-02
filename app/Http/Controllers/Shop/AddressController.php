<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        return view('shop.account.addresses', [
            'addresses' => $customer->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'full_address' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $customer = Auth::guard('customer')->user();

        if (! empty($data['is_default']) || $customer->addresses()->count() === 0) {
            $customer->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $customer->addresses()->create($data);

        return back()->with('status', 'Adresse ajoutée.');
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $this->authorizeOwnership($address);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'full_address' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['is_default'])) {
            $address->customer->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address->update($data);

        return back()->with('status', 'Adresse mise à jour.');
    }

    public function destroy(CustomerAddress $address)
    {
        $this->authorizeOwnership($address);

        $wasDefault = $address->is_default;
        $customer = $address->customer;
        $address->delete();

        if ($wasDefault) {
            $customer->addresses()->first()?->update(['is_default' => true]);
        }

        return back()->with('status', 'Adresse supprimée.');
    }

    private function authorizeOwnership(CustomerAddress $address): void
    {
        abort_unless($address->customer_id === Auth::guard('customer')->id(), 403);
    }
}
