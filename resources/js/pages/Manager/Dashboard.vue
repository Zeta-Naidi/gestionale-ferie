<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface LeaveRequest {
    id: number;
    type: string;
    start_date: string;
    end_date: string;
    days_count: number;
    reason?: string;
    status: string;
    created_at: string;
    updated_at: string;
    manager_notes?: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

export default {
    name: 'ManagerDashboard',
    components: {
        Head,
        AppLayout
    },
    props: {
        pendingRequests: {
            type: Array as () => LeaveRequest[],
            required: true
        },
        recentApprovals: {
            type: Array as () => LeaveRequest[],
            required: true
        }
    },
    data() {
        return {
            breadcrumbs: [
                {
                    title: 'Manager Dashboard',
                    href: '/manager/dashboard'
                }
            ] as BreadcrumbItem[],
            selectedRequest: null as LeaveRequest | null,
            showApprovalModal: false,
            actionType: 'approve' as 'approve' | 'reject',
            approvalForm: useForm({
                manager_notes: ''
            }),
            filters: {
                type: '',
                dateFrom: '',
                dateTo: '',
                search: ''
            },
            currentPage: 1,
            itemsPerPage: 5,
            typeLabels: {
                vacation: 'Ferie',
                personal: 'Permesso'
            } as Record<string, string>,
            statusLabels: {
                pending: 'In attesa',
                approved: 'Approvato',
                rejected: 'Rifiutato'
            } as Record<string, string>,
            statusColors: {
                pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
            } as Record<string, string>
        }
    },
    computed: {
        filteredPendingRequests() {
            let filtered = this.pendingRequests;

            if (this.filters.type) {
                filtered = filtered.filter(req => req.type === this.filters.type);
            }

            if (this.filters.search) {
                filtered = filtered.filter(req => 
                    req.user.name.toLowerCase().includes(this.filters.search.toLowerCase())
                );
            }

            if (this.filters.dateFrom) {
                filtered = filtered.filter(req => req.start_date >= this.filters.dateFrom);
            }

            if (this.filters.dateTo) {
                filtered = filtered.filter(req => req.end_date <= this.filters.dateTo);
            }

            return filtered;
        },
        paginatedPendingRequests() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPendingRequests.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.filteredPendingRequests.length / this.itemsPerPage);
        }
    },
    methods: {
        openApprovalModal(request: LeaveRequest, action: 'approve' | 'reject') {
            this.selectedRequest = request;
            this.actionType = action;
            this.approvalForm.manager_notes = '';
            this.showApprovalModal = true;
        },
        submitApproval() {
            if (!this.selectedRequest) return;

            const routePath = this.actionType === 'approve' 
                ? `/manager/approve/${this.selectedRequest.id}` 
                : `/manager/reject/${this.selectedRequest.id}`;
            
            this.approvalForm.post(routePath, {
                onSuccess: () => {
                    this.showApprovalModal = false;
                    this.selectedRequest = null;
                    this.approvalForm.reset();
                }
            });
        },
        formatDate(dateString: string) {
            return new Date(dateString).toLocaleDateString('it-IT');
        },
        formatDateTime(dateString: string) {
            return new Date(dateString).toLocaleString('it-IT');
        },
        clearFilters() {
            this.filters.type = '';
            this.filters.dateFrom = '';
            this.filters.dateTo = '';
            this.filters.search = '';
            this.currentPage = 1;
        },
        changePage(page: number) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        }
    }
}
</script>

