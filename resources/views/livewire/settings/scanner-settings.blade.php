<div>
    <div class="mb-3 d-flex justify-between align-center">
        <h2><i class="fas fa-barcode"></i> Hardware Scanner Settings</h2>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span>Scanner Configuration</span>
        </div>
        <div class="panel-body">
            <p class="mb-3 text-gray-600" style="color: #7f8c8d; font-size: 13px;">
                Configure how the application interprets input from hardware barcode/QR scanners.
                Hardware scanners typically act like a very fast keyboard, typing the scanned characters and following it with a specific key (like Enter).
            </p>

            <form wire:submit="save">
                <div class="form-group mb-3">
                    <label class="form-label cursor-pointer d-flex align-center gap-2">
                        <input type="checkbox" wire:model="scanner_enabled" value="1">
                        <strong>Enable Hardware Scanner Support</strong>
                    </label>
                    <small style="color: #95a5a6; display: block; margin-top: 4px;">If disabled, the application will not try to intercept rapid keyboard strokes as scanner input.</small>
                    @error('scanner_enabled') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3" style="max-width: 400px;">
                    <label class="form-label" for="scanner_timeout">Keystroke Timeout (ms)</label>
                    <input type="number" id="scanner_timeout" wire:model="scanner_timeout" class="form-control" min="10" max="1000">
                    <small style="color: #95a5a6; display: block; margin-top: 4px;">Maximun time between keystrokes to be considered a scanner. Human typing is usually > 100ms per key. Default is 50ms.</small>
                    @error('scanner_timeout') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group mb-3" style="max-width: 400px;">
                    <label class="form-label" for="scanner_suffix">Scanner Suffix Key</label>
                    <select id="scanner_suffix" wire:model="scanner_suffix" class="form-control">
                        <option value="Enter">Enter</option>
                        <option value="Tab">Tab</option>
                        <option value="None">None</option>
                    </select>
                    <small style="color: #95a5a6; display: block; margin-top: 4px;">The key that your scanner automatically presses at the end of a scan.</small>
                    @error('scanner_suffix') <span style="color: #e74c3c; font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    
                    <span wire:loading wire:target="save" class="ml-2" style="color: #3498db; font-size: 12px;">
                        <i class="fas fa-spinner fa-spin"></i> Saving...
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
