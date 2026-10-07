<!-- Reusable car form fields -->
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Car Name *</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Toyota Camry 2023" required>
    </div>
    <div class="form-group">
        <label class="form-label">Brand *</label>
        <input type="text" name="brand" class="form-control" placeholder="e.g. Toyota" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Model *</label>
        <input type="text" name="model" class="form-control" placeholder="e.g. Camry" required>
    </div>
    <div class="form-group">
        <label class="form-label">Year *</label>
        <input type="number" name="year" class="form-control" min="2000" max="2030" placeholder="2023" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">License Plate *</label>
        <input type="text" name="license_plate" class="form-control" placeholder="ABC-1234" required>
    </div>
    <div class="form-group">
        <label class="form-label">Seats</label>
        <input type="number" name="seats" class="form-control" min="2" max="15" value="5">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Category</label>
        <select name="category" class="form-control">
            <option value="economy">Economy</option>
            <option value="sedan">Sedan</option>
            <option value="suv">SUV</option>
            <option value="luxury">Luxury</option>
            <option value="van">Van</option>
            <option value="sports">Sports</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Fuel Type</label>
        <select name="fuel_type" class="form-control">
            <option value="petrol">Petrol</option>
            <option value="diesel">Diesel</option>
            <option value="electric">Electric</option>
            <option value="hybrid">Hybrid</option>
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Transmission</label>
        <select name="transmission" class="form-control">
            <option value="automatic">Automatic</option>
            <option value="manual">Manual</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="available">Available</option>
            <option value="maintenance">Maintenance</option>
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-label">Price Per Day ($) *</label>
        <input type="number" name="price_per_day" class="form-control" step="0.01" min="0" placeholder="50.00" required>
    </div>
    <div class="form-group">
        <label class="form-label">Fine Per Day ($)</label>
        <input type="number" name="fine_per_day" class="form-control" step="0.01" min="0" value="15.00">
    </div>
</div>
<div class="form-group">
    <label class="form-label">Image URL</label>
    <input type="url" name="image_url" class="form-control" placeholder="https://...">
</div>
<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Car description..."></textarea>
</div>
