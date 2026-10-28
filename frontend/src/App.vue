<script setup lang="ts">
import { computed, ref } from "vue";
import { useQuasar } from "quasar";
import ApplicationCard from "./components/ApplicationCard.vue";
import ApplicationForm from "./components/ApplicationForm.vue";
import { useApplications } from "./composables/useApplications";
import {
  labels,
  statuses,
  filterApplications,
  toCsv,
  type Application,
  type ApplicationInput,
  type Status,
} from "./domain/applications";
const $q = useQuasar();
const app = useApplications();
const { items, user, isDemo, busy, error, stats } = app;
const query = ref(""),
  remoteOnly = ref(false),
  selectedStatus = ref("all"),
  view = ref<"board" | "list">("board");
const newStatus = ref<Status>("saved");
const formOpen = ref(false),
  editing = ref<Application>(),
  loginOpen = ref(false),
  signup = ref(false),
  authBusy = ref(false);
const email = ref(""),
  password = ref(""),
  name = ref(""),
  authError = ref("");
const filtered = computed(() =>
  filterApplications(
    items.value,
    query.value,
    selectedStatus.value,
    remoteOnly.value,
  ),
);
const statusOptions = [
  { label: "Todas as etapas", value: "all" },
  ...statuses.map((value) => ({ label: labels[value], value })),
];
const columns = computed(() =>
  statuses.filter((s) =>
    selectedStatus.value === "all"
      ? s !== "rejected"
      : s === selectedStatus.value,
  ),
);
function create(status: Status = "saved") {
  newStatus.value = status;
  editing.value = undefined;
  formOpen.value = true;
}
function edit(item: Application) {
  editing.value = item;
  formOpen.value = true;
}
function fail(e: unknown) {
  $q.notify({
    type: "negative",
    message:
      e instanceof Error ? e.message : "Não foi possível concluir a operação.",
  });
}
async function save(input: ApplicationInput, id?: number) {
  try {
    await app.save(input, id);
    formOpen.value = false;
    $q.notify({ type: "positive", message: "Oportunidade salva." });
  } catch (e) {
    fail(e);
  }
}
async function move(item: Application, status: Status) {
  try {
    const { id, ...input } = item;
    await app.save({ ...input, status }, id);
    $q.notify({
      type: "positive",
      message: `Movida para ${labels[status].toLowerCase()}.`,
    });
  } catch (e) {
    fail(e);
  }
}
function remove(id: number) {
  $q.dialog({
    title: "Excluir oportunidade?",
    message:
      "Esta oportunidade será removida. Você pode exportar seus dados antes de excluir.",
    cancel: "Cancelar",
    ok: { label: "Excluir", color: "negative" },
  }).onOk(async () => {
    try {
      await app.remove(id);
    } catch (e) {
      fail(e);
    }
  });
}
function exportCsv() {
  const url = URL.createObjectURL(
    new Blob([toCsv(filtered.value)], { type: "text/csv;charset=utf-8" }),
  );
  const a = document.createElement("a");
  a.href = url;
  a.download = "jobflow-candidaturas.csv";
  a.click();
  URL.revokeObjectURL(url);
}
async function authenticate() {
  authBusy.value = true;
  authError.value = "";
  try {
    await app.authenticate(
      email.value,
      password.value,
      signup.value ? name.value : undefined,
    );
    password.value = "";
    loginOpen.value = false;
  } catch (e) {
    authError.value = e instanceof Error ? e.message : "Erro de conexão.";
  } finally {
    authBusy.value = false;
  }
}
async function logout() {
  try {
    await app.logout();
  } catch (e) {
    fail(e);
  }
}
</script>
<template>
  <div class="app-shell">
    <aside class="sidebar">
      <a href="#" class="brand" aria-label="JobFlow início"
        ><span class="brand-mark">j<span>f</span></span
        >jobflow<span class="brand-dot">.</span></a
      >
      <div class="workspace-label">ESPAÇO PESSOAL</div>
      <nav aria-label="Navegação principal">
        <button
          class="nav-link"
          :class="{ active: selectedStatus === 'all' }"
          @click="selectedStatus = 'all'"
        >
          <span class="material-icons" aria-hidden="true">space_dashboard</span
          >Visão geral</button
        ><button
          class="nav-link"
          :class="{ active: selectedStatus === 'interview' }"
          @click="selectedStatus = 'interview'"
        >
          <span class="material-icons" aria-hidden="true">forum</span
          >Entrevistas
          <span class="nav-count">{{ stats.interviews }}</span></button
        ><button
          class="nav-link"
          :class="{ active: selectedStatus === 'saved' }"
          @click="selectedStatus = 'saved'"
        >
          <span class="material-icons" aria-hidden="true">bookmark</span>Vagas
          salvas</button
        ><button
          class="nav-link"
          :class="{ active: selectedStatus === 'rejected' }"
          @click="selectedStatus = 'rejected'"
        >
          <span class="material-icons" aria-hidden="true">inventory_2</span
          >Encerradas
        </button>
      </nav>
      <div class="sidebar-bottom">
        <div class="tip-card">
          <span class="material-icons" aria-hidden="true">tips_and_updates</span
          ><strong>Um passo de cada vez.</strong>
          <p>Organize as oportunidades de hoje. Prepare o seu amanhã.</p>
        </div>
        <a
          class="repo-link"
          href="https://github.com/davinovakoski11/jobflow"
          target="_blank"
          rel="noopener noreferrer"
          >Projeto de portfólio <span aria-hidden="true">↗</span></a
        >
        <div class="profile">
          <span class="user-avatar">{{ user?.name?.slice(0, 1) || "D" }}</span>
          <div>
            <strong>{{ user?.name || "Visitante" }}</strong
            ><span>{{
              isDemo ? "Demonstração local" : "Conta conectada"
            }}</span>
          </div>
        </div>
      </div>
    </aside>
    <main class="main">
      <header class="topbar">
        <div>
          <span class="muted">Meu espaço</span><span class="crumb">/</span
          ><strong>Candidaturas</strong>
        </div>
        <div class="topbar-actions">
          <span class="mode-pill"
            ><i></i>{{ isDemo ? "Modo demonstração" : "API conectada" }}</span
          ><q-btn
            flat
            dense
            no-caps
            :label="isDemo ? 'Conectar API' : 'Sair'"
            @click="isDemo ? (loginOpen = true) : logout()"
          />
        </div>
      </header>
      <section class="workspace">
        <div v-if="error" class="error-banner" role="alert">{{ error }}</div>
        <div class="heading-row">
          <div>
            <span class="eyebrow">SUA CARREIRA EM MOVIMENTO</span>
            <h1>Seu próximo passo começa aqui<span>.</span></h1>
            <p>
              Acompanhe suas oportunidades, da primeira candidatura à proposta.
            </p>
          </div>
          <q-btn
            unelevated
            no-caps
            color="primary"
            icon="add"
            label="Nova oportunidade"
            class="new-button"
            @click="create()"
          />
        </div>
        <div class="stats-grid">
          <article class="stat">
            <div class="stat-caption">
              Oportunidades
              <span class="material-icons violet" aria-hidden="true"
                >work_outline</span
              >
            </div>
            <strong>{{ stats.total.toString().padStart(2, "0") }}</strong
            ><span class="stat-hint">Todo o seu histórico em um lugar</span>
          </article>
          <article class="stat">
            <div class="stat-caption">
              Em andamento
              <span class="material-icons blue" aria-hidden="true">send</span>
            </div>
            <strong>{{ stats.active.toString().padStart(2, "0") }}</strong
            ><span class="stat-hint"
              >Candidaturas, entrevistas e propostas</span
            >
          </article>
          <article class="stat">
            <div class="stat-caption">
              Entrevistas
              <span class="material-icons amber" aria-hidden="true"
                >chat_bubble_outline</span
              >
            </div>
            <strong>{{ stats.interviews.toString().padStart(2, "0") }}</strong
            ><span class="stat-hint">Conversas que abrem caminhos</span>
          </article>
          <article class="stat">
            <div class="stat-caption">
              Propostas
              <span class="material-icons green" aria-hidden="true"
                >celebration</span
              >
            </div>
            <strong>{{ stats.offers.toString().padStart(2, "0") }}</strong
            ><span class="stat-hint">Mais perto do seu objetivo</span>
          </article>
        </div>
        <div class="pipeline-heading">
          <h2>
            Suas oportunidades <span>{{ filtered.length }}</span>
          </h2>
          <q-btn
            flat
            no-caps
            icon="download"
            label="Exportar CSV"
            @click="exportCsv"
          />
        </div>
        <div class="toolbar">
          <label class="search"
            ><span class="material-icons" aria-hidden="true">search</span
            ><input
              v-model="query"
              placeholder="Buscar empresa, cargo ou localidade..."
              aria-label="Buscar oportunidades"
            /><kbd aria-hidden="true">⌕</kbd></label
          ><q-toggle
            v-model="remoteOnly"
            label="Só remotas"
            size="sm"
          /><q-select
            v-model="selectedStatus"
            dense
            outlined
            emit-value
            map-options
            :options="statusOptions"
            aria-label="Filtrar etapa"
            class="stage-filter"
          />
          <div class="view-switch" role="group" aria-label="Visualização">
            <button
              :class="{ selected: view === 'board' }"
              aria-label="Visualização em quadro"
              :aria-pressed="view === 'board'"
              @click="view = 'board'"
            >
              <span class="material-icons" aria-hidden="true"
                >view_kanban</span
              ></button
            ><button
              :class="{ selected: view === 'list' }"
              aria-label="Visualização em lista"
              :aria-pressed="view === 'list'"
              @click="view = 'list'"
            >
              <span class="material-icons" aria-hidden="true">view_list</span>
            </button>
          </div>
        </div>
        <div
          v-if="view === 'board'"
          class="board"
          :style="{ '--columns': columns.length }"
        >
          <section
            v-for="status in columns"
            :key="status"
            class="pipeline-column"
          >
            <div class="column-title">
              <span class="stage-dot" :class="status"></span>
              <h3>{{ labels[status] }}</h3>
              <span class="column-count">{{
                filtered.filter((i) => i.status === status).length
              }}</span
              ><button
                class="column-add"
                :aria-label="`Adicionar oportunidade em ${labels[status]}`"
                @click="create(status)"
              >
                ＋
              </button>
            </div>
            <ApplicationCard
              v-for="item in filtered.filter((i) => i.status === status)"
              :key="item.id"
              :item="item"
              @edit="edit"
              @remove="remove"
              @move="move"
            />
            <div
              v-if="!filtered.some((i) => i.status === status)"
              class="empty-column"
            >
              <span class="material-icons" aria-hidden="true">inbox</span>
              <p>Nenhuma oportunidade<br />nesta etapa.</p>
            </div>
          </section>
        </div>
        <div v-else class="list-view">
          <article v-for="item in filtered" :key="item.id" class="list-row">
            <button @click="edit(item)">
              <strong>{{ item.role }}</strong
              ><span>{{ item.company }} · {{ item.location }}</span></button
            ><span class="list-stage">{{ labels[item.status] }}</span
            ><q-btn
              flat
              round
              icon="edit"
              :aria-label="`Editar ${item.company}`"
              @click="edit(item)"
            />
          </article>
          <p v-if="!filtered.length" class="empty-column">
            Nenhuma oportunidade encontrada.
          </p>
        </div>
        <p class="demo-note" v-if="isDemo">
          <span class="material-icons" aria-hidden="true">info</span>Empresas e
          oportunidades fictícias. Suas alterações ficam salvas neste navegador.
        </p>
      </section>
      <footer class="page-footer">
        Feito para transformar organização em oportunidade.<span
          >JOBFLOW / PORTFÓLIO</span
        >
      </footer>
    </main>
    <q-dialog v-model="formOpen"
      ><ApplicationForm
        v-if="formOpen"
        :application="editing"
        :busy="busy"
        :initial-status="newStatus"
        @save="save"
        @cancel="formOpen = false"
    /></q-dialog>
    <q-dialog v-model="loginOpen"
      ><q-card class="auth-card"
        ><q-card-section
          ><span class="eyebrow">LARAVEL API</span>
          <h2>{{ signup ? "Crie sua conta" : "Conecte seu espaço" }}</h2>
          <p>
            Entre ou crie uma conta para salvar suas candidaturas. A API do
            projeto precisa estar em execução.
          </p>
          <q-banner v-if="authError" class="error-banner" role="alert">{{
            authError
          }}</q-banner
          ><q-form @submit="authenticate" class="auth-form"
            ><q-input
              v-if="signup"
              v-model="name"
              outlined
              label="Nome"
              required
              maxlength="100" /><q-input
              v-model="email"
              outlined
              label="E-mail"
              type="email"
              required /><q-input
              v-model="password"
              outlined
              label="Senha"
              type="password"
              minlength="8"
              required /><q-btn
              type="submit"
              unelevated
              color="primary"
              :loading="authBusy"
              :label="signup ? 'Criar conta' : 'Entrar'"
              no-caps /><q-btn
              flat
              :label="signup ? 'Já tenho uma conta' : 'Criar uma conta'"
              @click="signup = !signup"
              no-caps /><q-btn
              flat
              label="Continuar na demonstração"
              v-close-popup
              no-caps /></q-form></q-card-section></q-card
    ></q-dialog>
  </div>
</template>
