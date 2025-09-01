<script>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import NotificationBanner from '@/components/NotificationBanner.vue';

export default {
    name: 'Dashboard',
    components: {
        Head,
        AppLayout,
        NotificationBanner
    },
    props: {
        leaveRequests: {
            type: Array,
            required: true
        },
        pendingApprovals: {
            type: Array,
            required: true
        },
        adminLeaveRequests: {
            type: Object,
            required: false
        },
        user: {
            type: Object,
            required: true
        },
        userPermissions: {
            type: Object,
            required: true
        },
        filters: {
            type: Object,
            required: false
        }
    },
    data() {
        return {
            breadcrumbs: [
                {
                    title: 'Dashboard',
                    href: '/'
                }
            ],
            showForm: false,
            notification: {
                show: false,
                type: 'success',
                title: '',
                message: ''
            },
            form: useForm({
                type: 'vacation',
                start_date: '',
                end_date: '',
                reason: ''
            }),
            typeLabels: {
                vacation: 'Ferie',
                personal: 'Permesso'
            },
            statusLabels: {
                pending: 'In attesa',
                approved: 'Approvato',
                rejected: 'Rifiutato',
                cancelled: 'Cancellato'
            },
            showOnBehalfForm: false,
            selectedUser: null,
            users: [],
            showRejectModalState: false,
            selectedRequestForReject: null,
            rejectForm: useForm({
                notes: ''
            }),
            adminFilters: {
                status: '',
                type: '',
                user: '',
                department: '',
                dateFrom: '',
                dateTo: ''
            },
            currentPage: 1,
            itemsPerPage: 10,
            myRequestsFilters: {
                status: '',
                type: '',
                dateFrom: '',
                dateTo: ''
            },
            myRequestsCurrentPage: 1,
            myRequestsItemsPerPage: 5,
            page: usePage()
        }
    },
    computed: {
        today() {
            const now = new Date();
            return now.toISOString().split('T')[0];
        },
        minEndDate() {
            return this.form.start_date || this.today;
        },
        myRequests() {
            return this.leaveRequests.filter(req => !req.user || req.user.id === this.user.id);
        },
        approvedLeaves() {
            return this.myRequests.filter(req => req.status === 'approved');
        },
        pendingLeaves() {
            return this.myRequests.filter(req => req.status === 'pending');
        },
        rejectedLeaves() {
            return this.myRequests.filter(req => req.status === 'rejected');
        },
        cancelledLeaves() {
            return this.myRequests.filter(req => req.status === 'cancelled');
        },
        filteredRequests() {
            if (!this.userPermissions.isAdminIT) return [];
            return this.adminLeaveRequests?.data || [];
        },
        paginatedRequests() {
            return this.filteredRequests;
        },
        totalPages() {
            return this.adminLeaveRequests?.last_page || 1;
        },
        filteredMyRequests() {
            let filtered = this.leaveRequests;

            if (this.myRequestsFilters.status) {
                filtered = filtered.filter(req => req.status === this.myRequestsFilters.status);
            }

            if (this.myRequestsFilters.type) {
                filtered = filtered.filter(req => req.type === this.myRequestsFilters.type);
            }

            if (this.myRequestsFilters.dateFrom) {
                filtered = filtered.filter(req => req.start_date >= this.myRequestsFilters.dateFrom);
            }

            if (this.myRequestsFilters.dateTo) {
                filtered = filtered.filter(req => req.end_date <= this.myRequestsFilters.dateTo);
            }

            return filtered;
        },
        paginatedMyRequests() {
            const start = (this.myRequestsCurrentPage - 1) * this.myRequestsItemsPerPage;
            const end = start + this.myRequestsItemsPerPage;
            return this.filteredMyRequests.slice(start, end);
        },
        myRequestsTotalPages() {
            return Math.ceil(this.filteredMyRequests.length / this.myRequestsItemsPerPage);
        }
    },
    created() {
        // Initialize adminFilters with props values
        this.adminFilters = {
            status: this.filters?.status || '',
            type: this.filters?.type || '',
            user: this.filters?.user_name || '',
            department: this.filters?.department || '',
            dateFrom: this.filters?.start_date || '',
            dateTo: this.filters?.end_date || ''
        };
    },
    watch: {
        'page.props.flash': {
            handler(flash) {
                if (flash?.success) {
                    this.showNotification('success', 'Operazione completata', flash.success);
                }
            },
            immediate: true
        },
        'page.props.errors': {
            handler(errors) {
                if (errors && Object.keys(errors).length > 0) {
                    const errorMessage = Object.values(errors)[0];
                    this.showNotification('error', 'Errore', errorMessage);
                }
            },
            immediate: true
        }
    },
    methods: {
        showNotification(type, title, message) {
            this.notification.show = true;
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
        },
        hideNotification() {
            this.notification.show = false;
        },
        validateDates() {
            if (this.form.end_date && this.form.start_date && this.form.end_date < this.form.start_date) {
                this.form.end_date = this.form.start_date;
            }
        },
        clearFilters() {
            this.adminFilters.status = '';
            this.adminFilters.type = '';
            this.adminFilters.user = '';
            this.adminFilters.department = '';
            this.adminFilters.dateFrom = '';
            this.adminFilters.dateTo = '';
            this.currentPage = 1;
            this.applyFilters();
        },
        applyFilters() {
            this.currentPage = 1;
            this.$inertia.get('/dashboard', {
                status: this.adminFilters.status,
                type: this.adminFilters.type,
                user_name: this.adminFilters.user,
                department: this.adminFilters.department,
                start_date: this.adminFilters.dateFrom,
                end_date: this.adminFilters.dateTo
            }, {
                preserveState: true,
                preserveScroll: true
            });
        },
        changePage(page) {
            this.$inertia.get('/dashboard', {
                status: this.adminFilters.status,
                type: this.adminFilters.type,
                user_name: this.adminFilters.user,
                department: this.adminFilters.department,
                start_date: this.adminFilters.dateFrom,
                end_date: this.adminFilters.dateTo,
                page: page
            }, {
                preserveState: true,
                preserveScroll: true
            });
        },
        clearMyRequestsFilters() {
            this.myRequestsFilters.status = '';
            this.myRequestsFilters.type = '';
            this.myRequestsFilters.dateFrom = '';
            this.myRequestsFilters.dateTo = '';
            this.myRequestsCurrentPage = 1;
        },
        changeMyRequestsPage(page) {
            if (page >= 1 && page <= this.myRequestsTotalPages) {
                this.myRequestsCurrentPage = page;
            }
        },
        submitRequest() {
            this.form.post('/leave-requests', {
                onSuccess: () => {
                    this.form.reset();
                    this.showForm = false;
                    this.showNotification('success', 'Richiesta Inviata', 'La tua richiesta è stata inviata con successo e sarà valutata dal tuo responsabile.');
                }
            });
        },
        deleteRequest(id) {
            if (confirm('Sei sicuro di voler eliminare questa richiesta?')) {
                useForm({}).delete(`/leave-requests/${id}`);
            }
        },
        approveRequest(id, notes = '') {
            useForm({ notes }).post(`/leave-requests/${id}/approve`);
        },
        rejectRequest(id, notes) {
            useForm({ notes }).post(`/leave-requests/${id}/reject`);
        },
        cancelRequest(id, reason = '') {
            useForm({ reason }).post(`/leave-requests/${id}/cancel`);
        },
        async loadUsers() {
            if (this.userPermissions.isHR) {
                try {
                    const response = await fetch('/api/users');
                    this.users = await response.json();
                } catch (error) {
                    console.error('Error loading users:', error);
                }
            }
        },
        showRejectModal(request) {
            this.selectedRequestForReject = request;
            this.showRejectModalState = true;
            this.rejectForm.reset();
        },
        submitReject() {
            if (!this.selectedRequestForReject) return;
            
            this.rejectForm.post(`/leave-requests/${this.selectedRequestForReject.id}/reject`, {
                onSuccess: () => {
                    this.showRejectModalState = false;
                    this.selectedRequestForReject = null;
                    this.rejectForm.reset();
                }
            });
        },
        getRoleLabel(role) {
            const labels = {
                'ADMIN_IT': 'Admin IT',
                'HR': 'HR',
                'MANAGER': 'Manager',
                'EMPLOYEE': 'Dipendente'
            };
            return labels[role] || role;
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('it-IT');
        },
        getGreeting() {
            const hour = new Date().getHours();
            if (hour < 12) {
                return 'Buongiorno';
            } else if (hour < 18) {
                return 'Buon pomeriggio';
            } else {
                return 'Buonasera';
            }
        }
    }
}
</script>

