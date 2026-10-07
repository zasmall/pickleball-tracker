<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PlayerController from '@/actions/App/Http/Controllers/PlayerController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import LeaderboardTable from '@/components/LeaderboardTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/players';
import type { LeaderboardRow } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Players',
                href: index(),
            },
        ],
    },
});

defineProps<{
    players: LeaderboardRow[];
}>();
</script>

<template>
    <Head title="Players" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading
            title="Players"
            description="Everyone you play with, ranked by win percentage"
            class="mb-0!"
        />

        <Form
            v-bind="PlayerController.store.form()"
            reset-on-success
            :options="{ preserveScroll: true }"
            class="flex max-w-md flex-col gap-2"
            v-slot="{ errors, processing }"
        >
            <Label for="name">Add a player</Label>
            <div class="flex gap-2">
                <Input
                    id="name"
                    name="name"
                    required
                    maxlength="50"
                    placeholder="Name"
                    autocomplete="off"
                />
                <Button :disabled="processing">Add</Button>
            </div>
            <InputError :message="errors.name" />
        </Form>

        <section
            class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
        >
            <LeaderboardTable v-if="players.length" :rows="players" />
            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                No players yet. Add yourself and the people you play with above.
            </p>
        </section>
    </div>
</template>
