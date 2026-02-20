<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 14px; font-weight: bold;">QR Codes for {{ $product->name }}</h2>
        <div style="display: flex; gap: 10px;">
            <button wire:click="generateQr" class="btn btn-primary">Generate New QR</button>
            @if($qrCodes->count() > 0)
                <button wire:click="printLabels" class="btn btn-success" style="background-color: #27ae60; color: #fff;">Print Labels</button>
            @endif
            <a wire:navigate href="{{ route('products.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb;">{{ session('message') }}</div>
    @endif

    <div style="display: flex; flex-wrap: wrap; gap: 15px;">
        @foreach($qrCodes as $qr)
        <div style="background-color: #fff; border: 1px solid #bdc3c7; padding: 15px; text-align: center; width: 180px;">
            <div style="margin-bottom: 10px;">
                {!! app(\App\Contracts\Services\QrCodeServiceInterface::class)->getSvg($qr->qr_code_value) !!}
            </div>
            <p style="font-size: 10px; color: #7f8c8d;">{{ $qr->qr_code_value }}</p>
            <p style="font-size: 10px; color: #95a5a6;">Printed: {{ $qr->print_count }}x</p>
        </div>
        @endforeach
    </div>
</div>
