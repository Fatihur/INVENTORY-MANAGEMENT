<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?Supplier $supplier = null;

    public string $code = '';
    public string $name = '';
    public string $contact_person = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public int $default_lead_time_days = 7;
    public ?float $default_payment_terms = null;
    public bool $is_active = true;

    protected function rules()
    {
        return [
            'code' => 'required|string|max:20|unique:suppliers,code' . ($this->supplier ? ',' . $this->supplier->id : ''),
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'default_lead_time_days' => 'required|integer|min:1',
            'default_payment_terms' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function mount(?int $supplier = null)
    {
        if ($supplier) {
            $this->supplier = Supplier::findOrFail($supplier);
            $this->fill($this->supplier->toArray());
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'default_lead_time_days' => $this->default_lead_time_days,
            'default_payment_terms' => $this->default_payment_terms,
            'is_active' => $this->is_active,
        ];

        if ($this->supplier) {
            $this->supplier->update($data);
            session()->flash('message', 'Supplier updated successfully.');
        } else {
            Supplier::create($data);
            session()->flash('message', 'Supplier created successfully.');
        }

        return redirect()->route('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-form');
    }
}
