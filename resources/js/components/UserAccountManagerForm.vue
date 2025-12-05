<template>
    <div class="user-account-manager-wrapper">
        <!-- Search & Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Search & Filter Users</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" v-model="filters.name" @input="debounceSearch" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" v-model="filters.address" @input="debounceSearch" placeholder="Search by address...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" v-model="filters.phone" @input="debounceSearch" placeholder="Search by phone...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>User Type</label>
                            <select class="form-control" v-model="filters.user_type" @change="searchUsers">
                                <option value="">All Users</option>
                                <option value="real">Real Users</option>
                                <option value="test">Test Users (@test.com)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Users List</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Sites</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in usersList" :key="user.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.contact_number }}</td>
                                <td><span class="badge badge-primary">{{ user.sites_count || 0 }} site(s)</span></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm btn-circle mr-1" @click="viewUser(user.id)" title="View/Edit">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-circle" @click="confirmDeleteUser(user)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="usersList.length === 0">
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Details Modal -->
        <div class="modal fade" :class="{ show: showUserModal, 'd-block': showUserModal }" tabindex="-1" v-show="showUserModal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user mr-2"></i>
                            User Management - {{ selectedUser?.name }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeUserModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;" v-if="selectedUser">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'user' }" href="#" @click.prevent="activeTab = 'user'">
                                    <i class="fas fa-user mr-1"></i> User Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'accounts' }" href="#" @click.prevent="activeTab = 'accounts'">
                                    <i class="fas fa-file-invoice mr-1"></i> Accounts & Meters
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'readings' }" href="#" @click.prevent="activeTab = 'readings'">
                                    <i class="fas fa-tachometer-alt mr-1"></i> Add Readings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ active: activeTab === 'billing' }" href="#" @click.prevent="activeTab = 'billing'">
                                    <i class="fas fa-dollar-sign mr-1"></i> Billing (Coming Soon)
                                </a>
                            </li>
                        </ul>

                        <!-- User Details Tab -->
                        <div v-show="activeTab === 'user'">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="editUserData.name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" v-model="editUserData.email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" v-model="editUserData.contact_number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" v-model="editUserData.password" placeholder="Leave blank to keep current">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" @click="updateUser" :disabled="saving">
                                <i class="fas fa-save mr-1"></i> Save User Details
                            </button>
                        </div>

                        <!-- Accounts & Meters Tab -->
                        <div v-show="activeTab === 'accounts'">
                            <div v-for="site in selectedUser.sites" :key="site.id" class="card mb-3">
                                <div class="card-header">
                                    <strong><i class="fas fa-home mr-2"></i>{{ site.title || 'Site' }}</strong>
                                    <small class="text-muted ml-2">{{ site.address }}</small>
                                </div>
                                <div class="card-body">
                                    <div v-for="account in site.accounts" :key="account.id" class="border-left border-primary pl-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ account.account_name }}</strong>
                                                <span class="badge badge-secondary ml-2">{{ account.account_number }}</span>
                                            </div>
                                        </div>

                                        <!-- Meters List -->
                                        <div class="mt-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="font-weight-bold"><i class="fas fa-tachometer-alt mr-1"></i>Meters</small>
                                                <button type="button" class="btn btn-outline-success btn-sm" @click="openAddMeterModal(account.id)">
                                                    <i class="fas fa-plus"></i> Add Meter
                                                </button>
                                            </div>
                                            <div v-for="meter in account.meters" :key="meter.id" class="card bg-light mb-2">
                                                <div class="card-body py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="badge" :class="getMeterTypeBadgeClass(meter.meter_type_id)">
                                                                {{ getMeterTypeName(meter.meter_type_id) }}
                                                            </span>
                                                            <strong class="ml-2">{{ meter.meter_title }}</strong>
                                                            <small class="text-muted ml-2">#{{ meter.meter_number }}</small>
                                                        </div>
                                                        <div>
                                                            <button type="button" class="btn btn-info btn-sm mr-1" @click="viewReadings(meter)" title="View Readings">
                                                                <i class="fas fa-list"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" @click="deleteMeter(meter.id)" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="meter.readings && meter.readings.length > 0" class="mt-2">
                                                        <small class="text-muted">
                                                            Latest Reading: {{ meter.readings[0].reading_value }} on {{ meter.readings[0].reading_date }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="!account.meters || account.meters.length === 0" class="text-muted small">
                                                No meters for this account
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!selectedUser.sites || selectedUser.sites.length === 0" class="text-muted text-center py-3">
                                No sites found for this user
                            </div>
                        </div>

                        <!-- Add Readings Tab -->
                        <div v-show="activeTab === 'readings'">
                            <h6 class="font-weight-bold mb-3">Add New Reading</h6>
                            
                            <!-- Meter Selection -->
                            <div class="form-group">
                                <label>Select Meter <span class="text-danger">*</span></label>
                                <select class="form-control" v-model="newReading.meter_id" @change="onMeterSelectForReading">
                                    <option value="">Select a Meter</option>
                                    <template v-for="site in selectedUser.sites" :key="site.id">
                                        <template v-for="account in site.accounts" :key="account.id">
                                            <option v-for="meter in account.meters" :key="meter.id" :value="meter.id">
                                                {{ site.title }} / {{ account.account_name }} / {{ meter.meter_title }} ({{ getMeterTypeName(meter.meter_type_id) }})
                                            </option>
                                        </template>
                                    </template>
                                </select>
                            </div>

                            <div v-if="newReading.meter_id">
                                <!-- Previous Reading Info -->
                                <div v-if="previousReading" class="alert alert-info mb-3">
                                    <strong>Previous Reading:</strong> {{ previousReading.reading_value }} on {{ previousReading.reading_date }}
                                </div>

                                <!-- Date Input -->
                                <div class="form-group">
                                    <label>Reading Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" v-model="newReading.reading_date" :min="previousReading?.reading_date">
                                    <small v-if="previousReading" class="text-muted">Cannot be earlier than {{ previousReading.reading_date }}</small>
                                </div>

                                <!-- Reading Value Input -->
                                <div class="form-group">
                                    <label>Reading Value <span class="text-danger">*</span></label>
                                    
                                    <!-- Water Meter Pigeonhole Input -->
                                    <div v-if="isMeterWater(selectedMeterType)" class="meter-input-container">
                                        <div class="water-meter-display">
                                            <!-- White section: 6 digits -->
                                            <div class="meter-section white-section">
                                                <div v-for="i in 6" :key="'white-' + i" class="meter-box white-box">
                                                    <input 
                                                        type="text" 
                                                        maxlength="1"
                                                        class="meter-input"
                                                        :value="getDigit(newReading.wholeDigits, i - 1)"
                                                        @input="updateDigit($event, i - 1, 'whole')"
                                                        @keydown="handleKeyDown($event, i - 1, 'whole')"
                                                        :ref="el => wholeInputRefs[i - 1] = el">
                                                </div>
                                            </div>
                                            <span class="meter-decimal-point">.</span>
                                            <!-- Red section: 1 decimal digit -->
                                            <div class="meter-section red-section">
                                                <div class="meter-box red-box">
                                                    <input 
                                                        type="text" 
                                                        maxlength="1"
                                                        class="meter-input"
                                                        :value="newReading.decimalDigit"
                                                        @input="updateDigit($event, 0, 'decimal')"
                                                        ref="decimalInput">
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Enter water meter reading (6 whole digits + 1 decimal)</small>
                                        <small v-if="previousReading" class="text-danger d-block">Value must be >= {{ previousReading.reading_value }}</small>
                                    </div>

                                    <!-- Electricity Meter: 5 digits + 1 decimal -->
                                    <div v-else-if="isMeterElectricity(selectedMeterType)" class="meter-input-container">
                                        <div class="electricity-meter-display">
                                            <!-- Black section: 5 digits -->
                                            <div class="meter-section black-section">
                                                <div v-for="i in 5" :key="'black-' + i" class="meter-box black-box">
                                                    <input 
                                                        type="text" 
                                                        maxlength="1"
                                                        class="meter-input"
                                                        :value="getDigit(newReading.wholeDigits, i - 1)"
                                                        @input="updateDigit($event, i - 1, 'whole')"
                                                        @keydown="handleKeyDown($event, i - 1, 'whole')"
                                                        :ref="el => wholeInputRefs[i - 1] = el">
                                                </div>
                                            </div>
                                            <span class="meter-decimal-point">.</span>
                                            <!-- Red section: 1 decimal digit -->
                                            <div class="meter-section red-section">
                                                <div class="meter-box red-box">
                                                    <input 
                                                        type="text" 
                                                        maxlength="1"
                                                        class="meter-input"
                                                        :value="newReading.decimalDigit"
                                                        @input="updateDigit($event, 0, 'decimal')"
                                                        ref="decimalInput">
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Enter electricity meter reading (5 digits + 1 decimal)</small>
                                        <small v-if="previousReading" class="text-danger d-block">Value must be >= {{ previousReading.reading_value }}</small>
                                    </div>

                                    <!-- Standard input for other meter types -->
                                    <div v-else>
                                        <input type="number" class="form-control" v-model="newReading.reading_value" step="0.1">
                                        <small v-if="previousReading" class="text-danger">Value must be >= {{ previousReading.reading_value }}</small>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-success" @click="addReading" :disabled="saving">
                                    <i class="fas fa-plus mr-1"></i> Add Reading
                                </button>
                            </div>
                        </div>

                        <!-- Billing Tab -->
                        <div v-show="activeTab === 'billing'">
                            <div class="text-center py-5">
                                <i class="fas fa-hard-hat fa-4x text-warning mb-3"></i>
                                <h5>Billing Feature Coming Soon</h5>
                                <p class="text-muted">This feature is under development. Check back later!</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-else>
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading user data...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeUserModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showUserModal"></div>

        <!-- Add Meter Modal -->
        <div class="modal fade" :class="{ show: showAddMeterModal, 'd-block': showAddMeterModal }" tabindex="-1" v-show="showAddMeterModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Meter</h5>
                        <button type="button" class="close text-white" @click="showAddMeterModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Meter Type <span class="text-danger">*</span></label>
                            <select class="form-control" v-model="newMeter.meter_type_id">
                                <option value="">Select Type</option>
                                <option v-for="type in meterTypes" :key="type.id" :value="type.id">{{ type.title }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Meter Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" v-model="newMeter.meter_title" placeholder="Enter meter title">
                        </div>
                        <div class="form-group">
                            <label>Meter Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" v-model="newMeter.meter_number" placeholder="Enter meter number">
                        </div>
                        <div class="form-group">
                            <label>Initial Reading</label>
                            <input type="number" class="form-control" v-model="newMeter.initial_reading" placeholder="Optional initial reading">
                        </div>
                        <div class="form-group" v-if="newMeter.initial_reading">
                            <label>Initial Reading Date</label>
                            <input type="date" class="form-control" v-model="newMeter.initial_reading_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showAddMeterModal = false">Cancel</button>
                        <button type="button" class="btn btn-success" @click="addMeter" :disabled="saving">
                            <i class="fas fa-plus mr-1"></i> Add Meter
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showAddMeterModal" style="z-index: 1040;"></div>

        <!-- Readings History Modal -->
        <div class="modal fade" :class="{ show: showReadingsModal, 'd-block': showReadingsModal }" tabindex="-1" v-show="showReadingsModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-list mr-2"></i>Readings History</h5>
                        <button type="button" class="close text-white" @click="showReadingsModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div v-if="loadingReadings" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                        </div>
                        <div v-else-if="meterReadings.length === 0" class="text-center py-4 text-muted">
                            No readings found for this meter
                        </div>
                        <table v-else class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reading Value</th>
                                    <th>Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(reading, index) in meterReadings" :key="reading.id">
                                    <td>{{ reading.reading_date }}</td>
                                    <td>{{ reading.reading_value }}</td>
                                    <td>
                                        <span v-if="index < meterReadings.length - 1">
                                            {{ (parseFloat(reading.reading_value) - parseFloat(meterReadings[index + 1].reading_value)).toFixed(2) }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showReadingsModal = false">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showReadingsModal" style="z-index: 1040;"></div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" :class="{ show: showDeleteModal, 'd-block': showDeleteModal }" tabindex="-1" v-show="showDeleteModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete</h5>
                        <button type="button" class="close text-white" @click="showDeleteModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ deleteMessage }}</p>
                        <p class="text-danger font-weight-bold">This action cannot be undone!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="executeDelete" :disabled="loading">
                            <span v-if="loading"><i class="fas fa-spinner fa-spin"></i></span>
                            <span v-else><i class="fas fa-trash mr-1"></i> Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showDeleteModal" style="z-index: 1050;"></div>

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
import { ref, reactive, computed, onMounted } from 'vue';

const props = defineProps({
    csrfToken: {
        type: String,
        required: true
    },
    users: {
        type: Array,
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

// State
const usersList = ref([...props.users]);
const selectedUser = ref(null);
const showUserModal = ref(false);
const showAddMeterModal = ref(false);
const showReadingsModal = ref(false);
const showDeleteModal = ref(false);
const activeTab = ref('user');
const loading = ref(false);
const saving = ref(false);
const loadingReadings = ref(false);
const searchTimeout = ref(null);
const deleteMessage = ref('');
const deleteAction = ref(null);
const meterReadings = ref([]);
const previousReading = ref(null);
const selectedMeterType = ref(null);

// Refs for pigeonhole inputs
const wholeInputRefs = ref([]);
const decimalInput = ref(null);

// Notification
const notification = reactive({
    show: false,
    type: 'success',
    message: ''
});

// Filters
const filters = reactive({
    name: '',
    address: '',
    phone: '',
    user_type: ''
});

// Edit User Data
const editUserData = reactive({
    name: '',
    email: '',
    contact_number: '',
    password: ''
});

// New Meter
const newMeter = reactive({
    account_id: '',
    meter_type_id: '',
    meter_title: '',
    meter_number: '',
    initial_reading: '',
    initial_reading_date: new Date().toISOString().split('T')[0]
});

// New Reading with pigeonhole data
const newReading = reactive({
    meter_id: '',
    reading_date: new Date().toISOString().split('T')[0],
    reading_value: '',
    wholeDigits: '000000', // For water: 6 digits, for electricity: 5 digits (padded)
    decimalDigit: '0'
});

// Helper functions
function buildUrl(urlTemplate, replacements) {
    let url = urlTemplate;
    if (typeof replacements === 'object') {
        for (const [placeholder, value] of Object.entries(replacements)) {
            url = url.replace(placeholder, value);
        }
    } else {
        // Backward compatible: single ID replaces all placeholders
        url = url.replace('__ID__', replacements).replace('__METER_ID__', replacements).replace('__REGION_ID__', replacements);
    }
    return url;
}

function showNotification(message, type = 'success') {
    notification.message = message;
    notification.type = type;
    notification.show = true;
    setTimeout(() => {
        notification.show = false;
    }, 5000);
}

function getMeterTypeName(typeId) {
    const type = props.meterTypes.find(t => t.id === typeId);
    return type ? type.title : 'Unknown';
}

function getMeterTypeBadgeClass(typeId) {
    const type = props.meterTypes.find(t => t.id === typeId);
    if (!type) return 'badge-secondary';
    const title = type.title.toLowerCase();
    if (title === 'water') return 'badge-primary';
    if (title === 'electricity') return 'badge-warning';
    return 'badge-secondary';
}

function isMeterWater(typeId) {
    if (!typeId) return false;
    const type = props.meterTypes.find(t => t.id === typeId);
    return type && type.title && type.title.toLowerCase() === 'water';
}

function isMeterElectricity(typeId) {
    if (!typeId) return false;
    const type = props.meterTypes.find(t => t.id === typeId);
    return type && type.title && type.title.toLowerCase() === 'electricity';
}

// Pigeonhole input helpers
function getDigit(digits, index) {
    return digits && digits[index] ? digits[index] : '0';
}

function updateDigit(event, index, section) {
    const value = event.target.value.replace(/[^0-9]/g, '');
    
    if (section === 'whole') {
        const maxDigits = isMeterWater(selectedMeterType.value) ? 6 : 5;
        let digits = newReading.wholeDigits.split('');
        digits[index] = value || '0';
        newReading.wholeDigits = digits.join('').padEnd(maxDigits, '0').slice(0, maxDigits);
        
        // Auto-focus next input
        if (value && index < maxDigits - 1) {
            const nextRef = wholeInputRefs.value[index + 1];
            if (nextRef) nextRef.focus();
        } else if (value && index === maxDigits - 1 && decimalInput.value) {
            decimalInput.value.focus();
        }
    } else {
        newReading.decimalDigit = value || '0';
    }
    
    // Update combined reading value
    updateCombinedReadingValue();
}

function handleKeyDown(event, index, section) {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        const prevRef = wholeInputRefs.value[index - 1];
        if (prevRef) prevRef.focus();
    }
}

function updateCombinedReadingValue() {
    newReading.reading_value = `${newReading.wholeDigits}.${newReading.decimalDigit}`;
}

// Search
function debounceSearch() {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        searchUsers();
    }, 300);
}

async function searchUsers() {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (filters.name) params.append('name', filters.name);
        if (filters.address) params.append('address', filters.address);
        if (filters.phone) params.append('phone', filters.phone);
        if (filters.user_type) params.append('user_type', filters.user_type);
        
        const response = await fetch(`${props.apiUrls.search}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        if (data.status === 200) {
            usersList.value = data.data;
        }
    } catch (error) {
        showNotification('Error searching users: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

// View User
async function viewUser(userId) {
    loading.value = true;
    selectedUser.value = null;
    activeTab.value = 'user';
    showUserModal.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.getUserData, userId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        
        if (data.status === 200) {
            selectedUser.value = data.data;
            editUserData.name = data.data.name;
            editUserData.email = data.data.email;
            editUserData.contact_number = data.data.contact_number;
            editUserData.password = '';
        } else {
            showNotification('Error loading user data', 'danger');
            showUserModal.value = false;
        }
    } catch (error) {
        showNotification('Error loading user: ' + error.message, 'danger');
        showUserModal.value = false;
    } finally {
        loading.value = false;
    }
}

function closeUserModal() {
    showUserModal.value = false;
    selectedUser.value = null;
}

// Update User
async function updateUser() {
    if (!editUserData.name || !editUserData.email || !editUserData.contact_number) {
        showNotification('Please fill in all required fields', 'danger');
        return;
    }
    
    saving.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.updateUser, selectedUser.value.id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            },
            body: JSON.stringify(editUserData)
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            // Update local data
            selectedUser.value.name = editUserData.name;
            selectedUser.value.email = editUserData.email;
            selectedUser.value.contact_number = editUserData.contact_number;
            searchUsers();
        } else {
            showNotification(data.message || 'Error updating user', 'danger');
        }
    } catch (error) {
        showNotification('Error updating user: ' + error.message, 'danger');
    } finally {
        saving.value = false;
    }
}

// Add Meter
function openAddMeterModal(accountId) {
    newMeter.account_id = accountId;
    newMeter.meter_type_id = '';
    newMeter.meter_title = '';
    newMeter.meter_number = '';
    newMeter.initial_reading = '';
    newMeter.initial_reading_date = new Date().toISOString().split('T')[0];
    showAddMeterModal.value = true;
}

async function addMeter() {
    if (!newMeter.meter_type_id || !newMeter.meter_title || !newMeter.meter_number) {
        showNotification('Please fill in all required fields', 'danger');
        return;
    }
    
    saving.value = true;
    
    try {
        const response = await fetch(props.apiUrls.addMeter, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            },
            body: JSON.stringify(newMeter)
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            showAddMeterModal.value = false;
            // Refresh user data
            viewUser(selectedUser.value.id);
        } else {
            showNotification(data.message || 'Error adding meter', 'danger');
        }
    } catch (error) {
        showNotification('Error adding meter: ' + error.message, 'danger');
    } finally {
        saving.value = false;
    }
}

// Delete Meter
async function deleteMeter(meterId) {
    if (!confirm('Are you sure you want to delete this meter?')) return;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.deleteMeter, meterId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            viewUser(selectedUser.value.id);
        } else {
            showNotification(data.message || 'Error deleting meter', 'danger');
        }
    } catch (error) {
        showNotification('Error deleting meter: ' + error.message, 'danger');
    }
}

// View Readings
async function viewReadings(meter) {
    loadingReadings.value = true;
    meterReadings.value = [];
    showReadingsModal.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.getReadings, meter.id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        
        if (data.status === 200) {
            meterReadings.value = data.data;
        }
    } catch (error) {
        showNotification('Error loading readings: ' + error.message, 'danger');
    } finally {
        loadingReadings.value = false;
    }
}

// Meter selection for new reading
function onMeterSelectForReading() {
    if (!newReading.meter_id) {
        previousReading.value = null;
        selectedMeterType.value = null;
        return;
    }
    
    // Find the selected meter
    let selectedMeter = null;
    for (const site of selectedUser.value.sites) {
        for (const account of site.accounts) {
            for (const meter of account.meters) {
                if (meter.id === newReading.meter_id) {
                    selectedMeter = meter;
                    break;
                }
            }
        }
    }
    
    if (selectedMeter) {
        selectedMeterType.value = selectedMeter.meter_type_id;
        previousReading.value = selectedMeter.readings && selectedMeter.readings.length > 0 ? selectedMeter.readings[0] : null;
        
        // Initialize pigeonhole values
        const maxDigits = isMeterWater(selectedMeterType.value) ? 6 : 5;
        newReading.wholeDigits = '0'.repeat(maxDigits);
        newReading.decimalDigit = '0';
        newReading.reading_value = '';
    }
}

// Add Reading
async function addReading() {
    if (!newReading.meter_id || !newReading.reading_date) {
        showNotification('Please select a meter and enter a date', 'danger');
        return;
    }
    
    // Calculate combined value for pigeonhole meters
    if (isMeterWater(selectedMeterType.value) || isMeterElectricity(selectedMeterType.value)) {
        updateCombinedReadingValue();
    }
    
    if (!newReading.reading_value) {
        showNotification('Please enter a reading value', 'danger');
        return;
    }
    
    saving.value = true;
    
    try {
        const response = await fetch(props.apiUrls.addReading, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            },
            body: JSON.stringify({
                meter_id: newReading.meter_id,
                reading_date: newReading.reading_date,
                reading_value: newReading.reading_value
            })
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            // Reset form
            newReading.meter_id = '';
            newReading.reading_value = '';
            newReading.wholeDigits = '000000';
            newReading.decimalDigit = '0';
            previousReading.value = null;
            selectedMeterType.value = null;
            // Refresh user data
            viewUser(selectedUser.value.id);
        } else {
            showNotification(data.message || 'Error adding reading', 'danger');
        }
    } catch (error) {
        showNotification('Error adding reading: ' + error.message, 'danger');
    } finally {
        saving.value = false;
    }
}

// Delete User
function confirmDeleteUser(user) {
    deleteMessage.value = `Are you sure you want to delete user "${user.name}" and all associated data?`;
    deleteAction.value = () => deleteUser(user.id);
    showDeleteModal.value = true;
}

async function deleteUser(userId) {
    loading.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.deleteUser, userId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            showDeleteModal.value = false;
            searchUsers();
        } else {
            showNotification(data.message || 'Error deleting user', 'danger');
        }
    } catch (error) {
        showNotification('Error deleting user: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

function executeDelete() {
    if (deleteAction.value) {
        deleteAction.value();
    }
}

// Initial load
onMounted(() => {
    // Users are already loaded via props
});
</script>

<style scoped>
.user-account-manager-wrapper {
    padding: 0;
}

.modal.show {
    display: block;
}

.btn-circle {
    border-radius: 50%;
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.nav-tabs .nav-link {
    cursor: pointer;
}

.nav-tabs .nav-link.active {
    font-weight: bold;
}

/* Water Meter Pigeonhole Styles */
.meter-input-container {
    margin: 10px 0;
}

.water-meter-display,
.electricity-meter-display {
    display: flex;
    align-items: center;
    gap: 2px;
}

.meter-section {
    display: flex;
    gap: 2px;
}

.meter-box {
    width: 40px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 2px solid #333;
}

.white-box {
    background: #fff;
    border-color: #333;
}

.white-box .meter-input {
    color: #000;
    background: transparent;
}

.black-box {
    background: #333;
    border-color: #333;
}

.black-box .meter-input {
    color: #fff;
    background: transparent;
}

.red-box {
    background: #b30101;
    border-color: #b30101;
}

.red-box .meter-input {
    color: #fff;
    background: transparent;
}

.meter-input {
    width: 100%;
    height: 100%;
    text-align: center;
    font-family: 'Courier New', monospace;
    font-weight: bold;
    font-size: 24px;
    border: none;
    outline: none;
}

.meter-decimal-point {
    font-size: 30px;
    font-weight: bold;
    margin: 0 4px;
}

.badge-primary {
    background-color: #4e73df;
}

.badge-warning {
    background-color: #f6c23e;
    color: #1f2d3d;
}

.badge-secondary {
    background-color: #858796;
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
