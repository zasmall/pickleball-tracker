<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import PlayerController from '@/actions/App/Http/Controllers/PlayerController';
import ConfirmDelete from '@/components/ConfirmDelete.vue';
import GameRow from '@/components/GameRow.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDiff } from '@/lib/format';
import { destroy, index, show } from '@/routes/players';
import type { Game, HeadToHead, PlayerOption, PlayerStats } from '@/types';

const props = defineProps<{
    player: PlayerOption;
    stats: PlayerStats;
    games: Game[];
}>();

defineOptions({
    layout: (props: { player: PlayerOption }) => ({
        breadcrumbs: [
            { title: 'Players', href: index() },
            { title: props.player.name, href: show(props.player.id) },
        ],
    }),
});

const breakdown = computed(() => [
    { label: 'Singles', record: props.stats.singles },
    { label: 'Doubles', record: props.stats.doubles },
]);

const headToHead = computed<{ title: string; rows: HeadToHead[] }[]>(() => [
    { title: 'Partners', rows: props.stats.partners },
    { title: 'Opponents', rows: props.stats.opponents },
]);
</script>

<template>
    <Head :title="player.name" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            :title="player.name"
            :description="`${stats.overall.wins}–${stats.overall.losses} overall`"
            class="mb-0!"
        />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="Games played" :value="stats.overall.played" />
            <StatCard
                label="Win rate"
                :value="
                    stats.overall.played ? `${stats.overall.win_rate}%` : '–'
                "
                :hint="`${stats.overall.wins} W · ${stats.overall.losses} L`"
            />
            <StatCard
                label="Point differential"
                :value="formatDiff(stats.overall.point_diff)"
                :hint="`${stats.overall.points_for} for · ${stats.overall.points_against} against`"
            />
            <StatCard label="Current streak" :value="stats.streak ?? '–'" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-2 font-medium">By format</h2>
                <dl class="divide-y text-sm">
                    <div
                        v-for="item in breakdown"
                        :key="item.label"
                        class="flex justify-between py-2"
                    >
                        <dt class="text-muted-foreground">{{ item.label }}</dt>
                        <dd class="tabular-nums">
                            <template v-if="item.record.played">
                                {{ item.record.wins }}–{{
                                    item.record.losses
                                }}
                                · {{ item.record.win_rate }}% ·
                                {{ formatDiff(item.record.point_diff) }}
                            </template>
                            <template v-else>–</template>
                        </dd>
                    </div>
                </dl>
            </section>

            <section
                v-for="group in headToHead"
                :key="group.title"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-2 font-medium">{{ group.title }}</h2>
                <ul v-if="group.rows.length" class="divide-y text-sm">
                    <li
                        v-for="row in group.rows"
                        :key="row.id"
                        class="flex justify-between py-2"
                    >
                        <Link :href="show(row.id)" class="hover:underline">{{
                            row.name
                        }}</Link>
                        <span class="text-muted-foreground tabular-nums"
                            >{{ row.wins }}–{{ row.losses }} ·
                            {{ row.win_rate }}%</span
                        >
                    </li>
                </ul>
                <p v-else class="py-2 text-sm text-muted-foreground">
                    None yet.
                </p>
            </section>
        </div>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <h2 class="mb-2 font-medium">Games</h2>
            <div v-if="games.length" class="divide-y">
                <GameRow
                    v-for="game in games"
                    :key="game.id"
                    :game="game"
                    editable
                />
            </div>
            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                {{ player.name }} hasn't played any games yet.
            </p>
        </section>

        <section class="flex max-w-md flex-col gap-6">
            <Form
                v-bind="PlayerController.update.form(player.id)"
                :options="{ preserveScroll: true }"
                class="flex flex-col gap-2"
                v-slot="{ errors, processing }"
            >
                <Label for="name">Rename player</Label>
                <div class="flex gap-2">
                    <Input
                        id="name"
                        name="name"
                        :default-value="player.name"
                        required
                        maxlength="50"
                        autocomplete="off"
                    />
                    <Button variant="outline" :disabled="processing"
                        >Save</Button
                    >
                </div>
                <InputError :message="errors.name" />
            </Form>

            <div v-if="!games.length">
                <ConfirmDelete
                    :title="`Delete ${player.name}?`"
                    description="This player has no recorded games, so nothing else will be affected."
                    :action="destroy(player.id)"
                    label="Delete player"
                />
            </div>
        </section>
    </div>
</template>
