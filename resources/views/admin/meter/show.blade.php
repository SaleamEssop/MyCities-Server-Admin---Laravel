@extends('admin.layouts.main')
@section('title', 'Sites')
<style>
    .status {
        color: white;
        font-size: 90% !important;
    }
</style>
@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 custom-text-heading">Meter</h1>


        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Meter Details</h6>
                <a href="{{ route('account.edit-account', $account->id) }}" class="text-decoration-none"
                    style="color: #fdd600; font-size: x-large;">
                    <i class="fas fa-edit"></i>
                </a>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Left Column for Details -->
                    <div class="col-md-6">
                        <!-- Display account details -->
                        <div class="form-group">
                            <p><strong>Account Holder:</strong> {{ $account->user->name }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Account Name:</strong> {{ $account->account_name }}</p>
                        </div>
                        <div class="form-group">
                            <p>
                                <strong>Tariff Template:</strong>
                                <span class="badge badge-primary"
                                    style="font-size: medium;">{{ $meter->account->property->cost->template_name ?? 'No template' }}</span>
                            </p>
                        </div>
                        <div class="form-group">
                            <p><strong>Meter Title:</strong> {{ $meter->meter_title }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Meter Category:</strong> {{ $meter->meterCategory->name }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Billing Period:</strong> {{ $propertyBillingPeriod }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Current Month Usages:</strong> {{ $totalReadingDifference }}</p>
                        </div>

                        <div class="form-group">
                            <p><strong>Current Month Reading:</strong> {{ $latestReading->reading_value ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <p><strong>Account Holder Phone:</strong> {{ $account->user->contact_number }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Account Number:</strong> {{ $account->account_number }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Meter Number:</strong> {{ $meter->meter_number }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Meter Type:</strong> {{ $meter->meterTypes->title }}</p>
                        </div>
                        <div class="form-group">
                            <p><strong>Current Month Payment:</strong> R <span
                                    class="status badge bg-success">{{ number_format($currentPaymentsAmount, 2) }}</span>

                            </p>
                        </div>
                        <div class="form-group">
                            <p><strong>Overdue Payment:</strong> R <span
                                    class="status badge bg-danger">{{ number_format($totalPendingAmount, 2) }}</span>
                            </p>
                        </div>
                        <div class="form-group">
                            <p><strong>Previous Month Usages:</strong>
                                {{ $previousCycleTotalUsage ?? '-' }}
                            </p>
                        </div>
                        <div class="form-group">
                            <p><strong>Previous Month Reading:</strong>
                                {{ $previousMonthLatestReading->reading_value ?? '-' }}
                            </p>
                        </div>
                    </div>



                </div>
            </div>
        </div>



        <h1 class="h3 mb-2 custom-text-heading">Meter Readings</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">

            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">List of readings recorded under meters</h6>
                <div>

                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#meterModal"
                        data-meter-id="{{ $meter->id }}">
                        <i class="fas fa-plus"></i> Create
                    </button>

                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="reading-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Added By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Meter Reading</th>
                                <th>Meter Reading Date</th>
                                <th>Added By</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            @foreach ($meter->readings as $meterReading)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $meterReading->reading_value ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('m-d-Y', $meterReading->reading_date)->format('d M Y') }}
                                    </td>
                                    <td>
                                        {{ $meterReading->addedBy->name ?? '-' }}</td>

                                    {{-- <td>
                                                @if ($meterReading->reading_image)
                                                    <img src="{{ asset( $meterReading->reading_image) }}" alt="Reading Image" width="50" height="50" style="object-fit: cover; border-radius: 5px;">
                                                @else
                                                    No Image
                                                @endif
                                            </td> --}}
                                    <td>
                                        <a href="{{ url('admin/meter-reading/edit/' . $meterReading->id) }}"
                                            class="btn btn-warning btn-circle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ url('admin/meter-reading/delete/' . $meterReading->id) }}"
                                            onclick="return confirm('Are you sure you want to delete this meter reading?')"
                                            class="btn btn-danger btn-circle">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <h1 class="h3 mb-2 custom-text-heading"> Readings Cycles</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">List of readings cycles under meters</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="cost-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Start Reading</th>
                                <th>End Reading</th>
                                <th>Usage (Liters)</th>
                                <th>Daily Usage</th>
                                <th>Daily Cost (R)</th>
                                <th>VAT (R)</th>
                                <th>Consumption Charge (R)</th>
                                @if ($meter->meterTypes->title === 'Water')
                                    <th>Discharge Charge (R)</th>
                                    <th>Additional Costs</th>
                                    <th>Water Out Additional</th>
                                @endif
                                <th>Total Cost (R)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Start Reading</th>
                                <th>End Reading</th>
                                <th>Usage (Liters)</th>
                                <th>Daily Usage</th>
                                <th>Daily Cost (R)</th>
                                <th>VAT (R)</th>
                                <th>Consumption Charge (R)</th>
                                @if ($meter->meterTypes->title === 'Water')
                                    <th>Discharge Charge (R)</th>
                                    <th>Additional Costs</th>
                                    <th>Water Out Additional</th>
                                @endif
                                <th>Total Cost (R)</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
                        <tbody>

                            @foreach ($billingPeriods as $period)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}</td>
                                    <td>{{ $period['start_reading'] }}</td>
                                    <td>{{ $period['end_reading'] }}</td>
                                    <td>{{ number_format($period['usage_liters'], 2) }}</td>
                                    <td>{{ number_format($period['daily_usage'], 2) }}</td>
                                    <td>R {{ number_format($period['daily_cost'], 2) }}</td>
                                    <td>R {{ number_format($period['vat'], 2) }}</td>
                                    <td>R {{ number_format($period['consumption_charge'], 2) }}</td>
                                    @if ($meter->meterTypes->title === 'Water')
                                        <td>R {{ number_format($period['discharge_charge'], 2) }}</td>
                                        <td>
                                            @if (!empty($period['additional_costs']))
                                                @foreach ($period['additional_costs'] as $cost)
                                                    {{ $cost['title'] }}: R {{ number_format($cost['cost'], 2) }}<br>
                                                @endforeach
                                            @else
                                                None
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($period['water_out_additional']))
                                                @foreach ($period['water_out_additional'] as $cost)
                                                    {{ $cost['title'] }}: R {{ number_format($cost['cost'], 2) }}<br>
                                                @endforeach
                                            @else
                                                None
                                            @endif
                                        </td>
                                    @endif
                                    <td>R {{ number_format($period['cost'], 2) }}</td>
                                    <td>{{ $period['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <h1 class="h3 mb-2 custom-text-heading">Meter Payments</h1>


        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">List of payments recorded under meters</h6>

                <div class="d-flex align-items-center gap-3">
                    <!-- Billing Period Filter -->
                    <div class="d-flex align-items-center">
                        <label for="period" class="form-group fw-bold text-muted me-2 mb-0"
                            style="margin-right: 10px;">Billing Period:</label>

                        <select class="form-select shadow-sm bg-light border-0 rounded-pill" id="period"
                            style="height: 40px; width: 300px;" aria-label="Meter Reading Period">
                            <option selected>Select Meter Reading Period</option>
                            @foreach ($perviousBillingCycles as $cycle)
                                <option value="{{ $cycle['value'] }}">{{ $cycle['start_date'] }} -
                                    {{ $cycle['end_date'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="d-flex align-items-center" style="margin-left: 15px;">
                        <label for="payment-status" class="fw-bold text-muted me-2 mb-0"
                            style="margin-right: 10px;">Status:</label>
                        <select class="form-select shadow-sm bg-light border-0 rounded-pill" id="payment-status"
                            style="height: 40px; width: 300px;" aria-label="Payment Status">
                            <option selected>Payment Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>

                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="payment-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Billing Period</th>
                                <th>Total Usage</th>
                                <th>Meter Reading Date</th>
                                <th>Payment</th>
                                <th>Partially Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>#</th>
                                <th>Billing Period</th>
                                <th>Total Usage</th>
                                <th>Meter Reading Date</th>
                                <th>Payment</th>
                                <th>Partially Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        @if ($payment->billingPeriod)
                                            {{ $payment->billingPeriod->start_date->format('d M Y') }} -
                                            {{ $payment->billingPeriod->end_date->format('d M Y') }}
                                        @else
                                            No Billing Period
                                        @endif
                                    </td>
                                    <td>{{ $payment->billingPeriod->usage_liters ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('m-d-Y', $payment->reading->reading_date)->format('d M Y') }}
                                    </td>
                                    <td>{{ $payment->amount }}</td>
                                    <td>{{ $payment->paid_amount }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td>
                                        <span
                                            style="padding: 3px 8px; border-radius: 12px; font-size: 0.9em; {{ $payment->status == 'partially_paid' ? 'background-color: #d3d3d3; color: #000000;' : ($payment->status == 'pending' ? 'background-color: #FDD600; color: #212529;' : ($payment->status == 'paid' ? 'background-color: #28a745; color: #fff;' : 'background-color: #6c757d; color: #fff;')) }}">
                                            {{ $payment->status == 'partially_paid' ? 'Partially Paid' : $payment->status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{-- <button class="btn btn-warning btn-circle make-payment-btn" data-bs-toggle="modal"
                                            data-bs-target="#paymentModal" data-reading-id="{{ $meterReading->id }}"
                                            data-amount="{{ $payment->amount }}" data-status="{{ $payment->status }}">
                                            <i class="fas fa-edit"></i>
                                        </button> --}}
                                        <button class="btn btn-success btn-circle make-payment-btn" data-bs-toggle="modal"
                                            data-bs-target="#paymentModal" data-payment-id="{{ $payment->id }}"
                                            data-amount="{{ $payment->amount }}" data-status="{{ $payment->status }}"
                                            data-payment-date="{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') }}">
                                            <i class="fas fa-money-bill"></i>
                                        </button>
                                        <form action="{{ route('destroy-meter-payment', $payment->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-circle" onclick="return confirm('Are you sure you want to delete this meter reading?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->


    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('make-meter-payment') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="payment_id" id="payment_id">
                        <input type="hidden" name="reading_id" id="reading_id">

                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" required
                                step="0.01">
                            <input type="hidden" class="form-control" id="actual_amount" name="actual_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Payment Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Select Payment Status</option>
                                <option value="paid">Paid</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_date">Payment Date</label>
                            <input type="text" class="payment_date form-control" id="payment_date"
                                name="payment_date" required placeholder="MM/DD/YYYY">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Make Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Meter Modal -->
    <div class="modal fade" id="meterModal" tabindex="-1" role="dialog" aria-labelledby="meterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('add-meter-reading') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="meter_id" value="{{ $meter->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="meterModalLabel">Add Meter Reading</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label><strong>Meter Reading Image:</strong></label>
                            <input type="file" class="form-control" name="reading_image" />
                        </div>
                        <div class="form-group">
                            <label><strong>Reading Date:</strong></label>
                            <input type="date" class="payment_date form-control"
                                placeholder="Enter meter reading date" name="reading_date" required>
                        </div>
                        @if ($meter->meterTypes->title === 'Water')
                            <div class="form-group">
                                <label><strong>Reading Value (8 digits):</strong></label>
                                <input type="text" class="form-control" placeholder="Enter meter reading value"
                                    name="reading_value" id="reading_value" required maxlength="8">
                            </div>
                        @elseif ($meter->meterTypes->title === 'Electricity')
                            <div class="form-group">
                                <label><strong>Reading Value (6 digits):</strong></label>
                                <input type="text" class="form-control" placeholder="Enter meter reading value"
                                    name="reading_value" id="reading_value" required maxlength="6">
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




@endsection

@section('page-level-scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script type="text/javascript">
        $(document).ready(function() {
            $('#payment-dataTable').dataTable();
            $('#cost-dataTable').dataTable();
            $('#meter-dataTable').dataTable();
            $('#reading-dataTable').dataTable();

        });
    </script>
    {{-- script code for open modal  --}}
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var paymentButtons = document.querySelectorAll(".make-payment-btn");

            var readingIdInput = document.getElementById("reading_id");
            var paymentIdInput = document.getElementById("payment_id");
            var amountInput = document.getElementById("amount");
            var actualAmountInput = document.getElementById("actual_amount");
            var statusInput = document.getElementById("status");
            var paymentDateInput = document.getElementById("payment_date");

            paymentButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    var readingId = this.getAttribute("data-reading-id");
                    var paymentId = this.getAttribute("data-payment-id");
                    var amount = this.getAttribute("data-amount");
                    var actualAmount = this.getAttribute("data-amount");
                    var status = this.getAttribute("data-status");
                    var paymentDate = this.getAttribute("data-payment-date");


                    readingIdInput.value = '';
                    paymentIdInput.value = '';
                    amountInput.value = '';
                    actualAmountInput.value = '';
                    statusInput.value = '';
                    paymentDateInput.value = '';

                    if (paymentId) {
                        paymentIdInput.value = paymentId;
                        amountInput.value = amount;
                        actualAmountInput.value = actualAmount;
                        statusInput.value = status;
                        paymentDateInput.value = paymentDate;
                    } else if (readingId) {
                        readingIdInput.value = readingId;

                    }
                });
            });
        });
    </script>

    {{-- add meter reading script --}}
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var readingValueInput = document.getElementById("reading_value");

            if (readingValueInput) {
                var meterType = "{{ $meter->meterTypes->title }}";

                if (meterType === 'Water') {
                    readingValueInput.setAttribute("maxlength", "8");
                } else if (meterType === 'Electricity') {
                    readingValueInput.setAttribute("maxlength", "6");
                }

                readingValueInput.addEventListener("input", function() {
                    var value = this.value;
                    if (meterType === 'Water' && value.length > 8) {
                        this.setCustomValidity('Water meter reading can only be 8 digits.');
                    } else if (meterType === 'Electricity' && value.length > 6) {
                        this.setCustomValidity('Electricity meter reading can only be 6 digits.');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>


    {{-- filter payments by status script  --}}
    <script>
        $(document).ready(function() {

            function applyFilters() {
                let selectedStatus = $("#payment-status").val().toLowerCase();

                let rowCount = $("#payment-dataTable tbody tr").length;


                $("#payment-dataTable tbody tr").each(function(index) {
                    let $row = $(this);
                    let rowStatus = $row.find("td:nth-child(8) span").text().trim().toLowerCase();


                    let showDueToStatus = true;

                    if (selectedStatus !== "payment status") {
                        if (selectedStatus === "paid") {
                            showDueToStatus = (rowStatus === "paid");
                        } else if (selectedStatus === "pending") {
                            showDueToStatus = (rowStatus === "pending" || rowStatus === "partially paid");
                        } else {
                            showDueToStatus = false;
                        }
                    }

                    $row.toggle(showDueToStatus);
                });
            }

            $("#payment-status").on("change", function() {
                applyFilters();
            });
        });
    </script>


    {{-- filter payments by period script --}}
    <script>
        $(document).ready(function() {
            function applyFilters() {

                let selectedPeriod = $("#period").val().trim();

                $("#payment-dataTable tbody tr").show();

                $("#payment-dataTable tbody tr").each(function() {
                    let $row = $(this);

                    let rowDateText = $row.find("td:nth-child(7)").text().trim();
                    let rowDate = new Date(rowDateText.replace(/(\d+) (\w+) (\d+)/, "$2 $1, $3"));

                    let showDueToDate = true;

                    if (selectedPeriod !== "select meter reading period") {
                        let [startDate, endDate] = selectedPeriod.split(" ");
                        startDate = new Date(startDate);
                        endDate = new Date(endDate);
                        showDueToDate = (rowDate >= startDate && rowDate <= endDate);
                    }
                    if (!(showDueToDate)) {
                        $row.hide();
                    }
                });
            }

            $("#period").on("change", applyFilters);
        });
    </script>



@endsection
