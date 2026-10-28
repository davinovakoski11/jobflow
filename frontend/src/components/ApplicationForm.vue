<script setup lang="ts">
import { reactive, computed } from "vue";
import {
  labels,
  statuses,
  type Application,
  type ApplicationInput,
} from "../domain/applications";
const props = defineProps<{
  application?: Application;
  busy: boolean;
  initialStatus?: Application["status"];
}>();
const emit = defineEmits<{
  save: [input: ApplicationInput, id?: number];
  cancel: [];
}>();
const today = new Date().toLocaleDateString("en-CA");
const form = reactive<ApplicationInput>(
  props.application
    ? { ...props.application }
    : {
        company: "",
        role: "",
        location: "Brasil",
        url: "",
        salary_min: null,
        status: props.initialStatus ?? "saved",
        notes: "",
        applied_on: today,
        remote: true,
      },
);
const options = statuses.map((value) => ({ label: labels[value], value }));
const valid = computed(
  () => form.company.trim().length > 0 && form.role.trim().length > 0,
);
const required = (v: unknown) =>
  Boolean(String(v ?? "").trim()) || "Preencha este campo";
function submit() {
  if (valid.value)
    emit(
      "save",
      {
        ...form,
        company: form.company.trim(),
        role: form.role.trim(),
        salary_min:
          form.salary_min === null || String(form.salary_min) === ""
            ? null
            : Number(form.salary_min),
        url: form.url?.trim() || null,
        notes: form.notes?.trim() || null,
      },
      props.application?.id,
    );
}
</script>
<template>
  <q-card class="editor-card">
    <q-card-section class="editor-title"
      ><div>
        <span class="eyebrow">SEU PRÓXIMO PASSO</span>
        <h2>{{ application ? "Editar oportunidade" : "Nova oportunidade" }}</h2>
      </div>
      <q-btn
        flat
        round
        icon="close"
        aria-label="Fechar formulário"
        @click="emit('cancel')"
    /></q-card-section>
    <q-form @submit="submit">
      <q-card-section class="form-grid">
        <q-input
          v-model="form.company"
          outlined
          label="Empresa *"
          maxlength="120"
          :rules="[required]"
          autofocus
        />
        <q-input
          v-model="form.role"
          outlined
          label="Cargo *"
          maxlength="160"
          :rules="[required]"
        />
        <q-input
          v-model="form.location"
          outlined
          label="Localidade"
          maxlength="160"
        />
        <q-select
          v-model="form.status"
          outlined
          label="Etapa"
          :options="options"
          emit-value
          map-options
        />
        <q-input
          v-model="form.applied_on"
          outlined
          label="Data de referência *"
          type="date"
          :rules="[required]"
        />
        <q-input
          v-model.number="form.salary_min"
          outlined
          label="Salário mínimo mensal (R$)"
          type="number"
          min="0"
          max="99999999"
          step="0.01"
        />
        <q-input
          v-model="form.url"
          outlined
          class="span-two"
          label="Link da vaga"
          type="url"
          maxlength="2048"
          :rules="[
            (v) => !v || /^https?:\/\//i.test(v) || 'Use um link http ou https',
          ]"
        />
        <q-input
          v-model="form.notes"
          outlined
          class="span-two"
          label="Anotações e próximos passos"
          type="textarea"
          maxlength="5000"
          autogrow
        />
        <q-toggle
          v-model="form.remote"
          label="Oportunidade remota"
          color="primary"
        />
      </q-card-section>
      <q-card-actions align="right" class="editor-actions"
        ><q-btn flat label="Cancelar" @click="emit('cancel')" /><q-btn
          unelevated
          color="primary"
          type="submit"
          label="Salvar oportunidade"
          :loading="busy"
          :disable="!valid"
      /></q-card-actions>
    </q-form>
  </q-card>
</template>
