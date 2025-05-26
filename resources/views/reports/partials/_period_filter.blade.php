{{-- resources/views/reports/partials/_period_filter.blade.php --}}
{{-- Variabel yang WAJIB dikirim dari view pemanggil:
    $periodPrefix (string, contoh: '', 'creation_', 'activation_') 
    $periodData (array, berisi nilai-nilai filter periode saat ini, contoh: $request->all() atau array spesifik dari controller)
    $availableYears (array, [2023 => 2023, ...]) - Untuk dropdown tahun di filter 'yearly'
    $allMonthNames (array, [1 => 'Januari', 2 => 'Februari', ...]) - Untuk dropdown bulan di filter 'monthly'
--}}

@php
    // Mengambil nilai dari $periodData dengan fallback ke default jika tidak ada
    // Nama input di form akan menggunakan $periodPrefix
    $pType = $periodData[$periodPrefix . 'period_type'] ?? old($periodPrefix . 'period_type', 'all');
    $sDate =
        $periodData[$periodPrefix . 'selected_date'] ??
        old($periodPrefix . 'selected_date', \Carbon\Carbon::now()->toDateString());
    $sMonthYear =
        $periodData[$periodPrefix . 'selected_month_year'] ??
        old($periodPrefix . 'selected_month_year', \Carbon\Carbon::now()->format('Y-m'));
    $sYearOnly =
        $periodData[$periodPrefix . 'selected_year_only'] ??
        old($periodPrefix . 'selected_year_only', \Carbon\Carbon::now()->year);
    $cStartDate = $periodData[$periodPrefix . 'custom_start_date'] ?? old($periodPrefix . 'custom_start_date');
    $cEndDate = $periodData[$periodPrefix . 'custom_end_date'] ?? old($periodPrefix . 'custom_end_date');
@endphp

<div class="col-lg-auto col-md-4 col-sm-6 mb-2 mb-lg-0">
    <label for="{{ $periodPrefix }}period_type" class="form-label">Jenis Periode:</label>
    <select name="{{ $periodPrefix }}period_type" id="{{ $periodPrefix }}period_type"
        class="form-select form-select-sm rounded-pill period-type-select" data-prefix="{{ $periodPrefix }}">
        <option value="all" {{ $pType == 'all' ? 'selected' : '' }}>Semua Periode</option>
        <option value="monthly" {{ $pType == 'monthly' ? 'selected' : '' }}>Bulanan</option>
        <option value="daily" {{ $pType == 'daily' ? 'selected' : '' }}>Harian</option>
        <option value="weekly" {{ $pType == 'weekly' ? 'selected' : '' }}>Mingguan</option>
        <option value="yearly" {{ $pType == 'yearly' ? 'selected' : '' }}>Tahunan</option>
        <option value="custom" {{ $pType == 'custom' ? 'selected' : '' }}>Rentang Kustom</option>
    </select>
</div>

{{-- Input tanggal/bulan/tahun yang akan ditampilkan/disembunyikan oleh JS --}}
<div id="{{ $periodPrefix }}filter_daily" class="col-lg-auto col-md-4 col-sm-6 mb-2 mb-lg-0 period-specific-filter"
    style="display: {{ $pType == 'daily' ? 'block' : 'none' }};">
    <label for="{{ $periodPrefix }}selected_date" class="form-label">Tanggal:</label>
    <input type="date" name="{{ $periodPrefix }}selected_date" id="{{ $periodPrefix }}selected_date"
        class="form-control form-control-sm rounded-pill" value="{{ $sDate }}">
</div>
<div id="{{ $periodPrefix }}filter_monthly" class="col-lg-auto col-md-4 col-sm-6 mb-2 mb-lg-0 period-specific-filter"
    style="display: {{ $pType == 'monthly' ? 'block' : 'none' }};">
    <label for="{{ $periodPrefix }}selected_month_year" class="form-label">Bulan & Tahun:</label>
    <input type="month" name="{{ $periodPrefix }}selected_month_year" id="{{ $periodPrefix }}selected_month_year"
        class="form-control form-control-sm rounded-pill" value="{{ $sMonthYear }}">