<template>
    <Head title="Dashboard - Gestionale Ferie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Notification Banner -->
        <NotificationBanner
            :show="notification.show"
            :type="notification.type"
            :title="notification.title"
            :message="notification.message"
            @hide="hideNotification"
        />
        
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto rounded-xl p-6">
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-2xl">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">{{ getGreeting() }}, {{ user.name }}! 👋</h1>
                        <p class="text-blue-100 text-lg">
                            Gestisci le tue ferie e permessi in modo semplice
                        </p>
                        <div class="mt-3 space-y-1">
                            <div class="flex items-center text-blue-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"></path>
                                </svg>
                                Ruolo: {{ getRoleLabel(user.role) }}
                            </div>
                            <div v-if="user.organizational_unit" class="flex items-center text-blue-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Unità: {{ user.organizational_unit.name }}
                            </div>
                            <div v-if="user.manager" class="flex items-center text-blue-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Responsabile: {{ user.manager.name }}
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button
                            v-if="userPermissions.canCreateRequests"
                            @click="showForm = !showForm"
                            class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg border border-white/20"
                        >
                        <svg v-if="!showForm" class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                            {{ showForm ? 'Annulla' : 'Nuova Richiesta' }}
                        </button>
                        
                        <!-- Manager Pending Approvals Badge -->
                        <div v-if="userPermissions.canApproveRequests && pendingApprovals.length > 0" class="relative">
                            <button
                                @click="$inertia.visit('/manager/dashboard')"
                                class="bg-amber-500/20 backdrop-blur-sm hover:bg-amber-500/30 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg border border-amber-400/20"
                            >
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Richieste da Approvare
                            </button>
                            <!-- Badge -->
                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center animate-pulse shadow-lg">
                                {{ pendingApprovals.length }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Form per nuova richiesta -->
            <div v-if="showForm" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 shadow-xl">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Nuova Richiesta</h2>
                        <p class="text-gray-600 dark:text-gray-400">Compila i dettagli per la tua richiesta di ferie o permesso</p>
                    </div>
                </div>
                <form @submit.prevent="submitRequest" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo di richiesta
                            </label>
                            <select
                                v-model="form.type"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                            >
                                <option value="vacation">🏖️ Ferie</option>
                                <option value="personal">📋 Permesso</option>
                            </select>
                        </div>
                        <div></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data inizio
                            </label>
                            <input
                                v-model="form.start_date"
                                type="date"
                                required
                                :min="today"
                                @change="validateDates"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Data fine
                            </label>
                            <input
                                v-model="form.end_date"
                                type="date"
                                required
                                :min="minEndDate"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 shadow-sm hover:shadow-md"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo (opzionale)
                        </label>
                        <textarea
                            v-model="form.reason"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 shadow-sm hover:shadow-md resize-none"
                            placeholder="Inserisci il motivo della richiesta (opzionale)..."
                        ></textarea>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:from-gray-400 disabled:to-gray-500 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center"
                        >
                            <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Invio in corso...' : 'Invia Richiesta' }}
                        </button>
                        <button
                            type="button"
                            @click="showForm = false"
                            class="px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500"
                        >
                            Annulla
                        </button>
                    </div>
                </form>
            </div>

            <!-- Statistiche (non visibili per Admin IT) -->
            <div v-if="!userPermissions.isAdminIT" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="group bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl border border-green-200 dark:border-green-800 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-700 dark:text-green-300 mb-1">Ferie Approvate</p>
                            <p class="text-3xl font-bold text-green-800 dark:text-green-200">{{ approvedLeaves.length }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">✅ Confermate</p>
                        </div>
                        <div class="p-4 bg-green-100 dark:bg-green-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-2xl border border-yellow-200 dark:border-yellow-800 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mb-1">In Attesa</p>
                            <p class="text-3xl font-bold text-yellow-800 dark:text-yellow-200">{{ pendingLeaves.length }}</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">⏳ Da approvare</p>
                        </div>
                        <div class="p-4 bg-yellow-100 dark:bg-yellow-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 rounded-2xl border border-red-200 dark:border-red-800 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-1">Rifiutate</p>
                            <p class="text-3xl font-bold text-red-800 dark:text-red-200">{{ rejectedLeaves.length }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">❌ Non approvate</p>
                        </div>
                        <div class="p-4 bg-red-100 dark:bg-red-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-1">Giorni Totali</p>
                            <p class="text-3xl font-bold text-blue-800 dark:text-blue-200">
                                {{ approvedLeaves.reduce((sum, leave) => sum + leave.days_count, 0) }}
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">📅 Giorni utilizzati</p>
                        </div>
                        <div class="p-4 bg-blue-100 dark:bg-blue-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ferie Approvate Future (non visibili per Admin IT) -->
            <div v-if="!userPermissions.isAdminIT" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ferie e Permessi Approvati</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Le tue richieste approvate per il futuro</p>
                </div>
                <div class="p-6">
                    <div v-if="approvedLeaves.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nessuna ferie approvata</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Non hai ancora ferie approvate per il futuro.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="leave in approvedLeaves"
                            :key="leave.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ typeLabels[leave.type] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ leave.days_count }} {{ leave.days_count === 1 ? 'giorno' : 'giorni' }}
                                        <span v-if="leave.reason"> • {{ leave.reason }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Richieste in Attesa -->
            <div v-if="pendingLeaves.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Richieste in Attesa di Approvazione</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Le tue richieste in attesa di essere approvate dal responsabile</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="leave in pendingLeaves"
                            :key="leave.id"
                            class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        {{ typeLabels[leave.type] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ leave.days_count }} {{ leave.days_count === 1 ? 'giorno' : 'giorni' }}
                                        <span v-if="leave.reason"> • {{ leave.reason }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Richiesta inviata il {{ formatDate(leave.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="deleteRequest(leave.id)"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
                            >
                                Elimina
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Richieste Rifiutate -->
            <div v-if="rejectedLeaves.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Richieste Rifiutate</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Storico delle richieste rifiutate dal responsabile</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="leave in rejectedLeaves"
                            :key="leave.id"
                            class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        {{ typeLabels[leave.type] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ leave.days_count }} {{ leave.days_count === 1 ? 'giorno' : 'giorni' }}
                                        <span v-if="leave.reason"> • {{ leave.reason }}</span>
                                    </p>
                                    <p v-if="leave.manager_notes" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                        <strong>Motivo rifiuto:</strong> {{ leave.manager_notes }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Rifiutata il {{ formatDate(leave.updated_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Rifiutata
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin IT - Vista Completa con Filtri -->
            <div v-if="userPermissions.isAdminIT" class="bg-white dark:bg-gray-800 rounded-2xl border border-blue-200 dark:border-blue-700 shadow-xl">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-xl mr-4">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Vista Amministratore IT</h2>
                                <p class="text-gray-600 dark:text-gray-400">Tutte le richieste di ferie e permessi (sola lettura)</p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ filteredRequests.length }} richieste trovate
                        </div>
                    </div>
                </div>

                <!-- Filtri -->
                <div class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stato</label>
                            <select
                                v-model="adminFilters.status"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            >
                                <option value="">Tutti</option>
                                <option value="pending">In attesa</option>
                                <option value="approved">Approvato</option>
                                <option value="rejected">Rifiutato</option>
                                <option value="cancelled">Cancellato</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select
                                v-model="adminFilters.type"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            >
                                <option value="">Tutti</option>
                                <option value="vacation">Ferie</option>
                                <option value="personal">Permesso</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utente</label>
                            <input
                                v-model="adminFilters.user"
                                @input="applyFilters"
                                type="text"
                                placeholder="Nome utente..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dipartimento</label>
                            <input
                                v-model="adminFilters.department"
                                @input="applyFilters"
                                type="text"
                                placeholder="Dipartimento..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Da</label>
                            <input
                                v-model="adminFilters.dateFrom"
                                @change="applyFilters"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">A</label>
                            <input
                                v-model="adminFilters.dateTo"
                                @change="applyFilters"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
                        >
                            Pulisci Filtri
                        </button>
                    </div>
                </div>

                <!-- Lista Richieste -->
                <div class="p-6">
                    <div v-if="filteredRequests.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nessuna richiesta trovata</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Prova a modificare i filtri per vedere più risultati.</p>
                    </div>
                    
                    <div v-else class="space-y-4">
                        <div
                            v-for="request in paginatedRequests"
                            :key="request.id"
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600 p-6 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-3">
                                        <div class="flex-shrink-0 mr-4">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ request.user?.name }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ request.user?.organizational_unit?.name }} • {{ getRoleLabel(request.user?.role || '') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ typeLabels[request.type] }}
                                            </span>
                                            <span :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': request.status === 'pending',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': request.status === 'approved',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': request.status === 'rejected',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': request.status === 'cancelled'
                                            }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ statusLabels[request.status] }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo</p>
                                            <p class="text-gray-900 dark:text-white">{{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Durata</p>
                                            <p class="text-gray-900 dark:text-white">{{ request.days_count }} {{ request.days_count === 1 ? 'giorno' : 'giorni' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Richiesta</p>
                                            <p class="text-gray-900 dark:text-white">{{ formatDate(request.created_at) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div v-if="request.reason" class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo</p>
                                        <p class="text-gray-900 dark:text-white bg-white dark:bg-gray-600 p-3 rounded-lg text-sm">{{ request.reason }}</p>
                                    </div>
                                    
                                    <div v-if="request.manager_notes" class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note del responsabile</p>
                                        <p class="text-gray-900 dark:text-white bg-white dark:bg-gray-600 p-3 rounded-lg text-sm">{{ request.manager_notes }}</p>
                                    </div>
                                    
                                    <div v-if="request.approver" class="text-xs text-gray-500 dark:text-gray-400">
                                        Gestita da: {{ request.approver.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paginazione -->
                    <div v-if="totalPages > 1" class="mt-6 flex items-center justify-center space-x-2">
                        <button
                            @click="changePage((adminLeaveRequests?.current_page || 1) - 1)"
                            :disabled="(adminLeaveRequests?.current_page || 1) === 1"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Precedente
                        </button>
                        
                        <div class="flex space-x-1">
                            <button
                                v-for="page in Math.min(totalPages, 5)"
                                :key="page"
                                @click="changePage(page)"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-lg',
                                    (adminLeaveRequests?.current_page || 1) === page
                                        ? 'bg-blue-600 text-white'
                                        : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="changePage((adminLeaveRequests?.current_page || 1) + 1)"
                            :disabled="(adminLeaveRequests?.current_page || 1) === totalPages"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Successivo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal per Rifiuto Richiesta -->
            <div v-if="showRejectModalState" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-xl mr-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Rifiuta Richiesta</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ selectedRequestForReject?.user?.name }}</p>
                        </div>
                    </div>
                    
                    <form @submit.prevent="submitReject" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Motivo del rifiuto *
                            </label>
                            <textarea
                                v-model="rejectForm.notes"
                                rows="4"
                                required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white transition-all duration-200 shadow-sm hover:shadow-md resize-none"
                                placeholder="Inserisci il motivo del rifiuto..."
                            ></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Il motivo sarà visibile al richiedente</p>
                        </div>
                        
                        <div class="flex gap-4 pt-4">
                            <button
                                type="submit"
                                :disabled="rejectForm.processing || !rejectForm.notes.trim()"
                                class="flex-1 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 disabled:from-gray-400 disabled:to-gray-500 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center"
                            >
                                <svg v-if="!rejectForm.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ rejectForm.processing ? 'Rifiuto in corso...' : 'Conferma Rifiuto' }}
                            </button>
                            <button
                                type="button"
                                @click="showRejectModalState = false"
                                class="px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all duration-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500"
                            >
                                Annulla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
