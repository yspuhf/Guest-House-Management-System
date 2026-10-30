@extends('layouts.app')

@section('title', 'Global System Policies & Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i> Global System Policies & Settings</h4>
        <small class="text-muted">Configure payment collection stages, standard check-in/out hours, and university metadata</small>
    </div>
</div>

<form action="{{ route('masters.settings.update') }}" method="POST">
    @csrf

    <div class="row g-4">
        @foreach($settings as $group => $groupSettings)
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 fw-bold text-uppercase text-secondary small">
                        <i class="bi bi-gear-fill me-1"></i> {{ ucfirst($group) }} Configuration
                    </div>
                    <div class="card-body p-4">
                        @foreach($groupSettings as $stg)
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">{{ $stg->label }}</label>

                                @if($stg->type === 'select')
                                    @php
                                        $options = json_decode($stg->options, true) ?? [];
                                    @endphp
                                    <select name="{{ $stg->key }}" class="form-select">
                                        @foreach($options as $opt)
                                            <option value="{{ $opt }}" {{ $stg->value === $opt ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $opt)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif($stg->type === 'boolean')
                                    <select name="{{ $stg->key }}" class="form-select">
                                        <option value="1" {{ $stg->value == '1' ? 'selected' : '' }}>Enabled / Yes</option>
                                        <option value="0" {{ $stg->value == '0' ? 'selected' : '' }}>Disabled / No</option>
                                    </select>
                                @elseif($stg->type === 'time')
                                    <input type="time" name="{{ $stg->key }}" class="form-control" value="{{ $stg->value }}">
                                @elseif($stg->type === 'number')
                                    <input type="number" name="{{ $stg->key }}" class="form-control" value="{{ $stg->value }}">
                                @else
                                    <input type="text" name="{{ $stg->key }}" class="form-control" value="{{ $stg->value }}">
                                @endif

                                @if($stg->description)
                                    <div class="form-text small">{{ $stg->description }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">
            <i class="bi bi-save me-1"></i> Save Global System Settings
        </button>
    </div>
</form>
@endsection

