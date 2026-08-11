{{-- Partial: Profile Alamat View --}}
                <div class="sm:col-span-2 alamat-view">
                    <div class="alamat-view__grid">
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Provinsi</p>
                            <p class="alamat-view__value" x-text="selectedProvinceName || savedProvinceName || 'â€”'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kota / Kabupaten</p>
                            <p class="alamat-view__value" x-text="selectedCityName || savedCityName || 'â€”'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kecamatan</p>
                            <p class="alamat-view__value" x-text="selectedDistrictName || savedDistrictName || 'â€”'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kode Pos</p>
                            <p class="alamat-view__value">{{ $user->pasienProfile?->kode_pos ?: 'â€”' }}</p>
                        </div>
                    </div>
                </div>
