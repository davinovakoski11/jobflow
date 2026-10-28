<script setup lang="ts">
import { labels, type Application, type Status } from "../domain/applications";
defineProps<{ item: Application }>();
defineEmits<{
  edit: [item: Application];
  remove: [id: number];
  move: [item: Application, status: Status];
}>();
const nextStep: Partial<Record<Status, Status>> = {
  saved: "applied",
  applied: "interview",
  interview: "offer",
};
const money = (value: number) =>
  new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
    maximumFractionDigits: 0,
  }).format(value);
</script>
<template>
  <article class="job-card" :class="`stage-${item.status}`">
    <div class="job-top">
      <span class="company-avatar" aria-hidden="true">{{
        item.company.slice(0, 2).toUpperCase()
      }}</span
      ><q-btn
        flat
        dense
        round
        icon="more_horiz"
        :aria-label="`Ações de ${item.company}`"
        ><q-menu
          ><q-list style="min-width: 180px"
            ><q-item clickable v-close-popup @click="$emit('edit', item)"
              ><q-item-section>Editar oportunidade</q-item-section></q-item
            ><q-item
              v-if="nextStep[item.status]"
              clickable
              v-close-popup
              @click="$emit('move', item, nextStep[item.status]!)"
              ><q-item-section
                >Avançar para
                {{ labels[nextStep[item.status]!] }}</q-item-section
              ></q-item
            ><q-item
              v-if="item.status !== 'rejected'"
              clickable
              v-close-popup
              @click="$emit('move', item, 'rejected')"
              ><q-item-section>Encerrar processo</q-item-section></q-item
            ><q-item clickable v-close-popup @click="$emit('remove', item.id)"
              ><q-item-section class="text-negative"
                >Excluir</q-item-section
              ></q-item
            ></q-list
          ></q-menu
        ></q-btn
      >
    </div>
    <p class="company-name">{{ item.company }}</p>
    <button class="job-role" @click="$emit('edit', item)">
      {{ item.role }}
    </button>
    <div class="job-meta">
      <span class="material-icons" aria-hidden="true">location_on</span
      >{{ item.location || "Não informada" }}
    </div>
    <span class="work-badge" :class="{ onsite: !item.remote }">{{
      item.remote ? "↗ Remoto" : "Presencial / híbrido"
    }}</span>
    <p v-if="item.salary_min" class="salary">
      {{ money(item.salary_min) }} <span>/ mês</span>
    </p>
    <p v-else class="salary undefined">Salário a combinar</p>
    <div class="job-footer">
      <span>{{
        new Date(item.applied_on + "T12:00:00").toLocaleDateString("pt-BR", {
          day: "2-digit",
          month: "short",
        })
      }}</span
      ><a
        v-if="item.url"
        :href="item.url"
        target="_blank"
        rel="noopener noreferrer"
        :aria-label="`Abrir vaga de ${item.company}`"
        >Ver vaga ↗</a
      ><span v-else class="note-indicator"
        ><span class="material-icons" aria-hidden="true">notes</span
        >Anotações</span
      >
    </div>
  </article>
</template>
