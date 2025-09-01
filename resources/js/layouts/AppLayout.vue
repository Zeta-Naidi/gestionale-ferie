<script lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

export default {
    name: 'AppLayoutWrapper',
    components: {
        AppLayout
    },
    props: {
        breadcrumbs: {
            type: Array as () => BreadcrumbItemType[],
            default: () => []
        }
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
