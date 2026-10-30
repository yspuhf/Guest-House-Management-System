@extends('layouts.app')

@section('title', 'Add Physical Room')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-door-closed me-2"></i> Register New Physical Room</h5>
                <a href="{{ route('masters.rooms.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('masters.rooms.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Target Guest House <span class="text-danger">*</span></label>
                            <select name="guest_house_id" class="form-select" required>
                                @foreach($guestHouses as $gh)
                                    <option value="{{ $gh->id }}" {{ old('guest_house_id', request('guest_house_id')) == $gh->id ? 'selected' : '' }}>{{ $gh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Room Classification / Type <span class="text-danger">*</span></label>
                            <select name="room_type_id" class="form-select" required>
                                @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}" {{ old('room_type_id') == $rt->id ? 'selected' : '' }}>{{ $rt->name }} (Base: ₹{{ $rt->default_tariff }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Room Number / Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. VIP-105 / Room 204" value="{{ old('room_number') }}" required>
                            <div class="form-text">Must be unique within the selected guest house.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Floor / Wing</label>
                            <input type="text" name="floor" class="form-control" placeholder="e.g. Ground Floor / East Wing" value="{{ old('floor', 'Ground Floor') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Specific Tariff Override (INR)</label>
                            <input type="number" step="0.01" name="tariff" class="form-control" placeholder="Leave blank to use type default" value="{{ old('tariff') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Max Occupancy (Persons) <span class="text-danger">*</span></label>
                            <input type="number" name="max_occupancy" class="form-control" value="{{ old('max_occupancy', 2)" min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Publication State</label>
                            <select name="publication_status" class="form-select" required>
                                <option value="published" selected>Published (Available for allocation)</option>
                                <option value="draft">Draft (Unpublished)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Maintenance State</label>
                            <select name="maintenance_status" class="form-select" required>
                                <option value="operational" selected>Operational</option>
                                <option value="under_maintenance">Under Maintenance</option>
                                <option value="cleaning_in_progress">Cleaning in Progress</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Room Amenities / Facilities</label>
                            <div class="row g-2">
                                @foreach(['Air Conditioner (AC)', 'Geyser / Hot Water', 'LED Smart TV', 'High-Speed Wi-Fi', 'Study Desk', 'Mini Refrigerator', 'Balcony / Mountain View', 'Attached Western Bath'] as $fac)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $fac }}" id="fac_{{ md5($fac) }}" checked>
                                            <label class="form-check-label small" for="fac_{{ md5($fac) }}">{{ $fac }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Internal Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Special care, proximity to lift, etc.">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Room
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

