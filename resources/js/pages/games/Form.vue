<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import GameController from '@/actions/App/Http/Controllers/GameController';
import ConfirmDelete from '@/components/ConfirmDelete.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create, destroy, edit, index } from '@/routes/games';
import { index as playersIndex } from '@/routes/players';
import type { Game, GameFormat, PlayerOption, Team } from '@/types';

const props = defineProps<{
    game: Game | null;
    players: PlayerOption[];
}>();

defineOptions({
    layout: (props: { game: Game | null }) => ({
        breadcrumbs: [
            { title: 'Games', href: index() },
            props.game
                ? { title: 'Edit game', href: edit(props.game.id) }
                : { title: 'Log game', href: create() },
        ],
    }),
});

const selectClass =
    'border-input h-9 w-full rounded-md border bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:bg-input/30';

function today(): string {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');

    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

const teamSize = (format: GameFormat) => (format === 'singles' ? 1 : 2);

const form = useForm({
    format: props.game?.format ?? ('doubles' as GameFormat),
    played_on: props.game?.played_on ?? today(),
    location: props.game?.location ?? '',
    notes: props.game?.notes ?? '',
    team_a: (props.game?.team_a.map((p) => p.id) ?? ['', '']) as (
        | number
        | ''
    )[],
    team_b: (props.game?.team_b.map((p) => p.id) ?? ['', '']) as (
        | number
        | ''
    )[],
    team_a_score: props.game?.team_a_score ?? ('' as number | ''),
    team_b_score: props.game?.team_b_score ?? ('' as number | ''),
});

watch(
    () => form.format,
    (format) => {
        const size = teamSize(format);

        for (const team of ['team_a', 'team_b'] as const) {
            form[team] = Array.from(
                { length: size },
                (_, i) => form[team][i] ?? '',
            );
        }
    },
);

const teams: { key: Team; label: string }[] = [
    { key: 'a', label: 'Team A' },
    { key: 'b', label: 'Team B' },
];

const selectedIds = computed(
    () => new Set([...form.team_a, ...form.team_b].filter((id) => id !== '')),
);

function isTaken(playerId: number, current: number | ''): boolean {
    return playerId !== current && selectedIds.value.has(playerId);
}

const formats: GameFormat[] = ['singles', 'doubles'];

const errors = computed(
    () => form.errors as Record<string, string | undefined>,
);

function teamError(team: Team): string | undefined {
    return Object.entries(errors.value).find(
        ([key]) => key === `team_${team}` || key.startsWith(`team_${team}.`),
    )?.[1];
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        location: data.location || null,
        notes: data.notes || null,
    }));

    if (props.game) {
        form.submit(GameController.update(props.game.id));
    } else {
        form.submit(GameController.store());
    }
}
</script>

<template>
    <Head :title="game ? 'Edit game' : 'Log game'" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            :title="game ? 'Edit game' : 'Log a game'"
            description="Record who played and the final score"
            class="mb-0!"
        />

        <div
            v-if="players.length < 2"
            class="max-w-2xl rounded-lg border p-4 text-sm"
        >
            You need at least two players to log a game.
            <Link :href="playersIndex()" class="underline">Add players</Link>
            first.
        </div>

        <form
            v-else
            class="flex max-w-2xl flex-col gap-6"
            @submit.prevent="submit"
        >
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="grid gap-2">
                    <Label>Format</Label>
                    <div class="flex rounded-md border p-0.5">
                        <button
                            v-for="option in formats"
                            :key="option"
                            type="button"
                            class="flex-1 rounded px-3 py-1.5 text-sm capitalize transition-colors"
                            :class="
                                form.format === option
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="form.format = option"
                        >
                            {{ option }}
                        </button>
                    </div>
                    <InputError :message="form.errors.format" />
                </div>

                <div class="grid gap-2">
                    <Label for="played_on">Date</Label>
                    <Input
                        id="played_on"
                        v-model="form.played_on"
                        type="date"
                        :max="today()"
                        required
                    />
                    <InputError :message="form.errors.played_on" />
                </div>

                <div class="grid gap-2">
                    <Label for="location">Location</Label>
                    <Input
                        id="location"
                        v-model="form.location"
                        placeholder="Optional"
                        maxlength="100"
                    />
                    <InputError :message="form.errors.location" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <fieldset
                    v-for="team in teams"
                    :key="team.key"
                    class="grid gap-3 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <legend class="px-1 text-sm font-medium">
                        {{ team.label }}
                    </legend>

                    <select
                        v-for="(_, i) in form[`team_${team.key}`]"
                        :key="i"
                        v-model="form[`team_${team.key}`][i]"
                        :class="selectClass"
                        :aria-label="`${team.label} player ${i + 1}`"
                        required
                    >
                        <option value="" disabled>Select player</option>
                        <option
                            v-for="player in players"
                            :key="player.id"
                            :value="player.id"
                            :disabled="
                                isTaken(player.id, form[`team_${team.key}`][i])
                            "
                        >
                            {{ player.name }}
                        </option>
                    </select>
                    <InputError :message="teamError(team.key)" />

                    <div class="grid gap-2">
                        <Label :for="`team_${team.key}_score`">Score</Label>
                        <Input
                            :id="`team_${team.key}_score`"
                            v-model.number="form[`team_${team.key}_score`]"
                            type="number"
                            min="0"
                            max="99"
                            inputmode="numeric"
                            required
                        />
                        <InputError
                            :message="form.errors[`team_${team.key}_score`]"
                        />
                    </div>
                </fieldset>
            </div>

            <div class="grid gap-2">
                <Label for="notes">Notes</Label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="3"
                    maxlength="1000"
                    placeholder="Optional"
                    :class="[selectClass, 'h-auto py-2']"
                />
                <InputError :message="form.errors.notes" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ game ? 'Save changes' : 'Log game' }}
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="index()">Cancel</Link>
                </Button>
                <div v-if="game" class="ml-auto">
                    <ConfirmDelete
                        title="Delete this game?"
                        description="It will be removed from everyone's stats. This cannot be undone."
                        :action="destroy(game.id)"
                        label="Delete game"
                    />
                </div>
            </div>
        </form>
    </div>
</template>
