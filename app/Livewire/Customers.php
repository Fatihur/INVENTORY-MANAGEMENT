<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Customers extends Component
{
    use WithPagination;

    public $search = '';
    public $status = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $showModal = false;
    public $showViewModal = false;
    public $customerId = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $tax_id = '';
    public $credit_limit = 0;
    public $payment_terms = 0;
    public $is_active = true;
    public $notes = '';

    public $addresses = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:customers,email',
        'phone' => 'nullable|string|max:50',
        'tax_id' => 'nullable|string|max:50',
        'credit_limit' => 'nullable|numeric|min:0',
        'payment_terms' => 'nullable|integer|min:0',
    ];

    public function render()
    {
        $query = Customer::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== 'all') {
            $query->where('is_active', $this->status === 'active');
        }

        $customers = $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.customers.index', [
            'customers' => $customers
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'customerId', 'name', 'email', 'phone', 'tax_id',
            'credit_limit', 'payment_terms', 'is_active', 'notes', 'addresses'
        ]);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $customer = Customer::findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->tax_id = $customer->tax_id;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->is_active = $customer->is_active;
        $this->notes = $customer->notes;
        $this->addresses = $customer->addresses->toArray();

        $this->showModal = true;
    }

    public function view($id)
    {
        $this->resetForm();
        $customer = Customer::with('addresses', 'salesOrders')->findOrFail($id);

        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->tax_id = $customer->tax_id;
        $this->credit_limit = $customer->credit_limit;
        $this->payment_terms = $customer->payment_terms;
        $this->is_active = $customer->is_active;
        $this->notes = $customer->notes;
        $this->addresses = $customer->addresses->toArray();

        $this->showViewModal = true;
    }

    public function save()
    {
        if ($this->customerId) {
            $this->rules['email'] = 'nullable|email|unique:customers,email,' . $this->customerId;
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone,
            'tax_id' => $this->tax_id,
            'credit_limit' => $this->credit_limit,
            'payment_terms' => $this->payment_terms,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
            $customer->update($data);
            $this->dispatch('customer-updated');
        } else {
            $customer = Customer::create($data);
            $this->dispatch('customer-created');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $customer->delete();
            $this->dispatch('customer-deleted');
        }
    }

    public function addAddress()
    {
        $this->addresses[] = [
            'type' => 'billing',
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => 'Indonesia',
            'is_default' => false,
        ];
    }

    public function removeAddress($index)
    {
        unset($this->addresses[$index]);
        $this->addresses = array_values($this->addresses);
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }
}
