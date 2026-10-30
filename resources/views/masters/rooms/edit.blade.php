@extends('layouts.app')

@section('title', 'Edit Room ' . $room->room_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-pencil-square me-2"></i> Edit Physical Room: {{ $room->room_number }}</h5>
                <a href="{{ route('masters.rooms.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('masters.rooms.update', $room) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Target Guest House <span class="text-danger">*</span></label>
                            <select name="guest_house_id" class="form-select" required>
                                @foreach($guestHouses as $gh)
                                    <option value="{{ $gh->id }}" {{ old('guest_house_id', $room->guest_house_id) == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Room Classification / Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" class="form-select" required>
                                @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}" {{ old('room_type_id', $room->room_type_id) == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Room Number <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Floor / Wing</label>
                            <input type="text" name="floor" class="form-control" value="{{ old('floor', $room->floor) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Specific Tariff Override (INR)</label>
                            <input type="number" step="0.01" name="tariff" class="form-control" value="{{ old('tariff', $room->tariff) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Max Occupancy <span class="text-danger">*</span></label>
                            <input type="number" name="max_occupancy" class="form-control" value="{{ old('max_occupancy', $room->max_occupancy) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Publication State</label>
                            <select name="publication_status" class="form-select" required>
                                <option value="published" {{ $room->publication_status === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ $room->publication_status === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Maintenance Operational State</label>
                            <select name="maintenance_status" class="form-select" required>
                                <option value="operational" {{ $room->maintenance_status === 'operational' ? 'selected' : '' }}>Operational</option>
                                <option value="under_maintenance" {{ $room->maintenance_status === 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                <option value="cleaning_in_progress" {{ $room->maintenance_status === 'cleaning_in_progress' ? 'selected' : '' }}>Cleaning in Progress</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Active Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" {{ $room->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$room->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Room Amenities / Facilities</label>
                            <div class="row g-2">
                                @php
                                    $currentFacs = $room->facilities ?? [];
                                @endphp
                                @foreach(['Air Conditioner (AC)', 'Geyser / Hot Water', 'LED Smart TV', 'High-Speed Wi-Fi', 'Study Desk', 'Mini Refrigerator', 'Balcony / Mountain View', 'Attached Western Bath'] as $fac)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $fac }}" id="fac_{{ md5($fac) }}" {{ in_array($fac, $currentFacs) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="fac_{{ md5($fac) }}">{{ $fac }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Internal Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $room->notes) }}</textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Update Room Details
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

