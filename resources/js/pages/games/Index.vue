<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import GameRow from '@/components/GameRow.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/games';
import type { Game } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Games',
                href: index(),
            },
        ],
    },
});

defineProps<{
    games: Game[];
    pagination: {
        current_page: number;
        last_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();
</script>

<template>
    <Head title="Games" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between gap-4">
            <Heading
                title="Games"
                :description="`${pagination.total} logged`"
                class="mb-0!"
            />
            <Button as-child>
                <Link :href="create()"><Plus /> Log game</Link>
            </Button>
        </div>

        <section
            class="rounded-xl border border-sidebar-border/70 px-4 py-1 dark:border-sidebar-border"
        >
            <div v-if="games.length" class="divide-y">
                <GameRow
                    v-for="game in games"
                    :key="game.id"
                    :game="game"
                    editable
                />
            </div>
            <p v-else class="py-8 text-center text-sm text-muted-foreground">
                No games logged yet.
                <Link :href="create()" class="underline"
                    >Log your first game</Link
                >.
            </p>
        </section>

        <nav
            v-if="pagination.last_page > 1"
            class="flex items-center justify-between text-sm"
        >
            <Button
                variant="outline"
                size="sm"
                :disabled="!pagination.prev_page_url"
                as-child
            >
                <Link :href="pagination.prev_page_url ?? '#'" preserve-scroll
                    >Previous</Link
                >
            </Button>
            <span class="text-muted-foreground"
                >Page {{ pagination.current_page }} of
                {{ pagination.last_page }}</span
            >
            <Button
                variant="outline"
                size="sm"
                :disabled="!pagination.next_page_url"
                as-child
            >
                <Link :href="pagination.next_page_url ?? '#'" preserve-scroll
                    >Next</Link
                >
            </Button>
        </nav>
    </div>
</template>
