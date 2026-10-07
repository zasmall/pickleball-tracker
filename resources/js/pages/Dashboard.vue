<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import GameRow from '@/components/GameRow.vue';
import Heading from '@/components/Heading.vue';
import LeaderboardTable from '@/components/LeaderboardTable.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { create, index as gamesIndex } from '@/routes/games';
import { index as playersIndex } from '@/routes/players';
import type { Game, LeaderboardRow } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    summary: {
        games: number;
        players: number;
        last_played_on: string | null;
    };
    leaderboard: LeaderboardRow[];
    recentGames: Game[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between gap-4">
            <Heading
                title="Pickleball Tracker"
                description="Your games and standings at a glance"
                class="mb-0!"
            />
            <Button as-child>
                <Link :href="create()"><Plus /> Log game</Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Games logged" :value="summary.games" />
            <StatCard label="Players" :value="summary.players" />
            <StatCard
                label="Last played"
                :value="
                    summary.last_played_on
                        ? formatDate(summary.last_played_on)
                        : '–'
                "
            />
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="font-medium">Leaderboard</h2>
                    <Link
                        :href="playersIndex()"
                        class="text-sm text-muted-foreground hover:underline"
                        >All players</Link
                    >
                </div>
                <LeaderboardTable
                    v-if="leaderboard.length"
                    :rows="leaderboard"
                />
                <p
                    v-else
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    No players yet.
                    <Link :href="playersIndex()" class="underline"
                        >Add some players</Link
                    >
                    to get started.
                </p>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="font-medium">Recent games</h2>
                    <Link
                        :href="gamesIndex()"
                        class="text-sm text-muted-foreground hover:underline"
                        >All games</Link
                    >
                </div>
                <div v-if="recentGames.length" class="divide-y">
                    <GameRow
                        v-for="game in recentGames"
                        :key="game.id"
                        :game="game"
                    />
                </div>
                <p
                    v-else
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    No games logged yet.
                </p>
            </section>
        </div>
    </div>
</template>
