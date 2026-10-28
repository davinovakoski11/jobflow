export const statuses = [
  "saved",
  "applied",
  "interview",
  "offer",
  "rejected",
] as const;
export type Status = (typeof statuses)[number];
export const labels: Record<Status, string> = {
  saved: "Salvas",
  applied: "Candidaturas",
  interview: "Entrevistas",
  offer: "Propostas",
  rejected: "Encerradas",
};
export interface Application {
  id: number;
  company: string;
  role: string;
  location: string;
  url: string | null;
  salary_min: number | null;
  status: Status;
  notes: string | null;
  applied_on: string;
  remote: boolean;
}
export type ApplicationInput = Omit<Application, "id">;
export const normalize = (value: string) =>
  value
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase();
export function filterApplications(
  items: Application[],
  query: string,
  status: string,
  remoteOnly: boolean,
) {
  const terms = normalize(query.trim()).split(/\s+/).filter(Boolean);
  return items.filter(
    (item) =>
      (status === "all" || item.status === status) &&
      (!remoteOnly || item.remote) &&
      terms.every((term) =>
        normalize(`${item.company} ${item.role} ${item.location}`).includes(
          term,
        ),
      ),
  );
}
export function metrics(items: Application[]) {
  const applied = items.filter((item) => item.status !== "saved").length;
  const interviews = items.filter((item) => item.status === "interview").length;
  const offers = items.filter((item) => item.status === "offer").length;
  return {
    total: items.length,
    active: items.filter((item) => !["saved", "rejected"].includes(item.status))
      .length,
    interviews,
    offers,
    responseRate: applied
      ? Math.round(((interviews + offers) / applied) * 100)
      : 0,
  };
}
export function isApplication(value: unknown): value is Application {
  if (typeof value !== "object" || value === null) return false;
  const v = value as Record<string, unknown>;
  return (
    Number.isInteger(v.id) &&
    typeof v.company === "string" &&
    typeof v.role === "string" &&
    typeof v.location === "string" &&
    statuses.includes(v.status as Status) &&
    typeof v.remote === "boolean" &&
    typeof v.applied_on === "string" &&
    /^\d{4}-\d{2}-\d{2}$/.test(v.applied_on) &&
    (v.url === null || typeof v.url === "string") &&
    (v.notes === null || typeof v.notes === "string") &&
    (v.salary_min === null ||
      (typeof v.salary_min === "number" &&
        Number.isFinite(v.salary_min) &&
        v.salary_min >= 0))
  );
}
export function toCsv(items: Application[]) {
  // Neutraliza fórmulas ao abrir dados fornecidos pelo usuário em uma planilha.
  const cell = (value: unknown) => {
    let text = String(value ?? "");
    if (/^[=+\-@\t\r]/.test(text)) text = `'${text}`;
    return `"${text.replace(/"/g, '""')}"`;
  };
  return (
    "\uFEFF" +
    [
      [
        "Empresa",
        "Cargo",
        "Localidade",
        "Etapa",
        "Data",
        "Remoto",
        "Salário mínimo",
        "Link",
      ],
      ...items.map((i) => [
        i.company,
        i.role,
        i.location,
        labels[i.status],
        i.applied_on,
        i.remote ? "Sim" : "Não",
        i.salary_min,
        i.url,
      ]),
    ]
      .map((row) => row.map(cell).join(";"))
      .join("\r\n")
  );
}
