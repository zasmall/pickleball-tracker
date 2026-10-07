<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { formatDiff } from '@/lib/format';
import { show } from '@/routes/players';
import type { LeaderboardRow } from '@/types';

defineProps<{
    rows: LeaderboardRow[];
}>();
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-muted-foreground">
                <tr class="border-b">
                    <th class="py-2 pr-4 font-medium">#</th>
                    <th class="py-2 pr-4 font-medium">Player</th>
                    <th class="py-2 pr-4 text-right font-medium">GP</th>
                    <th class="py-2 pr-4 text-right font-medium">W</th>
                    <th class="py-2 pr-4 text-right font-medium">L</th>
                    <th class="py-2 pr-4 text-right font-medium">Win %</th>
                    <th class="py-2 pr-4 text-right font-medium">+/-</th>
                    <th class="py-2 text-right font-medium">Streak</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, i) in rows"
                    :key="row.id"
                    class="border-b last:border-0"
                >
                    <td class="py-2 pr-4 text-muted-foreground tabular-nums">
                        {{ row.played ? i + 1 : '–' }}
                    </td>
                    <td class="py-2 pr-4">
                        <Link
                            :href="show(row.id)"
                            class="font-medium hover:underline"
                            >{{ row.name }}</Link
                        >
                    </td>
                    <td class="py-2 pr-4 text-right tabular-nums">
                        {{ row.played }}
                    </td>
                    <td class="py-2 pr-4 text-right tabular-nums">
                        {{ row.wins }}
                    </td>
                    <td class="py-2 pr-4 text-right tabular-nums">
                        {{ row.losses }}
                    </td>
                    <td class="py-2 pr-4 text-right tabular-nums">
                        {{ row.played ? `${row.win_rate}%` : '–' }}
                    </td>
                    <td
                        class="py-2 pr-4 text-right tabular-nums"
                        :class="{
                            'text-green-600 dark:text-green-400':
                                row.point_diff > 0,
                            'text-red-600 dark:text-red-400':
                                row.point_diff < 0,
                        }"
                    >
                        {{ formatDiff(row.point_diff) }}
                    </td>
                    <td class="py-2 text-right tabular-nums">
                        {{ row.streak ?? '–' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