<template>
    <Head title="Manager Dashboard - Gestionale Ferie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto rounded-xl p-6">
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white shadow-2xl">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute -top-6 -right-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-white/5 rounded-full blur-xl"></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl mr-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-2">Dashboard Manager 👨‍💼</h1>
                            <p class="text-indigo-100 text-lg">
                                Gestisci le richieste di ferie e permessi del tuo team
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center text-indigo-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Approva, rifiuta e monitora le richieste in tempo reale
                    </div>
                </div>
            </div>

            <!-- Statistiche -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="group bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-amber-600 dark:text-amber-400 mb-2">Richieste in Attesa</p>
                            <p class="text-4xl font-bold text-amber-700 dark:text-amber-300 mb-1">{{ pendingRequests.length }}</p>
                            <p class="text-xs text-amber-600/70 dark:text-amber-400/70">⏰ Da gestire</p>
                        </div>
                        <div class="p-4 bg-amber-100 dark:bg-amber-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-amber-200 dark:border-amber-800">
                        <div class="flex items-center text-amber-600 dark:text-amber-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span class="text-sm font-medium">Priorità alta</span>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-2">Gestite di Recente</p>
                            <p class="text-4xl font-bold text-emerald-700 dark:text-emerald-300 mb-1">{{ recentApprovals.length }}</p>
                            <p class="text-xs text-emerald-600/70 dark:text-emerald-400/70">✅ Completate</p>
                        </div>
                        <div class="p-4 bg-emerald-100 dark:bg-emerald-800/50 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">Lavoro completato</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Richieste in Attesa -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-lg">
                <div class="p-8 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl mr-4">
                            <svg class="w-6 h-6 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Richieste in Attesa</h2>
                            <p class="text-gray-600 dark:text-gray-400">Richieste che necessitano della tua approvazione</p>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ filteredPendingRequests.length }} di {{ pendingRequests.length }} richieste
                        </div>
                    </div>
                </div>

                <!-- Filtri -->
                <div v-if="pendingRequests.length > 0" class="px-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select
                                v-model="filters.type"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                            >
                                <option value="">Tutti</option>
                                <option value="vacation">Ferie</option>
                                <option value="personal">Permesso</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Utente</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Nome utente..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Da</label>
                            <input
                                v-model="filters.dateFrom"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">A</label>
                            <input
                                v-model="filters.dateTo"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm"
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

                <div class="p-6">
                    <div v-if="filteredPendingRequests.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nessuna richiesta in attesa</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tutte le richieste sono state gestite.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div
                            v-for="request in paginatedPendingRequests"
                            :key="request.id"
                            class="group bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 rounded-2xl border border-amber-200 dark:border-amber-700 p-6 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-800 dark:to-yellow-800 rounded-xl flex items-center justify-center">
                                            <span class="text-lg">
                                                {{ request.type === 'vacation' ? '🏖️' : '📋' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mr-3">
                                                {{ request.user.name }}
                                            </h3>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800/50 dark:text-amber-200">
                                                {{ typeLabels[request.type] }}
                                            </span>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                📅 {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                                                <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-lg text-xs font-medium">
                                                    {{ request.days_count }} {{ request.days_count === 1 ? 'giorno' : 'giorni' }}
                                                </span>
                                            </p>
                                            <p v-if="request.reason" class="text-sm text-gray-600 dark:text-gray-400 italic">
                                                💬 "{{ request.reason }}"
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                                ⏰ Richiesta del {{ formatDateTime(request.created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <button
                                        @click="openApprovalModal(request, 'approve')"
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-300 transform hover:scale-105 shadow-md flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Approva
                                    </button>
                                    <button
                                        @click="openApprovalModal(request, 'reject')"
                                        class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-300 transform hover:scale-105 shadow-md flex items-center"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Rifiuta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginazione -->
                    <div v-if="totalPages > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Pagina {{ currentPage }} di {{ totalPages }} ({{ filteredPendingRequests.length }} richieste)
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="changePage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Precedente
                            </button>
                            
                            <template v-for="page in Math.min(totalPages, 5)" :key="page">
                                <button
                                    @click="changePage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-lg',
                                        page === currentPage
                                            ? 'bg-indigo-600 text-white'
                                            : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            
                            <button
                                @click="changePage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Successiva
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Richieste Gestite di Recente -->
            <div v-if="recentApprovals.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Richieste Gestite di Recente</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Le ultime richieste che hai approvato o rifiutato</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div
                            v-for="request in recentApprovals"
                            :key="request.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="statusColors[request.status]">
                                        {{ typeLabels[request.type] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ request.user.name }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                                        ({{ request.days_count }} {{ request.days_count === 1 ? 'giorno' : 'giorni' }})
                                    </p>
                                    <p v-if="request.manager_notes" class="text-sm text-gray-500 dark:text-gray-400">
                                        Note: {{ request.manager_notes }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="statusColors[request.status]">
                                    {{ statusLabels[request.status] }}
                                </span>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ formatDateTime(request.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal per Approvazione/Rifiuto -->
        <div v-if="showApprovalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ actionType === 'approve' ? 'Approva Richiesta' : 'Rifiuta Richiesta' }}
                </h3>
                
                <div v-if="selectedRequest" class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ selectedRequest.user.name }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ typeLabels[selectedRequest.type] }} • 
                        {{ formatDate(selectedRequest.start_date) }} - {{ formatDate(selectedRequest.end_date) }}
                    </p>
                </div>

                <form @submit.prevent="submitApproval">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ actionType === 'approve' ? 'Note (opzionale)' : 'Motivo del rifiuto' }}
                        </label>
                        <textarea
                            v-model="approvalForm.manager_notes"
                            rows="3"
                            :required="actionType === 'reject'"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            :placeholder="actionType === 'approve' ? 'Aggiungi note opzionali...' : 'Spiega il motivo del rifiuto...'"
                        ></textarea>
                    </div>
                    
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="approvalForm.processing"
                            class="flex-1 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                            :class="actionType === 'approve' 
                                ? 'bg-green-600 hover:bg-green-700 disabled:bg-gray-400' 
                                : 'bg-red-600 hover:bg-red-700 disabled:bg-gray-400'"
                        >
                            {{ approvalForm.processing ? 'Invio...' : (actionType === 'approve' ? 'Approva' : 'Rifiuta') }}
                        </button>
                        <button
                            type="button"
                            @click="showApprovalModal = false"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                        >
                            Annulla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