</div>
<div id="{{ $periodPrefix }}filter_yearly" class="col-lg-auto col-md-4 col-sm-6 mb-2 mb-lg-0 period-specific-filter"
    style="display: {{ $pType == 'yearly' ? 'block' : 'none' }};">
    <label for="{{ $periodPrefix }}selected_year_only" class="form-label">Tahun:</label>
    <select name="{{ $periodPrefix }}selected_year_only" id="{{ $periodPrefix }}selected_year_only"
        class="form-select form-select-sm rounded-pill">
        @if (isset($availableYears) && count($availableYears) > 0)
            @foreach ($availableYears as $yearOptionValue => $yearOptionDisplay)
                <option value="{{ $yearOptionValue }}" {{ $sYearOnly == $yearOptionValue ? 'selected' : '' }}>
                    {{ $yearOptionDisplay }}
                </option>
            @endforeach
        @else
            <option value="{{ $sYearOnly }}">{{ $sYearOnly }}</option> {{-- Fallback jika $availableYears tidak ada --}}
        @endif
    </select>
</div>
<div id="{{ $periodPrefix }}filter_custom_range"
    class="col-lg-auto col-md-12 custom-date-range-picker row gx-2 gy-0 period-specific-filter"
    style="display: {{ $pType == 'custom' ? 'flex' : 'none' }}; padding-left:0; padding-right:0;">
    <div class="col-md-auto col-6 pe-1 mb-2 mb-md-0">
        <label for="{{ $periodPrefix }}custom_start_date" class="form-label">Dari Tanggal:</label>
        <input type="date" name="{{ $periodPrefix }}custom_start_date" id="{{ $periodPrefix }}custom_start_date"
            class="form-control form-control-sm rounded-pill" value="{{ $cStartDate }}">
    </div>
    <div class="col-md-auto col-6 ps-1 mb-2 mb-md-0">
        <label for="{{ $periodPrefix }}custom_end_date" class="form-label">Sampai Tanggal:</label>
        <input type="date" name="{{ $periodPrefix }}custom_end_date" id="{{ $periodPrefix }}custom_end_date"
            class="form-control form-control-sm rounded-pill" value="{{ $cEndDate }}">
    </div>
</div>

{{-- JavaScript untuk partial ini, diletakkan sekali saja di layout utama atau di push('scripts') @once --}}
@once('scripts_period_filter_handler')
    {{-- ID unik untuk @once --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.period-type-select').forEach(function(selectElement) {
                    const prefix = selectElement.dataset.prefix || '';
                    const filterDaily = document.getElementById(prefix + 'filter_daily');
                    const filterMonthly = document.getElementById(prefix + 'filter_monthly');
                    const filterYearly = document.getElementById(prefix + 'filter_yearly');
                    const filterCustomRange = document.getElementById(prefix + 'filter_custom_range');

                    function toggleDateFiltersVisibility() {
                        const selectedPeriod = selectElement.value;

                        if (filterDaily) filterDaily.style.display = 'none';
                        if (filterMonthly) filterMonthly.style.display = 'none';
                        if (filterYearly) filterYearly.style.display = 'none';
                        if (filterCustomRange) filterCustomRange.style.display = 'none';

                        if (selectedPeriod === 'daily' && filterDaily) filterDaily.style.display = 'block';
                        else if (selectedPeriod === 'monthly' && filterMonthly) filterMonthly.style.display =
                            'block';
                        else if (selectedPeriod === 'yearly' && filterYearly) filterYearly.style.display =
                            'block';
                        else if (selectedPeriod === 'custom' && filterCustomRange) filterCustomRange.style
                            .display = 'flex';
                    }

                    selectElement.addEventListener('change', toggleDateFiltersVisibility);
                    toggleDateFiltersVisibility();
                });
            });
        </script>
    @endpush
@endonce
