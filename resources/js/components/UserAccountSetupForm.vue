<template>
    <div class="user-account-setup-wrapper">
        <!-- Progress Steps -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div v-for="(step, index) in steps" :key="index" 
                         class="step-indicator" 
                         :class="{ 'active': currentStep === index + 1, 'completed': currentStep > index + 1 }">
                        <div class="step-number">
                            <i v-if="currentStep > index + 1" class="fas fa-check"></i>
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <div class="step-label">{{ step }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Step {{ currentStep }}: {{ steps[currentStep - 1] }}</h6>
            </div>
            <div class="card-body">
                <!-- Step 1: User Details -->
                <div v-show="currentStep === 1">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="formData.name" placeholder="Enter full name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" v-model="formData.email" placeholder="Enter email address" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="formData.contact_number" placeholder="Enter phone number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" v-model="formData.password" placeholder="Enter password" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Region Selection -->
                <div v-show="currentStep === 2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Region <span class="text-danger">*</span></label>
                                <select class="form-control" v-model="formData.region_id" @change="onRegionChange" required>
                                    <option value="">Select a Region</option>
                                    <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Site Title</label>
                                <input type="text" class="form-control" v-model="formData.site_title" placeholder="Optional site title">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" v-model="formData.address" placeholder="Enter address">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Tariff Template Selection -->
                <div v-show="currentStep === 3">
                    <div v-if="loadingTemplates" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading tariff templates...</p>
                    </div>
                    <div v-else-if="tariffTemplates.length === 0" class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No active tariff templates found for the selected region. Please go back and select a different region, or create a tariff template first.
                    </div>
                    <div v-else class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tariff Template <span class="text-danger">*</span></label>
                                <select class="form-control" v-model="formData.tariff_template_id" required>
                                    <option value="">Select a Tariff Template</option>
                                    <option v-for="template in tariffTemplates" :key="template.id" :value="template.id">
                                        {{ template.template_name }}
                                        <span v-if="template.is_water">(Water)</span>
                                        <span v-if="template.is_electricity">(Electricity)</span>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12" v-if="selectedTemplate">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="font-weight-bold">Selected Template Details:</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ selectedTemplate.template_name }}</p>
                                    <p class="mb-1">
                                        <strong>Services:</strong> 
                                        <span v-if="selectedTemplate.is_water" class="badge badge-primary mr-1">Water</span>
                                        <span v-if="selectedTemplate.is_electricity" class="badge badge-warning">Electricity</span>
                                    </p>
                                    <p class="mb-0" v-if="selectedTemplate.start_date">
                                        <strong>Validity:</strong> {{ selectedTemplate.start_date }} to {{ selectedTemplate.end_date || 'Ongoing' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Account Details & Meters -->
                <div v-show="currentStep === 4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="formData.account_name" placeholder="Enter account name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="formData.account_number" placeholder="Enter account number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bill Day</label>
                                <input type="number" min="1" max="31" class="form-control" v-model="formData.bill_day" placeholder="1-31">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Read Day</label>
                                <input type="number" min="1" max="31" class="form-control" v-model="formData.read_day" placeholder="1-31">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Billing Type</label>
                                <select class="form-control" v-model="formData.billing_type">
                                    <option value="monthly">Monthly</option>
                                    <option value="bi-monthly">Bi-Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Meters Section -->
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-tachometer-alt mr-2"></i>Meters (Optional)</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" @click="addMeter">
                            <i class="fas fa-plus"></i> Add Meter
                        </button>
                    </div>
                    <div v-for="(meter, index) in formData.meters" :key="index" class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Meter {{ index + 1 }}</strong>
                                <button type="button" class="btn btn-outline-danger btn-sm" @click="removeMeter(index)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small">Type</label>
                                        <select class="form-control form-control-sm" v-model="meter.meter_type_id">
                                            <option value="">Select Type</option>
                                            <option v-for="type in meterTypes" :key="type.id" :value="type.id">{{ type.title }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small">Meter Title</label>
                                        <input type="text" class="form-control form-control-sm" v-model="meter.meter_title" placeholder="Title">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small">Meter Number</label>
                                        <input type="text" class="form-control form-control-sm" v-model="meter.meter_number" placeholder="Number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small">Initial Reading</label>
                                        <!-- Water Meter Input with Pigeonhole Style -->
                                        <div v-if="isMeterWater(meter.meter_type_id)" class="meter-input-wrapper">
                                            <div class="water-meter-input">
                                                <div class="meter-digits white-section">
                                                    <input type="text" 
                                                           maxlength="6" 
                                                           class="meter-digit-input"
                                                           v-model="meter.initial_reading_whole"
                                                           @input="formatWholeDigits(meter)"
                                                           placeholder="000000">
                                                </div>
                                                <span class="meter-decimal">.</span>
                                                <div class="meter-digits red-section">
                                                    <input type="text" 
                                                           maxlength="1" 
                                                           class="meter-digit-input"
                                                           v-model="meter.initial_reading_decimal"
                                                           @input="formatDecimalDigit(meter)"
                                                           placeholder="0">
                                                </div>
                                            </div>
                                            <small class="text-muted">Water: 6 digits + 1 decimal</small>
                                        </div>
                                        <!-- Standard Input for Electricity -->
                                        <input v-else type="number" class="form-control form-control-sm" v-model="meter.initial_reading" placeholder="Initial value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="formData.meters.length === 0" class="text-center text-muted py-3">
                        No meters added. Click "Add Meter" to add one.
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" @click="prevStep" :disabled="currentStep === 1">
                    <i class="fas fa-arrow-left mr-1"></i> Previous
                </button>
                <button v-if="currentStep < 4" type="button" class="btn btn-primary" @click="nextStep" :disabled="!canProceed">
                    Next <i class="fas fa-arrow-right ml-1"></i>
                </button>
                <button v-else type="button" class="btn btn-success" @click="submitForm" :disabled="saving || !canProceed">
                    <span v-if="saving"><i class="fas fa-spinner fa-spin"></i> Creating...</span>
                    <span v-else><i class="fas fa-check mr-1"></i> Create Account</span>
                </button>
            </div>
        </div>

        <!-- Notification Toast -->
        <div class="position-fixed" style="top: 20px; right: 20px; z-index: 9999;">
            <div v-if="notification.show" :class="['alert', 'alert-' + notification.type, 'alert-dismissible', 'fade', 'show']" role="alert">
                {{ notification.message }}
                <button type="button" class="close" @click="notification.show = false">
                    <span>&times;</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';

const props = defineProps({
    csrfToken: {
        type: String,
        required: true
    },
    regions: {
        type: Array,
        required: true
    },
    meterTypes: {
        type: Array,
        required: true
    },
    apiUrls: {
        type: Object,
        required: true
    }
});

const steps = ['User Details', 'Assign Region', 'Select Tariff', 'Create Account'];
const currentStep = ref(1);
const saving = ref(false);
const loadingTemplates = ref(false);
const tariffTemplates = ref([]);

const notification = reactive({
    show: false,
    type: 'success',
    message: ''
});

const formData = reactive({
    // Step 1
    name: '',
    email: '',
    contact_number: '',
    password: '',
    // Step 2
    region_id: '',
    site_title: '',
    address: '',
    // Step 3
    tariff_template_id: '',
    // Step 4
    account_name: '',
    account_number: '',
    bill_day: '',
    read_day: '',
    billing_type: 'monthly',
    meters: []
});

// Computed
const selectedTemplate = computed(() => {
    if (!formData.tariff_template_id) return null;
    return tariffTemplates.value.find(t => t.id === formData.tariff_template_id);
});

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return formData.name && formData.email && formData.contact_number && formData.password;
        case 2:
            return formData.region_id;
        case 3:
            return formData.tariff_template_id;
        case 4:
            return formData.account_name && formData.account_number;
        default:
            return false;
    }
});

// Methods
function showNotification(message, type = 'success') {
    notification.message = message;
    notification.type = type;
    notification.show = true;
    setTimeout(() => {
        notification.show = false;
    }, 5000);
}

function buildUrl(urlTemplate, replacements) {
    let url = urlTemplate;
    for (const [placeholder, value] of Object.entries(replacements)) {
        url = url.replace(placeholder, value);
    }
    return url;
}

async function onRegionChange() {
    if (!formData.region_id) {
        tariffTemplates.value = [];
        formData.tariff_template_id = '';
        return;
    }
    
    loadingTemplates.value = true;
    try {
        const url = buildUrl(props.apiUrls.getTariffTemplates, { '__REGION_ID__': formData.region_id });
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        if (data.status === 200) {
            tariffTemplates.value = data.data;
            formData.tariff_template_id = '';
        }
    } catch (error) {
        showNotification('Error loading tariff templates: ' + error.message, 'danger');
    } finally {
        loadingTemplates.value = false;
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function nextStep() {
    if (currentStep.value < 4 && canProceed.value) {
        currentStep.value++;
    }
}

function addMeter() {
    formData.meters.push({
        meter_type_id: '',
        meter_title: '',
        meter_number: '',
        initial_reading: '',
        initial_reading_whole: '',
        initial_reading_decimal: '',
        initial_reading_date: new Date().toISOString().split('T')[0]
    });
}

function removeMeter(index) {
    formData.meters.splice(index, 1);
}

function isMeterWater(meterTypeId) {
    if (!meterTypeId) return false;
    const type = props.meterTypes.find(t => t.id === meterTypeId);
    return type && type.title && type.title.toLowerCase() === 'water';
}

function formatWholeDigits(meter) {
    // Only allow digits, preserve leading zeros
    meter.initial_reading_whole = meter.initial_reading_whole.replace(/[^0-9]/g, '').slice(0, 6);
    updateMeterReading(meter);
}

function formatDecimalDigit(meter) {
    // Only allow single digit
    meter.initial_reading_decimal = meter.initial_reading_decimal.replace(/[^0-9]/g, '').slice(0, 1);
    updateMeterReading(meter);
}

function updateMeterReading(meter) {
    // Combine whole and decimal parts for water meters
    if (meter.initial_reading_whole || meter.initial_reading_decimal) {
        const whole = meter.initial_reading_whole || '0';
        const decimal = meter.initial_reading_decimal || '0';
        meter.initial_reading = `${whole}.${decimal}`;
    } else {
        meter.initial_reading = '';
    }
}

async function submitForm() {
    if (!canProceed.value) {
        showNotification('Please fill in all required fields', 'danger');
        return;
    }
    
    saving.value = true;
    
    try {
        const response = await fetch(props.apiUrls.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            // Reset form after success
            setTimeout(() => {
                window.location.href = '/admin/user-accounts/manager';
            }, 1500);
        } else {
            showNotification(data.message || 'Error creating account', 'danger');
        }
    } catch (error) {
        showNotification('Error creating account: ' + error.message, 'danger');
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.user-account-setup-wrapper {
    max-width: 900px;
    margin: 0 auto;
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-indicator:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 15px;
    left: calc(50% + 20px);
    width: calc(100% - 40px);
    height: 2px;
    background: #e9ecef;
}

.step-indicator.completed:not(:last-child)::after {
    background: #1cc88a;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 5px;
    position: relative;
    z-index: 1;
}

.step-indicator.active .step-number {
    background: #4e73df;
    color: white;
}

.step-indicator.completed .step-number {
    background: #1cc88a;
    color: white;
}

.step-label {
    font-size: 0.85rem;
    color: #858796;
}

.step-indicator.active .step-label {
    color: #4e73df;
    font-weight: bold;
}

/* Water Meter Input Styles */
.water-meter-input {
    display: flex;
    align-items: center;
    gap: 2px;
}

.meter-digits {
    display: flex;
}

.meter-digits.white-section .meter-digit-input {
    background: #fff;
    color: #000;
    border: 1px solid #ced4da;
}

.meter-digits.red-section .meter-digit-input {
    background: #b30101;
    color: #fff;
    border: 1px solid #b30101;
}

.meter-digit-input {
    width: 80px;
    height: 32px;
    text-align: center;
    font-family: 'Courier New', monospace;
    font-weight: bold;
    font-size: 14px;
    border-radius: 4px;
    padding: 2px;
}

.meter-digits.red-section .meter-digit-input {
    width: 24px;
}

.meter-decimal {
    font-weight: bold;
    font-size: 18px;
    margin: 0 2px;
}

.alert {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
