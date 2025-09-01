<script>
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

export default {
    components: {
        Head,
        Link
    },
    methods: {
        dashboard() {
            return dashboard();
        },
        login() {
            return login();
        },
        register() {
            return register();
        }
    }
};
</script>

<template>
    <Head title="Gestionale Ferie - Benvenuto">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-900 dark:to-indigo-900">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <!-- Navigation -->
        <nav class="relative z-10 flex items-center justify-between p-6 lg:px-8">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Gestionale Ferie</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sistema di gestione ferie aziendale</p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                    </svg>
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        Accedi
                    </Link>
                    <Link
                        :href="register()"
                        class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-6 py-2.5 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 border border-gray-200 dark:border-gray-700"
                    >
                        Registrati
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative z-10 flex items-center justify-center min-h-[calc(100vh-120px)] px-6">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-full text-blue-600 dark:text-blue-400 text-sm font-medium mb-6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Sistema moderno e intuitivo
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                        Gestisci le
                        <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">
                            Ferie
                        </span>
                        <br />del tuo team
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto leading-relaxed">
                        Una piattaforma completa per la gestione delle richieste di ferie e permessi. 
                        Semplifica i processi aziendali con un'interfaccia moderna e funzionalità avanzate.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="register()"
                            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-blue-500/25"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Inizia Gratuitamente
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="login()"
                            class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-8 py-4 rounded-2xl font-semibold text-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 transform hover:scale-105 shadow-lg"
                        >
                            Accedi al Sistema
                        </Link>
                        <Link
                            v-else
                            :href="dashboard()"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-green-500/25"
                        >
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                            </svg>
                            Vai alla Dashboard
                        </Link>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-3 gap-8 mb-16">
                    <div class="group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-white/20 dark:border-gray-700/50">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">👥 Gestione Utenti</h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Sistema completo per dipendenti e manager con ruoli differenziati e permessi specifici.
                        </p>
                    </div>

                    <div class="group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-white/20 dark:border-gray-700/50">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">✅ Approvazioni</h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Flusso di approvazione semplificato con notifiche in tempo reale e storico completo.
                        </p>
                    </div>

                    <div class="group bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-white/20 dark:border-gray-700/50">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">📊 Statistiche</h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            Dashboard avanzate con metriche dettagliate e report personalizzabili per ogni utente.
                        </p>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-3xl p-12 border border-white/20 dark:border-gray-700/50 shadow-2xl">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Perché scegliere il nostro sistema?
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Una soluzione completa che semplifica la gestione delle ferie e migliora l'efficienza del tuo team.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">⏰ Risparmio di Tempo</h3>
                                <p class="text-gray-600 dark:text-gray-300">Automatizza i processi di richiesta e approvazione, riducendo il tempo amministrativo del 70%.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">🔒 Sicurezza Garantita</h3>
                                <p class="text-gray-600 dark:text-gray-300">Dati protetti con crittografia avanzata e backup automatici per la massima sicurezza.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">🎨 Interfaccia Intuitiva</h3>
                                <p class="text-gray-600 dark:text-gray-300">Design moderno e user-friendly che non richiede formazione specifica per l'utilizzo.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">📈 Report Dettagliati</h3>
                                <p class="text-gray-600 dark:text-gray-300">Analisi complete e statistiche avanzate per ottimizzare la gestione delle risorse umane.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 mt-20">
            <div class="max-w-6xl mx-auto px-6 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-3 mb-4 md:mb-0">
                        <div class="p-2 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">Gestionale Ferie</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">© 2024 - Sistema di gestione ferie</p>
                        </div>
                    </div>
                    
                    <div class="text-center text-gray-600 dark:text-gray-400">
                        <p class="text-sm">Sviluppato con ❤️ per semplificare la gestione aziendale</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
