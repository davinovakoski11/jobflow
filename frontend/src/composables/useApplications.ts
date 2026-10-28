import { ref, computed } from "vue";
import {
  isApplication,
  metrics,
  type Application,
  type ApplicationInput,
} from "../domain/applications";
import { demoApplications } from "../domain/demo";
import { request } from "../services/api";

const KEY = "jobflow:applications:v1";
export function useApplications() {
  const items = ref<Application[]>(structuredClone(demoApplications));
  const user = ref<{ name: string; email: string } | null>(null);
  const token = ref<string | null>(null);
  const busy = ref(false);
  const error = ref("");
  try {
    const data = JSON.parse(localStorage.getItem(KEY) || "null");
    if (
      data &&
      data.version === 1 &&
      Array.isArray(data.items) &&
      data.items.every(isApplication)
    )
      items.value = data.items;
  } catch {
    error.value =
      "Não foi possível recuperar os dados locais. A demonstração foi carregada.";
  }
  const isDemo = computed(() => !token.value);
  const stats = computed(() => metrics(items.value));
  function persist() {
    try {
      localStorage.setItem(
        KEY,
        JSON.stringify({ version: 1, items: items.value }),
      );
    } catch {
      throw new Error(
        "O navegador não conseguiu salvar os dados. Exporte suas candidaturas antes de fechar.",
      );
    }
  }
  async function authenticate(email: string, password: string, name?: string) {
    const result = await request<{
      token: string;
      user: { name: string; email: string };
    }>(name ? "/register" : "/login", null, {
      method: "POST",
      body: JSON.stringify({
        email,
        password,
        ...(name ? { name, password_confirmation: password } : {}),
      }),
    });
    // Token apenas na memória: recarregar a página retorna ao modo de demonstração.
    token.value = result.token;
    user.value = result.user;
    try {
      await load();
    } catch (e) {
      token.value = null;
      user.value = null;
      throw e;
    }
  }
  async function load() {
    if (!token.value) return;
    const result = await request<{ data: Application[] }>(
      "/applications",
      token.value,
    );
    items.value = result.data;
  }
  async function save(input: ApplicationInput, id?: number) {
    busy.value = true;
    try {
      if (isDemo.value) {
        const old = items.value;
        const item = {
          ...input,
          id: id ?? Math.max(0, ...items.value.map((i) => i.id)) + 1,
        };
        items.value = id
          ? items.value.map((i) => (i.id === id ? item : i))
          : [item, ...items.value];
        try {
          persist();
        } catch (e) {
          items.value = old;
          throw e;
        }
      } else {
        await request(`/applications${id ? `/${id}` : ""}`, token.value, {
          method: id ? "PUT" : "POST",
          body: JSON.stringify(input),
        });
        await load();
      }
    } finally {
      busy.value = false;
    }
  }
  async function remove(id: number) {
    if (isDemo.value) {
      const old = items.value;
      items.value = items.value.filter((i) => i.id !== id);
      try {
        persist();
      } catch (e) {
        items.value = old;
        throw e;
      }
    } else {
      await request(`/applications/${id}`, token.value, { method: "DELETE" });
      await load();
    }
  }
  async function logout() {
    if (token.value) await request("/logout", token.value, { method: "POST" });
    token.value = null;
    user.value = null;
    try {
      const stored = localStorage.getItem(KEY);
      const data = JSON.parse(stored || "null");
      items.value =
        data?.version === 1 &&
        Array.isArray(data.items) &&
        data.items.every(isApplication)
          ? data.items
          : structuredClone(demoApplications);
    } catch {
      items.value = structuredClone(demoApplications);
    }
  }
  return {
    items,
    user,
    isDemo,
    busy,
    error,
    stats,
    authenticate,
    save,
    remove,
    logout,
  };
}
