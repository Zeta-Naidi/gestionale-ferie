<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="opacity-0 transform translate-x-full"
            enter-to-class="opacity-100 transform translate-x-0"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 transform translate-x-0"
            leave-to-class="opacity-0 transform translate-x-full"
        >
            <div
                v-if="show"
                :class="[
                    'fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-2xl border max-w-sm',
                    type === 'success' ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white border-green-500 dark:from-green-500 dark:to-emerald-500 dark:border-green-400' :
                    type === 'error' ? 'bg-gradient-to-r from-red-600 to-rose-600 text-white border-red-500 dark:from-red-500 dark:to-rose-500 dark:border-red-400' :
                    type === 'warning' ? 'bg-gradient-to-r from-yellow-600 to-amber-600 text-white border-yellow-500 dark:from-yellow-500 dark:to-amber-500 dark:border-yellow-400' :
                    'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-blue-500 dark:from-blue-500 dark:to-indigo-500 dark:border-blue-400'
                ]"
            >
                <div class="flex items-start">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mr-3">
                        <svg v-if="type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg v-else-if="type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg v-else-if="type === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1">
                        <p class="font-semibold text-sm">{{ title }}</p>
                        <p v-if="message" class="text-xs mt-1 opacity-90">{{ message }}</p>
                    </div>
                    
                    <!-- Close button -->
                    <button
                        @click="hide"
                        class="ml-4 opacity-70 hover:opacity-100 transition-opacity"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
export default {
    name: 'NotificationBanner',
    props: {
        show: {
            type: Boolean,
            required: true
        },
        type: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        message: {
            type: String,
            required: false
        },
        autoHide: {
            type: Boolean,
            default: true
        },
        autoHideDelay: {
            type: Number,
            default: 4000
        }
    },
    emits: ['hide'],
    data() {
        return {
            autoHideTimer: null
        }
    },
    watch: {
        show: {
            handler(newShow) {
                if (newShow && this.autoHide) {
                    if (this.autoHideTimer) {
                        clearTimeout(this.autoHideTimer)
                    }
                    this.autoHideTimer = setTimeout(() => {
                        this.hide()
                    }, this.autoHideDelay)
                } else if (!newShow && this.autoHideTimer) {
                    clearTimeout(this.autoHideTimer)
                    this.autoHideTimer = null
                }
            },
            immediate: true
        }
    },
    methods: {
        hide() {
            this.$emit('hide')
        }
    }
}
</script>
