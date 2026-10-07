<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/format';
import { edit } from '@/routes/games';
import { show } from '@/routes/players';
import type { Game, Team } from '@/types';

defineProps<{
    game: Game;
    editable?: boolean;
}>();

const teams: Team[] = ['a', 'b'];
</script>

<template>
    <div
        class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex flex-col gap-1.5">
            <div
                v-for="team in teams"
                :key="team"
                class="flex items-center gap-3"
                :class="
                    game.winner === team
                        ? 'font-semibold'
                        : 'text-muted-foreground'
                "
            >
                <span class="w-6 text-right text-lg tabular-nums">
                    {{ team === 'a' ? game.team_a_score : game.team_b_score }}
                </span>
                <span>
                    <template
                        v-for="(player, i) in team === 'a'
                            ? game.team_a
                            : game.team_b"
                        :key="player.id"
                    >
                        <span v-if="i > 0"> &amp; </span>
                        <Link :href="show(player.id)" class="hover:underline">{{
                            player.name
                        }}</Link>
                    </template>
                </span>
            </div>
        </div>

        <div
            class="flex items-center gap-3 text-sm text-muted-foreground sm:text-right"
        >
            <div>
                <div>{{ formatDate(game.played_on) }}</div>
                <div v-if="game.location" class="text-xs">
                    {{ game.location }}
                </div>
            </div>
            <Badge variant="secondary" class="capitalize">{{
                game.format
            }}</Badge>
            <Link
                v-if="editable"
                :href="edit(game.id)"
                class="rounded-md p-1.5 hover:bg-accent hover:text-accent-foreground"
                aria-label="Edit game"
            >
                <Pencil class="size-4" />
            </Link>
        </div>
    </div>
</template>
