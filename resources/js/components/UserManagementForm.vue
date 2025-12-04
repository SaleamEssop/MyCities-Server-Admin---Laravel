<template>
    <div class="user-management-wrapper">
        <!-- Quick Actions Bar -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                <div>
                    <button type="button" class="btn btn-success btn-sm mr-2" @click="openCreateModal">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                    <button type="button" class="btn btn-info btn-sm mr-2" @click="generateTestUser" :disabled="loading">
                        <i class="fas fa-flask"></i> Generate Test User
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" @click="confirmDeleteTestUsers" :disabled="loading">
                        <i class="fas fa-trash"></i> Delete All Test Data
                    </button>
                </div>
            </div>
        </div>

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
                            <input type="text" class="form-control" v-model="filters.address" @input="debounceSearch" placeholder="Search by site address...">
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
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Sites</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in filteredUsers" :key="user.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.contact_number }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ user.sites_count || 0 }} site(s)</span>
                                </td>
                                <td>
                                    <span v-if="isTestUser(user.email)" class="badge badge-warning">Test User</span>
                                    <span v-else class="badge badge-success">Real User</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm btn-circle mr-1" @click="editUser(user.id)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm btn-circle mr-1" @click="cloneUser(user.id)" title="Clone">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-circle" @click="confirmDeleteUser(user)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="7" class="text-center">No users found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Form Modal -->
        <div class="modal fade" :class="{ show: showModal, 'd-block': showModal }" tabindex="-1" role="dialog" v-show="showModal">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user mr-2"></i>
                            {{ editingUserId ? 'Edit User' : 'Create New User' }}
                        </h5>
                        <button type="button" class="close text-white" @click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Section 1: User Details -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-user mr-2"></i>Section 1: User Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" v-model="formData.name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" v-model="formData.email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" v-model="formData.contact_number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Password <span v-if="!editingUserId" class="text-danger">*</span></label>
                                            <input type="password" class="form-control" v-model="formData.password" :placeholder="editingUserId ? 'Leave blank to keep current' : 'Enter password'">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Setup Options -->
                                <div class="border-top pt-3 mt-3">
                                    <h6 class="text-muted mb-3"><i class="fas fa-bolt mr-2"></i>Quick Setup Options</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Region Selection</label>
                                                <select class="form-control" v-model="formData.default_region_id" @change="onDefaultRegionChange">
                                                    <option value="">Select Region</option>
                                                    <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.name }}</option>
                                                </select>
                                                <small class="text-muted">Applied to new sites</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tariff Template</label>
                                                <select class="form-control" v-model="formData.default_tariff_template_id" :disabled="!formData.default_region_id || tariffTemplatesLoading">
                                                    <option value="">Select Tariff Template</option>
                                                    <option v-for="template in tariffTemplates" :key="template.id" :value="template.id">{{ template.template_name }}</option>
                                                </select>
                                                <small class="text-muted">
                                                    <span v-if="tariffTemplatesLoading">Loading...</span>
                                                    <span v-else-if="!formData.default_region_id">Select region first</span>
                                                    <span v-else>Applied to new accounts</span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Billing Mode</label>
                                                <div class="billing-mode-toggle">
                                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                                        <label class="btn btn-outline-primary" :class="{ active: formData.default_billing_type === 'monthly' }">
                                                            <input type="radio" v-model="formData.default_billing_type" value="monthly"> Monthly
                                                        </label>
                                                        <label class="btn btn-outline-primary" :class="{ active: formData.default_billing_type === 'date-to-date' }">
                                                            <input type="radio" v-model="formData.default_billing_type" value="date-to-date"> Date-to-Date
                                                        </label>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Applied to new sites</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Sites, Accounts & Meters -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-home mr-2"></i>Section 2: Account & Meter Allocation</h6>
                                <button type="button" class="btn btn-success btn-sm" @click="addSite">
                                    <i class="fas fa-plus"></i> Add Site
                                </button>
                            </div>
                            <div class="card-body">
                                <div v-for="(site, siteIndex) in formData.sites" :key="'site-' + siteIndex" class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="m-0">
                                            <a href="#" @click.prevent="toggleSite(siteIndex)" class="text-dark">
                                                <i :class="site.expanded ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="mr-2"></i>
                                                Site {{ siteIndex + 1 }}: {{ site.title || 'New Site' }}
                                            </a>
                                        </h6>
                                        <button type="button" class="btn btn-danger btn-sm" @click="removeSite(siteIndex)" v-if="formData.sites.length > 0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div v-show="site.expanded">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Title</label>
                                                    <input type="text" class="form-control" v-model="site.title" placeholder="Site title">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label>Address</label>
                                                    <input type="text" class="form-control" v-model="site.address" placeholder="Full address">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Latitude</label>
                                                    <input type="number" step="any" class="form-control" v-model="site.lat" placeholder="Lat">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Longitude</label>
                                                    <input type="number" step="any" class="form-control" v-model="site.lng" placeholder="Lng">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Region</label>
                                                    <select class="form-control" v-model="site.region_id">
                                                        <option value="">Select Region</option>
                                                        <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.name }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Billing Type</label>
                                                    <select class="form-control" v-model="site.billing_type">
                                                        <option value="monthly">Monthly</option>
                                                        <option value="bi-monthly">Bi-Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Accounts Section (nested) -->
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong><i class="fas fa-file-invoice mr-2"></i>Accounts</strong>
                                                <button type="button" class="btn btn-outline-success btn-sm" @click="addAccount(siteIndex)">
                                                    <i class="fas fa-plus"></i> Add Account
                                                </button>
                                            </div>
                                            
                                            <div v-for="(account, accountIndex) in site.accounts" :key="'account-' + siteIndex + '-' + accountIndex" class="border-left border-primary pl-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        <a href="#" @click.prevent="toggleAccount(siteIndex, accountIndex)" class="text-dark">
                                                            <i :class="account.expanded ? 'fas fa-chevron-down' : 'fas fa-chevron-right'" class="mr-1"></i>
                                                            Account {{ accountIndex + 1 }}: {{ account.account_name || 'New Account' }}
                                                        </a>
                                                    </small>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="removeAccount(siteIndex, accountIndex)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                
                                                <div v-show="account.expanded">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Account Name</label>
                                                                <input type="text" class="form-control form-control-sm" v-model="account.account_name" placeholder="Account name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Account Number</label>
                                                                <input type="text" class="form-control form-control-sm" v-model="account.account_number" placeholder="Account number">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Tariff Template</label>
                                                                <select class="form-control form-control-sm" v-model="account.tariff_template_id">
                                                                    <option value="">Select Tariff Template</option>
                                                                    <option v-for="template in tariffTemplates" :key="template.id" :value="template.id">{{ template.template_name }}</option>
                                                                </select>
                                                                <small v-if="!formData.default_region_id" class="text-muted">Select region first</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Billing Date</label>
                                                                <input type="date" class="form-control form-control-sm" v-model="account.billing_date">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Bill Day</label>
                                                                <input type="number" min="1" max="31" class="form-control form-control-sm" v-model="account.bill_day" placeholder="1-31">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Read Day</label>
                                                                <input type="number" min="1" max="31" class="form-control form-control-sm" v-model="account.read_day" placeholder="1-31">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Meters Section (nested) -->
                                                    <div class="mt-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small><i class="fas fa-tachometer-alt mr-1"></i>Meters</small>
                                                            <button type="button" class="btn btn-outline-info btn-sm" @click="addMeter(siteIndex, accountIndex)">
                                                                <i class="fas fa-plus"></i> Add Meter
                                                            </button>
                                                        </div>
                                                        
                                                        <div v-for="(meter, meterIndex) in account.meters" :key="'meter-' + siteIndex + '-' + accountIndex + '-' + meterIndex" class="meter-card border-left border-info pl-2 mb-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-tachometer-alt mr-1"></i>
                                                                    Meter {{ meterIndex + 1 }}: {{ meter.meter_title || 'New Meter' }}
                                                                </small>
                                                                <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="removeMeter(siteIndex, accountIndex, meterIndex)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1">
                                                                        <label class="small">Meter Title</label>
                                                                        <input type="text" class="form-control form-control-sm" v-model="meter.meter_title" placeholder="e.g. Main Electricity Meter">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1">
                                                                        <label class="small">Meter Number</label>
                                                                        <input type="text" class="form-control form-control-sm" v-model="meter.meter_number" placeholder="Physical meter number">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-1">
                                                                        <label class="small">Meter Type</label>
                                                                        <select class="form-control form-control-sm" v-model="meter.meter_type_id" @change="onMeterTypeChange(meter)">
                                                                            <option value="">Select Type</option>
                                                                            <option v-for="type in meterTypes" :key="type.id" :value="type.id">{{ type.title }}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Section 3: Initial Reading Capture (Pigeonhole Inputs) -->
                                                            <div v-if="meter.meter_type_id" class="initial-reading-section mt-2 p-2 bg-light rounded">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <small class="font-weight-bold text-primary">
                                                                        <i class="fas fa-edit mr-1"></i>Initial Reading (Quasar-Style)
                                                                    </small>
                                                                    <div class="form-group mb-0">
                                                                        <input type="date" class="form-control form-control-sm" v-model="meter.initial_reading_date" style="width: 150px;">
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Electricity Meter: 5 black + 1 red decimal (6 boxes) -->
                                                                <div v-if="getMeterTypeName(meter.meter_type_id) === 'electricity'" class="pigeonhole-container">
                                                                    <div class="d-flex align-items-center justify-content-center">
                                                                        <div class="pigeonhole-group">
                                                                            <input 
                                                                                v-for="(digit, digitIndex) in 5" 
                                                                                :key="'elec-' + digitIndex"
                                                                                type="text" 
                                                                                maxlength="1" 
                                                                                class="pigeonhole-input pigeonhole-black"
                                                                                :value="meter.reading_digits ? meter.reading_digits[digitIndex] : '0'"
                                                                                @input="onDigitInput($event, meter, digitIndex, 5)"
                                                                                @keydown="onDigitKeydown($event, meter, digitIndex, 5)"
                                                                                :ref="el => setDigitRef(el, siteIndex, accountIndex, meterIndex, digitIndex)"
                                                                                inputmode="numeric"
                                                                                pattern="[0-9]"
                                                                            >
                                                                        </div>
                                                                        <span class="pigeonhole-decimal">.</span>
                                                                        <div class="pigeonhole-group">
                                                                            <input 
                                                                                type="text" 
                                                                                maxlength="1" 
                                                                                class="pigeonhole-input pigeonhole-red"
                                                                                :value="meter.reading_digits ? meter.reading_digits[5] : '0'"
                                                                                @input="onDigitInput($event, meter, 5, 5)"
                                                                                @keydown="onDigitKeydown($event, meter, 5, 5)"
                                                                                :ref="el => setDigitRef(el, siteIndex, accountIndex, meterIndex, 5)"
                                                                                inputmode="numeric"
                                                                                pattern="[0-9]"
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-center mt-1">
                                                                        <small class="text-muted">kWh (5 digits + 1 decimal)</small>
                                                                    </div>
                                                                    <div class="text-center mt-1">
                                                                        <span class="badge badge-secondary">Reading: {{ getReadingValue(meter) }} kWh</span>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Water Meter: 7 black + 1 red decimal (8 boxes) -->
                                                                <div v-else-if="getMeterTypeName(meter.meter_type_id) === 'water'" class="pigeonhole-container">
                                                                    <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                                        <!-- First 4 digits (kL) -->
                                                                        <div class="pigeonhole-group">
                                                                            <input 
                                                                                v-for="(digit, digitIndex) in 4" 
                                                                                :key="'water-kl-' + digitIndex"
                                                                                type="text" 
                                                                                maxlength="1" 
                                                                                class="pigeonhole-input pigeonhole-black"
                                                                                :value="meter.reading_digits ? meter.reading_digits[digitIndex] : '0'"
                                                                                @input="onDigitInput($event, meter, digitIndex, 7)"
                                                                                @keydown="onDigitKeydown($event, meter, digitIndex, 7)"
                                                                                :ref="el => setDigitRef(el, siteIndex, accountIndex, meterIndex, digitIndex)"
                                                                                inputmode="numeric"
                                                                                pattern="[0-9]"
                                                                            >
                                                                        </div>
                                                                        <!-- Next 3 digits (litres) -->
                                                                        <div class="pigeonhole-group ml-1">
                                                                            <input 
                                                                                v-for="(digit, digitIndex) in 3" 
                                                                                :key="'water-l-' + digitIndex"
                                                                                type="text" 
                                                                                maxlength="1" 
                                                                                class="pigeonhole-input pigeonhole-black"
                                                                                :value="meter.reading_digits ? meter.reading_digits[digitIndex + 4] : '0'"
                                                                                @input="onDigitInput($event, meter, digitIndex + 4, 7)"
                                                                                @keydown="onDigitKeydown($event, meter, digitIndex + 4, 7)"
                                                                                :ref="el => setDigitRef(el, siteIndex, accountIndex, meterIndex, digitIndex + 4)"
                                                                                inputmode="numeric"
                                                                                pattern="[0-9]"
                                                                            >
                                                                        </div>
                                                                        <span class="pigeonhole-decimal">.</span>
                                                                        <!-- Last digit (red - 1/10 L) -->
                                                                        <div class="pigeonhole-group">
                                                                            <input 
                                                                                type="text" 
                                                                                maxlength="1" 
                                                                                class="pigeonhole-input pigeonhole-red"
                                                                                :value="meter.reading_digits ? meter.reading_digits[7] : '0'"
                                                                                @input="onDigitInput($event, meter, 7, 7)"
                                                                                @keydown="onDigitKeydown($event, meter, 7, 7)"
                                                                                :ref="el => setDigitRef(el, siteIndex, accountIndex, meterIndex, 7)"
                                                                                inputmode="numeric"
                                                                                pattern="[0-9]"
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-center mt-1">
                                                                        <small class="text-muted">
                                                                            <span class="mr-2">kL (4 digits)</span>
                                                                            <span class="mr-2">Litres (3 digits)</span>
                                                                            <span>1/10 L</span>
                                                                        </small>
                                                                    </div>
                                                                    <div class="text-center mt-1">
                                                                        <span class="badge badge-secondary">Reading: {{ getReadingValue(meter) }} kL</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="formData.sites.length === 0" class="text-center text-muted py-3">
                                    No sites added yet. Click "Add Site" to get started.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="saveUser" :disabled="saving">
                            <span v-if="saving"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                            <span v-else><i class="fas fa-save"></i> {{ editingUserId ? 'Update User' : 'Create User' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showModal"></div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" :class="{ show: showDeleteModal, 'd-block': showDeleteModal }" tabindex="-1" role="dialog" v-show="showDeleteModal">
            <div class="modal-dialog" role="document">
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
                            <span v-else><i class="fas fa-trash"></i> Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showDeleteModal"></div>

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
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingUserId = ref(null);
const loading = ref(false);
const saving = ref(false);
const deleteMessage = ref('');
const deleteAction = ref(null);
const searchTimeout = ref(null);

// Tariff templates state
const tariffTemplates = ref([]);
const tariffTemplatesLoading = ref(false);

// Digit input refs for pigeonhole inputs
const digitRefs = ref({});

// Notification state
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

// Form data
const formData = reactive({
    name: '',
    email: '',
    contact_number: '',
    password: '',
    default_region_id: '',
    default_tariff_template_id: '',
    default_billing_type: 'monthly',
    sites: []
});

// Computed
const filteredUsers = computed(() => {
    let result = usersList.value;
    
    // Apply local filtering for immediate UI response
    // (Server-side search also runs via searchUsers() for complete results)
    if (filters.name) {
        const searchName = filters.name.toLowerCase();
        result = result.filter(user => user.name && user.name.toLowerCase().includes(searchName));
    }
    
    if (filters.phone) {
        const searchPhone = filters.phone.toLowerCase();
        result = result.filter(user => user.contact_number && user.contact_number.toLowerCase().includes(searchPhone));
    }
    
    if (filters.user_type === 'test') {
        result = result.filter(user => isTestUser(user.email));
    } else if (filters.user_type === 'real') {
        result = result.filter(user => !isTestUser(user.email));
    }
    
    return result;
});

// Methods
function isTestUser(email) {
    return email && email.toLowerCase().endsWith('@test.com');
}

// Helper to replace __ID__ placeholder in URLs
function buildUrl(urlTemplate, id) {
    return urlTemplate.replace('__ID__', id);
}

function showNotification(message, type = 'success') {
    notification.message = message;
    notification.type = type;
    notification.show = true;
    setTimeout(() => {
        notification.show = false;
    }, 5000);
}

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

function resetForm() {
    formData.name = '';
    formData.email = '';
    formData.contact_number = '';
    formData.password = '';
    formData.default_region_id = '';
    formData.default_tariff_template_id = '';
    formData.default_billing_type = 'monthly';
    formData.sites = [];
    tariffTemplates.value = [];
    digitRefs.value = {};
    editingUserId.value = null;
}

// Helper to build URL for tariff templates endpoint
function buildTariffUrl(regionId) {
    return props.apiUrls.getTariffTemplates.replace('__REGION_ID__', regionId);
}

// Fetch tariff templates when region changes
async function onDefaultRegionChange() {
    formData.default_tariff_template_id = '';
    tariffTemplates.value = [];
    
    if (!formData.default_region_id) {
        return;
    }
    
    tariffTemplatesLoading.value = true;
    try {
        const response = await fetch(buildTariffUrl(formData.default_region_id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        if (data.status === 200) {
            tariffTemplates.value = data.data;
        }
    } catch (error) {
        console.error('Error fetching tariff templates:', error);
    } finally {
        tariffTemplatesLoading.value = false;
    }
}

// Get meter type name from ID
function getMeterTypeName(meterTypeId) {
    const meterType = props.meterTypes.find(t => t.id === meterTypeId || t.id === parseInt(meterTypeId));
    return meterType ? meterType.title.toLowerCase() : '';
}

// Handle meter type change - initialize reading digits
function onMeterTypeChange(meter) {
    const meterTypeName = getMeterTypeName(meter.meter_type_id);
    if (meterTypeName === 'electricity') {
        meter.reading_digits = ['0', '0', '0', '0', '0', '0'];
    } else if (meterTypeName === 'water') {
        meter.reading_digits = ['0', '0', '0', '0', '0', '0', '0', '0'];
    } else {
        meter.reading_digits = [];
    }
    // Set default date to today
    if (!meter.initial_reading_date) {
        meter.initial_reading_date = new Date().toISOString().split('T')[0];
    }
}

// Set reference for digit input
function setDigitRef(el, siteIndex, accountIndex, meterIndex, digitIndex) {
    const key = `${siteIndex}-${accountIndex}-${meterIndex}-${digitIndex}`;
    if (el) {
        digitRefs.value[key] = el;
    }
}

// Get digit input reference
function getDigitRef(siteIndex, accountIndex, meterIndex, digitIndex) {
    const key = `${siteIndex}-${accountIndex}-${meterIndex}-${digitIndex}`;
    return digitRefs.value[key];
}

// Handle digit input - auto-advance to next box
function onDigitInput(event, meter, digitIndex, maxIndex) {
    const value = event.target.value;
    // Only allow digits
    if (value && !/^[0-9]$/.test(value)) {
        event.target.value = meter.reading_digits[digitIndex] || '0';
        return;
    }
    
    // Update the digit
    if (!meter.reading_digits) {
        meter.reading_digits = getMeterTypeName(meter.meter_type_id) === 'electricity' 
            ? ['0', '0', '0', '0', '0', '0'] 
            : ['0', '0', '0', '0', '0', '0', '0', '0'];
    }
    meter.reading_digits[digitIndex] = value || '0';
    
    // Auto-advance to next input
    if (value && digitIndex < maxIndex) {
        // Find and focus next input in same meter
        const parent = event.target.closest('.pigeonhole-container');
        if (parent) {
            const inputs = parent.querySelectorAll('.pigeonhole-input');
            if (inputs[digitIndex + 1]) {
                inputs[digitIndex + 1].focus();
                inputs[digitIndex + 1].select();
            }
        }
    }
    
    // Update the initial_reading value
    updateMeterReading(meter);
}

// Handle keydown for navigation between pigeonhole inputs
function onDigitKeydown(event, meter, digitIndex, maxIndex) {
    const parent = event.target.closest('.pigeonhole-container');
    if (!parent) return;
    
    const inputs = parent.querySelectorAll('.pigeonhole-input');
    
    switch(event.key) {
        case 'ArrowLeft':
            if (digitIndex > 0 && inputs[digitIndex - 1]) {
                event.preventDefault();
                inputs[digitIndex - 1].focus();
                inputs[digitIndex - 1].select();
            }
            break;
        case 'ArrowRight':
            if (digitIndex < maxIndex && inputs[digitIndex + 1]) {
                event.preventDefault();
                inputs[digitIndex + 1].focus();
                inputs[digitIndex + 1].select();
            }
            break;
        case 'Backspace':
            if (!event.target.value && digitIndex > 0 && inputs[digitIndex - 1]) {
                event.preventDefault();
                inputs[digitIndex - 1].focus();
                inputs[digitIndex - 1].select();
            }
            break;
        case 'Tab':
            // Allow default tab behavior
            break;
    }
}

// Update meter reading value from digits
function updateMeterReading(meter) {
    const meterTypeName = getMeterTypeName(meter.meter_type_id);
    if (!meter.reading_digits) return;
    
    if (meterTypeName === 'electricity') {
        // Format: 5 digits + 1 decimal = XXXXX.X kWh
        const whole = meter.reading_digits.slice(0, 5).join('');
        const decimal = meter.reading_digits[5] || '0';
        meter.initial_reading = `${parseInt(whole || '0')}.${decimal}`;
    } else if (meterTypeName === 'water') {
        // Format: 4 kL digits + 3 litre digits + 1 tenth-litre decimal = XXXX XXX .X
        // First 4 = kL (kilolitres), next 3 = litres (0-999), last 1 = 1/10 litre
        const kl = parseInt(meter.reading_digits.slice(0, 4).join('') || '0');
        const litres = parseInt(meter.reading_digits.slice(4, 7).join('') || '0');
        const tenthLitre = parseInt(meter.reading_digits[7] || '0');
        // Total in litres = kL*1000 + litres + tenthLitre/10
        const totalLitres = kl * 1000 + litres + tenthLitre / 10;
        // Store as kL with 4 decimal places
        meter.initial_reading = (totalLitres / 1000).toFixed(4);
    }
}

// Get the formatted reading value for display
function getReadingValue(meter) {
    const meterTypeName = getMeterTypeName(meter.meter_type_id);
    if (!meter.reading_digits) return '0';
    
    if (meterTypeName === 'electricity') {
        const whole = meter.reading_digits.slice(0, 5).join('');
        const decimal = meter.reading_digits[5] || '0';
        return `${parseInt(whole || '0')}.${decimal}`;
    } else if (meterTypeName === 'water') {
        const kl = parseInt(meter.reading_digits.slice(0, 4).join('') || '0');
        const litres = parseInt(meter.reading_digits.slice(4, 7).join('') || '0');
        const tenthLitre = parseInt(meter.reading_digits[7] || '0');
        const totalLitres = kl * 1000 + litres + tenthLitre / 10;
        return (totalLitres / 1000).toFixed(4);
    }
    return '0';
}

function openCreateModal() {
    resetForm();
    showModal.value = true;
}

async function editUser(userId) {
    resetForm();
    editingUserId.value = userId;
    loading.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.getUserData, userId), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        const data = await response.json();
        
        if (data.status === 200) {
            const user = data.data;
            formData.name = user.name;
            formData.email = user.email;
            formData.contact_number = user.contact_number;
            formData.password = '';
            
            // Map sites with expanded state
            formData.sites = (user.sites || []).map(site => ({
                id: site.id,
                title: site.title || '',
                address: site.address || '',
                lat: site.lat || 0,
                lng: site.lng || 0,
                region_id: site.region_id || '',
                billing_type: site.billing_type || 'monthly',
                expanded: true,
                accounts: (site.accounts || []).map(account => ({
                    id: account.id,
                    account_name: account.account_name || '',
                    account_number: account.account_number || '',
                    tariff_template_id: account.tariff_template_id || '',
                    billing_date: account.billing_date || '',
                    bill_day: account.bill_day || '',
                    read_day: account.read_day || '',
                    expanded: true,
                    meters: (account.meters || []).map(meter => ({
                        id: meter.id,
                        meter_title: meter.meter_title || '',
                        meter_number: meter.meter_number || '',
                        meter_type_id: meter.meter_type_id || '',
                        sample_reading: '',
                        initial_reading: '',
                        initial_reading_date: new Date().toISOString().split('T')[0],
                        reading_digits: []
                    }))
                }))
            }));
            
            showModal.value = true;
        } else {
            showNotification('Error loading user data', 'danger');
        }
    } catch (error) {
        showNotification('Error loading user: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

function closeModal() {
    showModal.value = false;
    resetForm();
}

// Site management
function addSite() {
    formData.sites.push({
        title: '',
        address: '',
        lat: 0,
        lng: 0,
        region_id: formData.default_region_id || '',
        billing_type: formData.default_billing_type || 'monthly',
        expanded: true,
        accounts: []
    });
}

function removeSite(index) {
    formData.sites.splice(index, 1);
}

function toggleSite(index) {
    formData.sites[index].expanded = !formData.sites[index].expanded;
}

// Account management
function addAccount(siteIndex) {
    formData.sites[siteIndex].accounts.push({
        account_name: '',
        account_number: '',
        tariff_template_id: formData.default_tariff_template_id || '',
        billing_date: '',
        bill_day: '',
        read_day: '',
        expanded: true,
        meters: []
    });
}

function removeAccount(siteIndex, accountIndex) {
    formData.sites[siteIndex].accounts.splice(accountIndex, 1);
}

function toggleAccount(siteIndex, accountIndex) {
    formData.sites[siteIndex].accounts[accountIndex].expanded = !formData.sites[siteIndex].accounts[accountIndex].expanded;
}

// Meter management
function addMeter(siteIndex, accountIndex) {
    formData.sites[siteIndex].accounts[accountIndex].meters.push({
        meter_title: '',
        meter_number: '',
        meter_type_id: '',
        sample_reading: '',
        initial_reading: '',
        initial_reading_date: new Date().toISOString().split('T')[0],
        reading_digits: []
    });
}

function removeMeter(siteIndex, accountIndex, meterIndex) {
    formData.sites[siteIndex].accounts[accountIndex].meters.splice(meterIndex, 1);
}

// Save user
async function saveUser() {
    // Basic validation
    if (!formData.name || !formData.email || !formData.contact_number) {
        showNotification('Please fill in all required fields', 'danger');
        return;
    }
    
    if (!editingUserId.value && !formData.password) {
        showNotification('Password is required for new users', 'danger');
        return;
    }
    
    saving.value = true;
    
    try {
        const url = editingUserId.value 
            ? buildUrl(props.apiUrls.update, editingUserId.value)
            : props.apiUrls.store;
        
        const method = editingUserId.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
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
            closeModal();
            searchUsers(); // Refresh list
        } else {
            showNotification(data.message || 'Error saving user', 'danger');
        }
    } catch (error) {
        showNotification('Error saving user: ' + error.message, 'danger');
    } finally {
        saving.value = false;
    }
}

// Delete user
function confirmDeleteUser(user) {
    deleteMessage.value = `Are you sure you want to delete user "${user.name}" and all associated data (sites, accounts, meters, readings)?`;
    deleteAction.value = () => deleteUser(user.id);
    showDeleteModal.value = true;
}

async function deleteUser(userId) {
    loading.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.delete, userId), {
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
            searchUsers(); // Refresh list
        } else {
            showNotification(data.message || 'Error deleting user', 'danger');
        }
    } catch (error) {
        showNotification('Error deleting user: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

// Test user functions
async function generateTestUser() {
    loading.value = true;
    
    try {
        const response = await fetch(props.apiUrls.generateTestUser, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(`Test user created: ${data.email}`, 'success');
            searchUsers(); // Refresh list
        } else {
            showNotification(data.message || 'Error generating test user', 'danger');
        }
    } catch (error) {
        showNotification('Error generating test user: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

function confirmDeleteTestUsers() {
    deleteMessage.value = 'Are you sure you want to delete ALL test users (emails ending with @test.com) and their associated data?';
    deleteAction.value = deleteTestUsers;
    showDeleteModal.value = true;
}

async function deleteTestUsers() {
    loading.value = true;
    
    try {
        const response = await fetch(props.apiUrls.deleteTestUsers, {
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
            searchUsers(); // Refresh list
        } else {
            showNotification(data.message || 'Error deleting test users', 'danger');
        }
    } catch (error) {
        showNotification('Error deleting test users: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

// Clone user
async function cloneUser(userId) {
    loading.value = true;
    
    try {
        const response = await fetch(buildUrl(props.apiUrls.cloneUser, userId), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.status === 200) {
            showNotification(data.message, 'success');
            searchUsers(); // Refresh list
        } else {
            showNotification(data.message || 'Error cloning user', 'danger');
        }
    } catch (error) {
        showNotification('Error cloning user: ' + error.message, 'danger');
    } finally {
        loading.value = false;
    }
}

// Execute delete action
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
.user-management-wrapper {
    padding: 0;
}

.modal.show {
    display: block;
}

.badge-warning {
    background-color: #f6c23e;
    color: #1f2d3d;
}

.badge-success {
    background-color: #1cc88a;
}

.badge-primary {
    background-color: #4e73df;
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

.border-left {
    border-left-width: 3px !important;
}

.form-control-sm {
    font-size: 0.85rem;
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

/* Billing Mode Toggle */
.billing-mode-toggle .btn-group-toggle {
    display: flex;
}

.billing-mode-toggle .btn-group-toggle .btn {
    flex: 1;
}

.billing-mode-toggle .btn-group-toggle input[type="radio"] {
    display: none;
}

/* Pigeonhole Meter Reading Inputs - Quasar Style */
.initial-reading-section {
    border: 1px solid #dee2e6;
}

.pigeonhole-container {
    padding: 10px 0;
}

.pigeonhole-group {
    display: inline-flex;
    gap: 3px;
}

.pigeonhole-input {
    width: 42px;
    height: 52px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    border: 2px solid #333;
    border-radius: 4px;
    outline: none;
    transition: all 0.2s ease;
}

.pigeonhole-input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
}

/* Black boxes for whole number digits */
.pigeonhole-black {
    background-color: #1a1a1a;
    color: #ffffff;
    border-color: #333;
}

.pigeonhole-black:focus {
    background-color: #2d2d2d;
    border-color: #4e73df;
}

/* Red box for decimal digit */
.pigeonhole-red {
    background-color: #dc3545;
    color: #ffffff;
    border-color: #c82333;
}

.pigeonhole-red:focus {
    background-color: #e74c5c;
    border-color: #4e73df;
}

/* Decimal separator */
.pigeonhole-decimal {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    padding: 0 5px;
    display: inline-flex;
    align-items: center;
}

/* Meter card styling */
.meter-card {
    background-color: #fafafa;
    border-radius: 4px;
    padding: 10px;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .pigeonhole-input {
        width: 36px;
        height: 44px;
        font-size: 1.2rem;
    }
    
    .pigeonhole-decimal {
        font-size: 1.5rem;
        padding: 0 3px;
    }
    
    .pigeonhole-group {
        gap: 2px;
    }
}

/* Large screen touch-friendly targets */
@media (min-width: 769px) {
    .pigeonhole-input {
        cursor: text;
    }
    
    .pigeonhole-input:hover {
        border-color: #4e73df;
    }
}
</style>
